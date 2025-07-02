import { game, Phase } from "./proj3.js";
import { Api } from "./api.js";

/* ---------- Player Trainer Sprite Lookup ---------- */
const playerAvatars = [
  { key: "rookie", sprite: "images/ambitiousrookie.png" },
  { key: "scholar", sprite: "images/curiousscholar.png" },
  { key: "wanderer", sprite: "images/wildwanderer.png" },
  { key: "beginner", sprite: "images/joyfulbeginner.png" }
];

function getPlayerAvatarSprite(key) {
  const found = playerAvatars.find(a => a.key === key);
  return found ? found.sprite : "images/default_player.png";
}

/* ---------- Opponent Trainer Sprite Lookup ---------- */
const opponents = [
  { name: "Youngster Joey",  sprite: "images/youngster.png"  },
  { name: "Lass Ellie",      sprite: "images/lass.png" },
  { name: "PokéManiac Brent",sprite: "images/pokemaniac.png" },
  { name: "Ace Trainer Chad",sprite: "images/acetrainerchad.png" },
  { name: "Ace Trainer Quinn",sprite:"images/acetrainerquinn.png" },
  { name: "Lt. Surge",       sprite: "images/ltsurge.png" },
  { name: "Team Rocket",     sprite: "images/teamrocket.png"},
  { name: "Gym Leader Misty",sprite: "images/misty.png" },
  { name: "Gym Leader Brock",sprite: "images/brock.png" },
  { name: "Champion Blue",   sprite: "images/championblue.png" }
];

function getOpponentSpriteUrl(name) {
  const opp = opponents.find(o => o.name === name);
  return opp ? opp.sprite : "images/default_opponent.png";
}

/* ---------- DOM references ---------- */
const $identityGrid = document.querySelector(".identity-selection");
const $identitySum = document.getElementById("identity-summary");
const $identityName = document.getElementById("identity-name");
const $identityTxt = document.getElementById("identity-snippet");
const $startBtn = document.getElementById("start-battle");
const $pAvatar = document.getElementById("player-trainer-sprite");
const $opponentTrainerAvatar = document.getElementById("opponent-trainer-sprite");

const $playerHp = document.getElementById("player-hp");
const $enemyHp = document.getElementById("opponent-hp");
const $statusTxt = document.querySelector("#status-box p");

const $playerMonImg = document.getElementById("player-avatar");
const $enemyMonImg = document.getElementById("opponent-avatar");

const $battleScreen = document.getElementById("battle-screen");
const $introScreen = document.getElementById("intro-screen");
const $selectionBlock = document.getElementById("pokemon-selection");
const $selectionGrid = document.getElementById("selection-grid");
const $selectionMsg = document.getElementById("selection-message");
const $selectionConf = document.getElementById("selection-confirmation");
const $nextBattleBtn = document.getElementById("next-battle");
const moveBtns = [...document.querySelectorAll("#action-panel .move-btn")];

function spriteUrl(pokemon) {
  // expects a pokemon object with a .name property
  // Lowercase and remove spaces/special chars for file path
  const cleanName = pokemon.name.replace(/[^a-zA-Z0-9]/g, "").toLowerCase();
  return `images/${cleanName}.gif`;
}

function trainerSpriteUrl(name) {
  return name.sprite;
}

function pct(hp, max) {
  if (!max) return "0%";
  return Math.max(0, Math.min(100, (hp / max) * 100)) + "%";
}

const moveCache = new Map(); // Cache for moves to avoid multiple API calls

async function getMoveName(id) {
  if (moveCache.has(id)) {
    return moveCache.get(id).name;
  }

  const data = await Api.getMove(id);
  const move = Array.isArray(data) ? data[0] : data;
  moveCache.set(id, move);
  return move.name;
}

async function renderMoves(pokemon) {
  console.log("Didn't pass check");
  if (!pokemon) return;
  console.log("Passed check");

  // we expect pokemon.move_ids like [13, 85] (two moves)
  const ids = pokemon.move_ids || [];

  // loop over the 2 buttons you have in HTML
  await Promise.all(
    moveBtns.map(async (btn, i) => {
      if (ids[i] !== undefined) {
        btn.disabled = false;
        btn.dataset.moveId = ids[i]; // keep id for click
        btn.textContent = await getMoveName(ids[i]);
      } else {
        btn.disabled = true;
        btn.textContent = "—";
        delete btn.dataset.moveId;
      }
    })
  );
}

