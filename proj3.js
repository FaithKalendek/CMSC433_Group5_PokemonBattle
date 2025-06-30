
import { Phase } from './gamestate.js';
import { Api } from './api.js';

// Gamestate class that helps determine the players game state amount other important loops in the game.
export class GameState {
    // initialize the player's state to the title screen
    // Set player's starting team to null and active pokemon to null alongside the player's active choice
    // Set the current enemy's team to null and active pokemon to 0
    #phase = Phase.TITLE;
    #player = { team: [], active: 0, choice: null, playerRank : 0};
    #currentEnemy = { team: [], active: 0 };


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
    snapshot() {
        return {
            phase : this.#phase,
            player : this.#player,
            enemy : this.#currentEnemy
        }; 
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


        this.#dispatch();

        // TODO: Api calls to start battle and get player / enemy team.

    }

    async #runTurn() {
        if (this.#player.choice == null) return;

        const playerPokemon = this.#player.team[0];
        const enemyPokemon = this.#currentEnemy.team[0];

        // Turn order
        const first = await Api.turnOrder(playerPokemon.id, enemyPokemon.id); 
        let result;

        if (first == playerPokemon) {
            result = await Api.attack(playerPokemon, enemyPokemon); 
        }
        else {
            result = await Api.attack(enemyPokemon, playerPokemon);
        }

        enemyPokemon.hp -= result.damage;
        // Check if the enemy pokemon is defeated
        if (enemyPokemon.hp <= 0) {
            this.#currentEnemy.active++;
            // Check if the enemy team is defeated
            if (this.#currentEnemy.active >= this.#currentEnemy.team.length) {
                this.#setPhase(Phase.RESULT);
                return;
            }
        }
        playerPokemon.hp -= result.damage;
        // Check if the player pokemon is defeated
        if (playerPokemon.hp <= 0) {
            this.#player.active++;
            // Check if the player team is defeated
            if (this.#player.active >= this.#player.team.length) {
                this.#setPhase(Phase.RESULT);
                return;
            }
        }
        // Reset player choice for next turn
        this.#player.choice = null;



    }

    async selectMove(moveId) {
        if (this.#phase !== Phase.BATTLE) return;

        // Set the player's choice to the move ID
        this.#player.choice = moveId;

        // Proceed to the next game state
        this.next();
    }


    // Sets the current game phase
    #setPhase(phase) { this.#phase = phase; }


}