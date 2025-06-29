// This file handles the UI interactions and updates based on game state
import { game, Phase } from './gamestate.js';

// DOM elements 
const $setup = document.getElementById('player-setup');
const $battle = document.getElementById('battle');
const $status = document.getElementById('status');
const $playerHp = document.getElementById('player-hp');
const $enemyHp = document.getElementById('enemy-hp');
const moveButtons = document.querySelectorAll('.move-btn');
const $start = document.getElementById('start-game');
const $avatar = document.getElementById('avatar');

// Show the initial section based on game state
function show(section) {
    $setup.classList.toggle('hidden', section !== Phase.TITLE);
    $battle.classList.toggle('hidden', section !== Phase.BATTLE && section !== Phase.RESULT); 
}


$start.onclick = () => { 

    const name = document.getElementById('player-name').value || 'Player';

    game.next();
}; 

// Calls select move on the game object when a move button is clicked
moveButtons.forEach((button, i) => button.onclick = () => game.selectMove(i));

// Changes to sprite when the player comfirms gender
document.querySelectorAll('input[name="gender"]').forEach(r => {
    r.onchange = () => {
        $avatar.src = r.value === 'boy' ? 'boy png' // Place boy png here
        : 'girl png' // place girl png here
    }; 
});

// Changes width values of Hp bars and result message
document.addEventlistener('state', ({ detail:s}) => {
    show(s.phase);

    if (s.phase === Phase.BATTLE) {
        const p = s.player.team[s.player.active];
        const e = s.currentEnemy.team[s.currentEnemy.active];
        $playerHp.style.width = `{(p.hp / p.maxHp) * 100}%`;
        $enemyHp.style.width = `{(e.hp / e.maxHp) * 100}%`;
        $status.textContent = `${p.name} vs ${e.name} `;

    }

    if (s.phase === Phase.RESULT) {
        $status.textContent = s.player.active >= player.team ? 'You lost' : "You won"; 
    }
});

// Calls the start of the game loop
game.start();