// When the player hits the start button, their identity is set and the game state changes to battle.
$startBtn.addEventListener("click", () => {
  const name = $identityName.textContent.trim();
  const avatarUrl = window.selectedAvatarUrl || "images/ambitiousrookie.png";
  console.log("Adding player with avatarUrl:", avatarUrl);
  game.addPlayer(name, avatarUrl);
});

// attack button functionality with game logic
document.querySelectorAll("#action-panel .move-btn").forEach((btn, i) =>
  btn.addEventListener("click", () => {
    // If the button is disabled or the game is not in battle phase, do nothing
    if (btn.disabled || game.snapshot().phase !== Phase.BATTLE) return;

    // Get the move Id and go to the game state
    game.selectMove(i);
  })
);

// Used chat gpt to help make the player and enemy hp bars update.
// Have to test this code when things are running
document.addEventListener("statechange", ({ detail: snap }) => {
  console.log("=== STATECHANGE ===");
  console.log("snap.player:", snap.player);
  console.log("snap.player.avatarUrl:", snap.player.avatarUrl);
  console.log("Setting $pAvatar.src to:", snap.player.avatarUrl || "images/ambitiousrookie.png");

  if (snap.phase === Phase.BATTLE) {
    const p = snap.player.team[snap.player.active];
    const e = snap.currentEnemy.team[snap.currentEnemy.active];

    $playerHp.style.width = `${(p.current_hp / p.max_hp) * 100}%`;
    $enemyHp.style.width = `${(e.current_hp / e.max_hp) * 100}%`;

    // Update player and opponent Pokémon sprites
    $playerMonImg.src = spriteUrl(p);
    $playerMonImg.alt = p.name;
    $enemyMonImg.src = spriteUrl(e);
    $enemyMonImg.alt = e.name;

    // Update player trainer avatar
    $pAvatar.src = snap.player.avatarUrl || "images/ambitiousrookie.png";
    $pAvatar.alt = snap.player.name;

    // Update opponent trainer avatar if available in state
    if ($opponentTrainerAvatar && snap.currentEnemy.name) {
      $opponentTrainerAvatar.src = getOpponentSpriteUrl(snap.currentEnemy.name);
      $opponentTrainerAvatar.alt = snap.currentEnemy.name || "Opponent Trainer";
    }

    if (snap.lastMoveText) {
      $statusTxt.textContent = snap.lastMoveText;
    } else {
      $statusTxt.textContent = `${p.name} HP ${p.current_hp} vs ${e.name} HP ${e.current_hp}`;
    }

    const active = snap.player.team[snap.player.active];
    renderMoves(active);

    // Always show the switch panel during battle
    renderSwitchPanel();
  }

  if (snap.phase === Phase.RESULT) {
    // Hide battle screen, show selection/results screen
    document.getElementById("battle-screen").classList.add("hidden");
    document.getElementById("pokemon-selection").classList.remove("hidden");
    document.getElementById("switch-panel").classList.add("hidden");
  }

  console.log("avatarUrl in state:", snap.player.avatarUrl);
});

// continue button logic
$nextBattleBtn.addEventListener("click", () => {
  game.next();
});

function renderSwitchPanel() {
  const container = document.getElementById("team-roster");
  container.innerHTML = "";

  // Get the actual team and active index from the game state
  const snap = game.snapshot();
  const team = snap.player.team;
  const activeIndex = snap.player.active;

  team.forEach((poke, i) => {
    const btn = document.createElement("button");
    btn.className = "switch-btn";
    btn.textContent = poke.name;

    // Disable if already active or fainted
    if (i === activeIndex || poke.current_hp <= 0) {
      btn.disabled = true;
      btn.style.opacity = "0.5";
    }

    btn.addEventListener("click", () => {
      game.selectActive(i); // Actually switch Pokémon
      document.getElementById("switch-panel").classList.add("hidden");
    });

    container.appendChild(btn);
  });

  document.getElementById("switch-panel").classList.remove("hidden");
}

// Starts the game state
game.start();
