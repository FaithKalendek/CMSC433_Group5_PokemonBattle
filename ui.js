
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

const $battleScreen = document.getElementById("battle-screen");
const $introScreen = document.getElementById("intro-screen");
const $selectionBlock = document.getElementById("pokemon-selection");
const $selectionGrid = document.getElementById("selection-grid");
const $selectionMsg = document.getElementById("selection-message");
const $selectionConf = document.getElementById("selection-confirmation");
const $nextBattleBtn = document.getElementById("next-battle");

// When the player hits the start button, their identity is set and the game state changes to battle.
$startBtn.addEventListener("click", () => {
    const name = $identityName.textContent.trim();
    const avatarUrl = $pAvatar.src; 
    game.addPlayer(name, avatarUrl);
    game.next(); 
});

// attack button functionality with game logic
document.querySelectorAll("#action-panel .move-btn").forEach((btn, i) =>
  btn.addEventListener("click", () => {
    game.selectMove(i);
    game.next(); // runTurn()
  })
);

// Used chat gpt to help make the player and enemy hp bars update.
// Have to test this code when things are running
document.addEventListener("state", ({ detail: snap }) => {
  if (snap.phase === Phase.BATTLE) {
    const p = snap.player.team[snap.player.active];
    const e = snap.enemy.team[snap.enemy.active];

    $playerHp.style.width = `${(p.hp / p.hpMax) * 100}%`;
    $enemyHp.style.width = `${(e.hp / e.hpMax) * 100}%`;
    $statusTxt.textContent = `${p.name} HP ${p.hp} vs ${e.name} HP ${e.hp}`;
  }

  if (snap.phase === Phase.RESULT) {
    buildSelection(snap.enemy.team);
  }
});


// continue button logic
$nextBattleBtn.addEventListener("click", () => {
    game.next(); 
});

// Starts the game state 
game.start();
