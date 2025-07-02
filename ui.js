import { game, Phase } from "./proj3.js";
import { Api } from "./api.js";

/* ---------- DOM references ---------- */
const $identityGrid = document.querySelector(".identity-selection");
const $identitySum = document.getElementById("identity-summary");
const $identityName = document.getElementById("identity-name");
const $identityTxt = document.getElementById("identity-snippet");
const $startBtn = document.getElementById("start-battle");
const $pAvatar = document.getElementById("player-trainer-sprite");

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

function spriteUrl(name) {
  return `images/${name.name}.gif`;
}

function trainerSpriteUrl(name) {
  return `images/${name}.png`;
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
  const avatarUrl = $pAvatar.src;
  // calls api to add player to the database and stores data in the gamestate
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


function buildSelection(enemyTeam) {
  // 1. Hide the battle UI, show the selection block
  $battleScreen.classList.add("hidden");
  $selectionBlock.classList.remove("hidden");

  // 2. Clear any previous grid
  $selectionGrid.innerHTML = "";
  $selectionConf.classList.add("hidden");

  // 3. Build a card for every Pokémon the enemy had
  enemyTeam.forEach((mon, i) => {
    const card = document.createElement("div");
    card.className = "pokemon-card";
    card.innerHTML = `
      <img src="${spriteUrl(mon)}" alt="${mon.name}">
      <h4>${mon.name}</h4>
      <button data-idx="${i}">Choose</button>
    `;
    $selectionGrid.appendChild(card);
  });
}



 $selectionGrid.querySelectorAll("button").forEach((btn) => {
   btn.addEventListener("click", async () => {
     const idx = +btn.dataset.idx;
     const chosen = enemyTeam[idx];

     $selectionMsg.textContent = `You chose ${chosen.name}! Great choice.`;
     $selectionConf.classList.remove("hidden");

     try {
       // add the mon to the player’s DB team
       await Api.addToTeam(game.snapshot().player.id, chosen.pokemon_id);
     } catch (err) {
       console.error("Add-to-team failed:", err);
     }
   });
 });






// Used chat gpt to help make the player and enemy hp bars update.
// Have to test this code when things are running
document.addEventListener("statechange", ({ detail: snap }) => {
  if (snap.phase === Phase.BATTLE) {
    const p = snap.player.team[snap.player.active];
    const e = snap.currentEnemy.team[snap.currentEnemy.active];

    $playerHp.style.width = `${(p.current_hp / p.max_hp) * 100}%`;
    $enemyHp.style.width = `${(e.current_hp / e.max_hp) * 100}%`;

    $playerMonImg.src = spriteUrl(p);
    $playerMonImg.alt = p.name;
    $enemyMonImg.src = spriteUrl(e);
    $enemyMonImg.alt = e.name;
    $pAvatar.src = trainerSpriteUrl(snap.player.avatarUrl);
    $pAvatar.alt = snap.player.name;

    if (snap.lastMoveText) {
      $statusTxt.textContent = snap.lastMoveText;
    } else {
      $statusTxt.textContent = `${p.name} HP ${p.current_hp} vs ${e.name} HP ${e.current_hp}`;
    }

    const active = snap.player.team[snap.player.active];
    renderMoves(active);
  }

  if (snap.phase === Phase.RESULT) {
    buildSelection(snap.currentEnemy.team);
  }
});

// continue button logic
$nextBattleBtn.addEventListener("click", () => {
  game.next();
});

// Starts the game state
game.start();
