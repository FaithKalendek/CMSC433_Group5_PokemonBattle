import { Api } from "./api.js";

export const Phase = {
    TITLE: "TITLE",
    BATTLE: "BATTLE",
    RESULT: "RESULT",
}

// Gamestate class that helps determine the players game state amount other important loops in the game.
export class GameState {
  // initialize the player's state to the title screen
  // Set player's starting team to null and active pokemon to null alongside the player's active choice
  // Set the current enemy's team to null and active pokemon to 0
  #phase = Phase.TITLE;
  #player = { name: "", avatarUrl: "", id: "", team: [], active: 0, choice: null, playerRank: 1 };
  #currentEnemy = { name: "", avatarUrl: "", team: [], active: 0 };

  start() {
    this.#setPhase(Phase.TITLE);
  }
  next() {
    this.#advanceGameState();
  }

  #dispatch() {
    document.dispatchEvent(
      new CustomEvent("statechange", { detail: this.snapshot() })
    );
  }

  snapshot() {
    return {
      phase: this.#phase,
      player: {
        name: this.#player.name,
        avatarUrl: this.#player.avatarUrl,
        team: this.#player.team,
        active: this.#player.active,
        choice: this.#player.choice,
      },
      currentEnemy: {
        name: this.#currentEnemy.name,
        avatarUrl: this.#currentEnemy.avatarUrl,
        team: this.#currentEnemy.team,
        active: this.#currentEnemy.active,
      },
    };
  }


  async addPlayer(name, avatarUrl) {
    this.#player.name = name;
    if (!avatarUrl) {
        avatarUrl = " ";
    }
    else {
        // Validate the avatar URL
        try {
            new URL(avatarUrl);
        } catch (e) {
            console.error("Invalid avatar URL:", avatarUrl);
            avatarUrl = " "; // Fallback to a default avatar
    }
    // Api call so that the player's name and avatar are added to the database
    this.#player.avatarUrl = avatarUrl;
    console.log(`Player added: ${name} with avatar ${avatarUrl}`);
    
    try {
        const id = await Api.addPlayer(name, avatarUrl);
        this.#player.id = id;
        console.log(`Player ID: ${this.#player.id}`);
    }
    catch (error) {
        console.error("Error adding player:", error);
    }
    this.#dispatch();
  }
}

  #advanceGameState() {
    switch (this.#phase) {
      case Phase.TITLE:
        return this.#startBattle();
      case Phase.BATTLE:
        return this.#runTurn();
      case Phase.RESULT:
        return this.#startBattle();
      default:
        return;
    }
  }

  async #startBattle() {

    if (!this.#player.id) {
      console.error("Player ID is not set. Cannot start battle.");
      return;
    }

    const level = this.#player.playerRank;
    const pid = this.#player.id;

    try {
        // If the player has no pokemon, generate a random pokemon
        if (this.#player.team.length === 0) {
            // Generate a random team if the player has no pokemon
            const playerTeam = await Api.genRandomTeam(1, "player", pid);
        }

        // Generate a random team for the enemy they are facing
        // The enemy id is based on the player's current level
        const enemyTeam = await Api.genRandomTeam(level, "enemy", level);

        // Set the teams locally
        this.#player.team = playerTeam;
        this.#player.choice = null;
        this.#currentEnemy.team = enemyTeam;
        this.#currentEnemy.active = 0;

        // Change the game phase to battle
        this.#setPhase(Phase.BATTLE);
    } catch (error) {
        console.error("Error starting battle:", error);
        this.#setPhase(Phase.TITLE);
    }
    this.#dispatch();
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
    } else {
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

  // Select move logic
  async selectMove(moveIndex) {
    if (this.#phase !== Phase.BATTLE) return;
    
    // set player choice to the move index
    this.#player.choice = moveIndex;
    // calls run turn
    this.next(); 
  }

  // Sets the current game phase
  #setPhase(phase) {
    this.#phase = phase;
    this.#dispatch();
  }
}

export const game = new GameState();