// This file handles the UI interactions and updates based on game state
import { game, Phase } from './gamestate.js';

// DOM elements 
const $setup = document.getElementById('player-setup');
const $battle = document.getElementById('battle-screen');
const $status = document.querySelector('#status-box p');
const $playerHp = document.getElementById('player-hp');
const $enemyHp = document.getElementById('opponent-hp');
const moveButtons = document.querySelectorAll('.move-btn');
const $start = document.getElementById('start-game');
const $avatar = document.getElementById('trainer-preview');

const screens = {
    [Phase.TITLE]: $setup,
    [Phase.BATTLE]: $battle,
    [Phase.RESULT]: $battle
}; 

// Show the initial section based on game state
function show(section) {
    Object.values(screens).forEach(el => el.classList.add('hidden'));
    screens[section].classList.remove('hidden');
}


$start.onclick = () => { 

    const name = document.getElementById('player-name').value || 'Ash';

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
document.addEventListener('state', ({ detail: s }) => {
    show(s.phase);

    switch (s.phase) {
        case Phase.TITLE:
            break;

        case Phase.BATTLE:
            const p = s.player.team[s.player.active];
            const e = s.enemy.team[s.enemy.active];
            document.getElementById('player-hp').style.width = `${(p.hp / p.maxHp) * 100}%`;
            document.getElementById('opponent-hp').style.width = `${(e.hp / e.maxHp) * 100}%`;
            document.querySelector('#status-box p').textContent = `${p.name} HP ${p.hp} vs ${e.name} HP ${e.hp}`;
            break;

        case Phase.RESULT: 
            // Change this later to display a results scredn where the player can pick one of the enemies pokemon, or the player receives a game over
            document.querySelector('#status-box p').textContent = 'Game Over';
            break;
        }
});

// Calls the start of the game loop
game.start();