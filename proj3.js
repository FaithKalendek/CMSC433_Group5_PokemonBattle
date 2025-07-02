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
  #lastMoveText = "";

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
      lastMoveText: this.#lastMoveText,
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
    const enemyId = this.#player.playerRank + 1;
    if (this.#player.choice == null) return;

    // Current pokemon in battle
    const playerPokemon = this.#player.team[this.#player.active];
    const enemyPokemon = this.#currentEnemy.team[this.#currentEnemy.active];

    // current move player chose
    const moveId = playerPokemon.move_ids[this.#player.choice];

    if (moveId == null) {
      console.error("Invalid move selected.");
      return;
    }

    console.log(playerPokemon.pokemon_id, enemyPokemon.pokemon_id, moveId);
    // Turn order
    const first = await Api.turnOrder(
      playerPokemon.id,
      enemyPokemon.id,
      "player"
    );
    let result, attackerIsPlayer;

    if (first === "attacker") { // player attacks first
      result = await Api.attack(
        playerPokemon.pokemon_id,
        enemyPokemon.pokemon_id,
        moveId,
        true,
        this.#player.id,
        enemyId
      );
      attackerIsPlayer = true;
      console.log("player attack result:", result);
    } else { // enemy attacks first
      const pick = await Api.pickRandomMove(enemyPokemon.pokemon_id);
      console.log(pick);
      const enemyMoveId = Array.isArray(pick) ? pick[0].move_id : pick.move_id;
      result = await Api.attack(
        enemyPokemon.pokemon_id,
        playerPokemon.pokemon_id,
        enemyMoveId,
        false,
        this.#player.id,
        enemyId
      );
      attackerIsPlayer = false;
        console.log("enemy attack result:", result);
    }

    const playerHpUpdate = await Api.getPokemon(
      playerPokemon.pokemon_id,
      "player",
      this.#player.id
    );

    const enemyHpUpdate = await Api.getPokemon(
      enemyPokemon.pokemon_id,
      "opponent",
      enemyId
    );

    // Update the player's and enemy's current HP
    Object.assign(playerPokemon, playerHpUpdate[0]);
    Object.assign(enemyPokemon, enemyHpUpdate[0]);

    console.log(playerPokemon, enemyPokemon);

    this.#lastMoveText = result.result;
    this.#dispatch(); 


    // Check if both Pokémon are still alive
    if (playerPokemon.current_hp > 0 && enemyPokemon.current_hp > 0) {
      if (attackerIsPlayer) {
        // Enemy takes a turn if player attacked first
         const pick = await Api.pickRandomMove(enemyPokemon.pokemon_id);
         console.log(pick);
         const enemyMoveId = Array.isArray(pick)
           ? pick[0].move_id
           : pick.move_id;
         const enemyResult = await Api.attack(
           enemyPokemon.pokemon_id,
           playerPokemon.pokemon_id,
           enemyMoveId,
           false,
           this.#player.id,
           enemyId
         );
        console.log("Enemy attack result:", enemyResult);
        this.#lastMoveText = enemyResult.result;
      } else {
        // Player takes a turn if enemy attacked first
        const playerResult = await Api.attack(
          playerPokemon.pokemon_id,
          enemyPokemon.pokemon_id,
          moveId,
          true,
          this.#player.id,
          enemyId
        );
        console.log("Player attack result:", playerResult);
        this.#lastMoveText = playerResult.result;
      }
    } else {
      console.log("One of the Pokémon has fainted.");
    }


    // update player and enemy current pokemon hp again
    const updatedPlayerPokemon = await Api.getPokemon(
      playerPokemon.pokemon_id,
      "player",
      this.#player.id
    );
    const updatedEnemyPokemon = await Api.getPokemon(
      enemyPokemon.pokemon_id,
      "opponent",
      enemyId
    );
    // Update the player's and enemy's current HP
    Object.assign(playerPokemon, updatedPlayerPokemon[0]);
    Object.assign(enemyPokemon, updatedEnemyPokemon[0]);

    const resultObject = Array.isArray(result) ? result[0] : result;
    this.#lastMoveText = resultObject.result;


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
