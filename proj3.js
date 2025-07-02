import { Api } from "./api.js";

export const Phase = {
  TITLE: "TITLE",
  BATTLE: "BATTLE",
  RESULT: "RESULT",
};

// Gamestate class that helps determine the players game state amount other important loops in the game.
export class GameState {
  // initialize the player's state to the title screen
  // Set player's starting team to null and active pokemon to null alongside the player's active choice
  // Set the current enemy's team to null and active pokemon to 0
  #phase = Phase.TITLE;
  #player = {
    name: "",
    avatarUrl: "",
    id: "",
    team: [],
    active: 0,
    choice: null,
    playerRank: 0,
  };
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
    } else {
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
      } catch (error) {
        console.error("Error adding player:", error);
      }
      this.#dispatch();
    }
    this.next();
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
      if (this.#player.playerRank === 0) {
        // Generate a random team if the player has no pokemon
        const playerTeam = await Api.genRandomTeam(1, "player", pid);
        this.#player.team = playerTeam;
        console.log("Generated player team:");
      }

      // Generate a random team for the enemy they are facing
      // The enemy id is based on the player's current level
      const enemyId = level + 1;
      const enemyTeam = await Api.genRandomTeam(enemyId, "opponent", enemyId);

      // Set the teams locally
      this.#player.choice = null;
      this.#currentEnemy.team = enemyTeam;
      this.#currentEnemy.active = 0;

      // Change the game phase to battle
      this.#setPhase(Phase.BATTLE);
    } catch (error) {
      console.error("Error starting battle:", error);
      this.#setPhase(Phase.TITLE);
    }
    console.log("Battle started with player team:", this.#player.team);
    console.log("Enemy team:", this.#currentEnemy.team);
    this.#dispatch();
  }

  async #runTurn() {
    if (this.#player.choice == null) return;

    const playerPokemon = this.#player.team[this.#player.active];
    const enemyPokemon = this.#currentEnemy.team[this.#currentEnemy.active];

    const moveId = playerPokemon.move_ids[this.#player.choice];
    if (moveId == null) {
      console.error("Invalid move selected.");
      return;
    }



    // Turn order
    const first = await Api.turnOrder(playerPokemon.id, enemyPokemon.id, "player");
    let result, attackerIsPlayer;

    if (first === "attacker") {
        attackerIsPlayer = true;
        result = await Api.attack(playerPokemon.id, enemyPokemon.id, moveId, true, this.#player.id, this.#player.playerRank + 1);

        enemyPokemon.current_hp = result.new_hp ?? (enemyPokemon.current_hp - result.damage);
        if (enemyPokemon.current_hp <= 0) {
          this.#currentEnemy.active++;
        }

    } else {
        attackerIsPlayer = false;
        const { move_id: enemyMoveId} = Array.isArray(await Api.pickRandomMove(enemyPokemon.id)) ? (await Api.pickRandomMove(enemyPokemon.id))[0] : await Api.pickRandomMove(enemyPokemon.id);

        result = await Api.attack(enemyPokemon.id, playerPokemon.id, enemyMoveId, false, this.#player.id, this.#player.playerRank + 1);

        playerPokemon.current_hp = result.new_hp ?? (playerPokemon.current_hp - result.damage);
        if (playerPokemon.current_hp <= 0) {
          this.#player.active++;
        }
    }

    this.#player.choice = null;
    this.#dispatch();
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
