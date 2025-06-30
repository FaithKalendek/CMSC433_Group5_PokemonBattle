
import { Api } from './api.js';

// Game states
export const Phase = { 
    TITLE : 'TITLE', 
    BATTLE : 'BATTLE',
    RESULT : 'RESULT'
};
// Gamestate class that helps determine the players game state amount other important loops in the game.
export class GameState {
    // initialize the player's state to the title screen
    // Set player's starting team to null and active pokemon to null alongside the player's active choice
    // Set the current enemy's team to null and active pokemon to 0
    #phase = Phase.TITLE;
    #player = { name: "Ash", team: [], active: 0, choice: null, playerRank : 0};
    #currentEnemy = { name: "Lucy",team: [], active: 0 };


    // Sets the gamestate to title
    start () { 
        this.#setPhase(Phase.TITLE);
        this.#dispatch();
     }
     // function to advance the game state
    next() { this.#advanceGameState(); }




    // Function to select a move for the player
    selectMove(index) {
        if (this.#phase === Phase.BATTLE) 
            this.#player.choice = index;
    }




    // Function to give current state of the game
    snapshot() {
        return {
            phase: this.#phase,
            player: this.#player,
            enemy: this.#currentEnemy
        };
    }
    


    #dispatch() {
        document.dispatchEvent(
            new CustomEvent('state', { detail: this.snapshot() })
        );
    }



    #advanceGameState() {
        switch (this.#phase) {
            case Phase.TITLE: return this.#startBattle();
            case Phase.BATTLE: return this.#runTurn(); 
            case Phase.RESULT: return this.#startBattle();
            default: return; 

        }
    }


    async #startBattle() {
        this.#setPhase(Phase.BATTLE); 

        if (this.#player.team.length === 0 ) {
            // Fetch player a random pokemon from Api if they don't have a team
            this.#player.team = await Api.getRandomPokemon(1, "player", this.#player.name);
        }

        // Fetch a random enemy team from the Api
        // If the player has beated 12 battles, the enemy will just have 12 pokemon
        if (this.#player.playerRank >= 12) {
            this.#currentEnemy.team = await Api.getRandomPokemon(12, "enemy", this.#currentEnemy.name);
        }
        // If the player hasn't beaten 12 battles, the enemy will increase their team size by 1 each battle. 
        else {
            this.#currentEnemy.team = await Api.getRandomPokemon(this.#player.playerRank, "enemy", this.#currentEnemy.name);
        }


        // Set the active pokemon to the first pokemon in the team
        this.#player.active = 0;
        this.#currentEnemy.active = 0;
        // Reset player choice
        this.#player.choice = null;

        this.#dispatch();
    }




    async #runTurn() {
        if (this.#player.choice == null) return;

        const playerPokemon = this.#player.team[this.#player.active];
        const enemyPokemon = this.#currentEnemy.team[this.#currentEnemy.active];

        // Turn order
        const { first } = await Api.turnOrder(playerPokemon.id, enemyPokemon.id);

        if (first == 'player') {
            result = await Api.attack(playerPokemon, enemyPokemon, moveId); 
            enemyPokemon.hp -= result.damage;
        }
        else {
            result = await Api.attack(enemyPokemon, playerPokemon, enemyPokemon.moves[0]);
            playerPokemon.hp -= result.damage;
        }

        // Reset player choice for next turn
        this.#player.choice = null;
        this.#dispatch();
    }

    // Sets the current game phase
    #setPhase(phase) { 
        this.#phase = phase;
        this.#dispatch();
     }


}


// Export the GameState class
export const game = new GameState();