import { game, Phase } from "./proj3.js";
import { Api } from "./api.js";

/* ---------- Move Description Lookup ---------- */
const moveDescriptions = {
  "Growl": "The user growls in an endearing way, making opposing Pokémon less wary. This lowers their Attack stats by one stage.\n\nPower: —\nAccuracy: 100",
  "Tackle": "A physical attack in which the user charges and slams into the foe with its whole body.\n\nPower: 40\nAccuracy: 100",
  "Vine Whip": "The target is struck with slender, whiplike vines to inflict damage.\n\nPower: 45\nAccuracy: 100",
  "Growth": "The user’s body grows all at once, raising its Attack stats by one stage.\n\nPower: —\nAccuracy: —",
  "Scratch": "Hard, pointed, sharp claws rake the target to inflict damage.\n\nPower: 40\nAccuracy: 100",
  "Absorb": "A nutrient-draining attack. The user’s HP is restored by half the damage taken by the target.\n\nPower: 20\nAccuracy: 100",
  "Ember": "The target is attacked with small flames.\n\nPower: 40\nAccuracy: 100",
  "Tail Whip": "The user wags its tail cutely, making opposing Pokémon less wary and lowering their Defense stat by one stage.\n\nPower: —\nAccuracy: 100",
  "Bite": "The target is bitten with viciously sharp fangs. This may also make the target flinch (30% chance of skipping turn if target has not yet moved).\n\nPower: 60\nAccuracy: 100",
  "Howl": "The user howls loudly to raise its spirit, boosting its Attack stat by one stage.\n\nPower: —\nAccuracy: —",
  "Pound": "The target is physically pounded with a long tail, a foreleg, or the like.\n\nPower: 40\nAccuracy: 100",
  "Bubble Beam": "A spray of bubbles is forcefully ejected at the target. This may also lower the target’s Speed stat by one stage (10% chance).\n\nPower: 65\nAccuracy: 100",
  "Water Gun": "The target is blasted with a forceful shot of water.\n\nPower: 40\nAccuracy: 100",
  "Electro Ball": "The user hurls an electric orb at the target. The faster the user is than the target, the greater the move’s power.\n\nPower: —\nAccuracy: 100",
  "Screech": "An earsplitting screech harshly lowers the target’s Defense stat by two stages.\n\nPower: —\nAccuracy: 85",
  "Charm": "The user gazes at the target rather charmingly, making it less wary. This harshly lowers the target’s Attack stat by two stages.\n\nPower: —\nAccuracy: 100",
  "Gyro Ball": "The user tackles the target with a high-speed spin. The slower the user compared to the target, the greater the move’s power.\n\nPower: —\nAccuracy: 100",
  "Sand Attack": "Sand is hurled in the target’s face, reducing the target’s accuracy by one stage.\n\nPower: —\nAccuracy: 100",
  "Defense Curl": "The user curls up to conceal weak spots and raise its Defense stat by one stage.\n\nPower: —\nAccuracy: —",
  "Rock Throw": "The user picks up and throws a small rock at the target to attack.\n\nPower: 50\nAccuracy: 90",
  "Astonish": "The user attacks the target while shouting in a startling fashion. This may also make the target flinch (30% chance of skipping turn if target has not yet moved).\n\nPower: 30\nAccuracy: 100",
  "Mud-Slap": "The user hurls mud in the target’s face to inflict damage and lower its accuracy by one stage.\n\nPower: 20\nAccuracy: 100",
  "Gust": "A gust of wind is whipped up by wings and launched at the target to inflict damage.\n\nPower: 40\nAccuracy: 100",
  "Leer": "The user gives opposing Pokémon an intimidating leer that lowers the Defense stat by one stage.\n\nPower: —\nAccuracy: 100",
  "Wing Attack": "The target is struck with large, imposing wings spread wide to inflict damage.\n\nPower: 60\nAccuracy: 100",
  "Peck": "The target is jabbed with a sharply pointed beak or horn.\n\nPower: 35\nAccuracy: 100",
  "Agility": "The user relaxes and lightens its body to move faster. This sharply raises the Speed stat by two stages.\n\nPower: —\nAccuracy: —"
};

/* ---------- Player Trainer Sprite Lookup ---------- */
const playerAvatars = [
  { key: "rookie", sprite: "images/ambitiousrookie.png" },
  { key: "scholar", sprite: "images/curiousscholar.png" },
  { key: "wanderer", sprite: "images/wildwanderer.png" },
  { key: "beginner", sprite: "images/joyfulbeginner.png" },
];

/* ---------- Opponent Trainer Sprite Lookup ---------- */
const opponents = [
  { name: "Youngster Joey", sprite: "images/youngster.png" },
  { name: "Lass Ellie", sprite: "images/lass.png" },
  { name: "PokéManiac Brent", sprite: "images/pokemaniac.png" },
  { name: "Ace Trainer Chad", sprite: "images/acetrainerchad.png" },
  { name: "Ace Trainer Quinn", sprite: "images/acetrainerquinn.png" },
  { name: "Lt. Surge", sprite: "images/ltsurge.png" },
  { name: "Team Rocket", sprite: "images/teamrocket.png" },
  { name: "Gym Leader Misty", sprite: "images/misty.png" },
  { name: "Gym Leader Brock", sprite: "images/brock.png" },
  { name: "Champion Blue", sprite: "images/championblue.png" },
];

function getOpponentSpriteUrl(name) {
  const opp = opponents.find((o) => o.name === name);
  return opp ? opp.sprite : "images/default_opponent.png";
}

