// This file handles the UI interactions and updates based on game state
import { game, Phase } from './gamestate.js';

// DOM elements 
const $setup = document.getElementById('player-setup');
const $battle = document.getElementById('battle-screen');
const $status = document.getElementById('#status-box p');
const $playerHp = document.getElementById('player-hp');
const $enemyHp = document.getElementById('opponent-hp');
const moveButtons = document.querySelectorAll('.action-panel');
const $start = document.getElementById('start-game');
const $avatar = document.getElementById('trainer-preview');

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
        $avatar.src = r.value === 'boy' ? 'proj3_images/user_sprite.png' // Place boy png here
        : 'proj3_images/user_sprite.png' // place girl png here
    }; 
});

// Changes width values of Hp bars and result message
document.addEventListener('state', ({ detail:s}) => {
    show(s.phase);

    if (s.phase === Phase.BATTLE) {
        const p = s.player.team[s.player.active];
        const e = s.currentEnemy.team[s.currentEnemy.active];
        $playerHp.style.width = `{(p.hp / p.maxHp) * 100}%`;
        $enemyHp.style.width = `{(e.hp / e.maxHp) * 100}%`;
        $status.textContent = `${p.name} vs ${e.name} HP ${e.hp}`;

    }

    if (s.phase === Phase.RESULT) {
        $status.textContent = s.player.active >= player.team.length ? 'You lost' : "You won"; 
    }
});

// Calls the start of the game loop
game.start();