/* Music */
const music = {
  [Phase.TITLE]: new Audio("audio/title.mp3"),
  [Phase.BATTLE]: new Audio("audio/battle.mp3"),
  [Phase.LOSS]: new Audio("audio/loss.mp3"),
  [Phase.RESULT]: new Audio("audio/result.mp3"),
};

music[Phase.TITLE].loop = true;
music[Phase.BATTLE].loop = true;
music[Phase.RESULT].loop = true;

/* ---------- DOM references ---------- */
const $identityGrid = document.querySelector(".identity-selection");
const $identitySum = document.getElementById("identity-summary");
const $identityName = document.getElementById("identity-name");
const $identityTxt = document.getElementById("identity-snippet");
const $startBtn = document.getElementById("start-battle");
const $pAvatar = document.getElementById("player-trainer-sprite");
const $opponentTrainerAvatar = document.getElementById(
  "opponent-trainer-sprite"
);

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

// current music track
let currentTrack = null;

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
  if (!pokemon) return;

  // we expect pokemon.move_ids like [13, 85] (two moves)
  const ids = pokemon.move_ids || [];

  await Promise.all(
    moveBtns.map(async (btn, i) => {
      // Find the tooltip span next to the button
      const tooltipSpan = btn.parentElement.querySelector('.tooltiptext');
      if (ids[i] !== undefined) {
        btn.disabled = false;
        btn.dataset.moveId = ids[i];
        const moveName = await getMoveName(ids[i]);
        btn.textContent = moveName;
        // Set the tooltip span's text to the full description
        if (tooltipSpan) {
          tooltipSpan.textContent = moveDescriptions[moveName] || moveName;
        }
      } else {
        btn.disabled = true;
        btn.textContent = "—";
        if (tooltipSpan) tooltipSpan.textContent = "";
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
  console.log(
    "Setting $pAvatar.src to:",
    snap.player.avatarUrl || "images/ambitiousrookie.png"
  );
  if (audioUnlocked) playMusic(snap.phase);

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
      $statusTxt.innerHTML = snap.lastMoveText.replace(/\n/g, "<br>");
    } else {
      $statusTxt.textContent = `${p.name} HP ${p.current_hp} vs ${e.name} HP ${e.current_hp}`;
    }

    const active = snap.player.team[snap.player.active];
    renderMoves(active);

    // Always show the switch panel during battle
    renderSwitchPanel();
  }

  if (snap.phase === Phase.LOSS) {
    showLossScreen();
  }

  if (snap.phase === Phase.RESULT) {
    buildSelection(snap.currentEnemy.team);

    // Hide the switch panel outside of battle
    document.getElementById("switch-panel").classList.add("hidden");
    document.getElementById("battle-screen").classList.add("hidden");
    document.getElementById("pokemon-selection").classList.remove("hidden");
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

function showLossScreen() {
  // Hide everything else
  document.getElementById("battle-screen").classList.add("hidden");
  document.getElementById("pokemon-selection").classList.add("hidden");
  document.getElementById("switch-panel").classList.add("hidden");
  document.getElementById("pre-battle-screen").classList.add("hidden");

  // Set identity name
  const identity = document.getElementById("identity-name").textContent;
  document.getElementById("loss-name").textContent = identity;

  // Show the loss screen
  document.getElementById("loss-screen").classList.remove("hidden");
}

document.getElementById("play-again-loss").addEventListener("click", () => {
  location.reload();
  game.Api.clearteam(game.snapshot().player.id); 
  game.Api.clearteam(game.snapshot().currentEnemy.id);
  game.snapshot().playerRank = 0; // Reset player rank
  game.start(); // Restart the game
});

function buildSelection(enemyTeam) {
  // Make sure screens are swapped
  $battleScreen.classList.add("hidden");
  $selectionBlock.classList.remove("hidden");
  $selectionGrid.innerHTML = "";
  $selectionConf.classList.add("hidden");
  $nextBattleBtn.disabled = true; // lock "Continue" until a pick

  // 2 ⟶ create a card for every opposing Pokémon
  enemyTeam.forEach((mon, i) => {
    const div = document.createElement("div");
    div.className = "pokemon-card";
    div.innerHTML = `
      <img src="${spriteUrl(mon)}" alt="${mon.name}">
      <h4>${mon.name}</h4>
      <button data-i="${i}">Choose</button>`;
    div.querySelector("button").onclick = async () => {
      await game.addToTeam(mon);
      $selectionMsg.textContent = `You chose ${mon.name}! Great choice.`;
      $selectionConf.classList.remove("hidden");
      $nextBattleBtn.disabled = false;
    };
    $selectionGrid.appendChild(div);
  });
}

function playMusic(phase) {
  const next = music[phase];
  if (next === currentTrack) return;

  if (currentTrack) {
    const t = currentTrack;
    const fade = setInterval(() => {
      if (t.volume <= 0.05) {
        t.volume = 0; // Mute before stopping
        t.pause();
        t.currentTime = 0; // Reset to start
        t.volume = 1; // Reset volume for next track
        clearInterval(fade);
        startNext();
      } else {
        t.volume = Math.max(0, t.volume - 0.05); // Decrease volume
      }
    }, 50);
  } else {
    startNext(); // No current track, just start the next one
  }

  function startNext() {
    if (!next) return;
    next.volume = 1;
    next.play().catch(console.error);
    currentTrack = next; // Update current track
  }
}

/* --- unlock audio on the first user-gesture --- */
let audioUnlocked = false;
window.addEventListener(
  "pointerdown", // fires for mouse, touch, pen
  () => {
    if (audioUnlocked) return;
    audioUnlocked = true;
    playMusic(game.snapshot().phase); // start whatever phase we’re in
  },
  { once: true } // listener removes itself
);

// Starts the game state
game.start();
