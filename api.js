// This is an api wrapper so that I don't have to edit fetch states each time there is a change to them
// import { Api } from './api.js'; 

const BASE = "http://localhost/CMSC433_Group5_PokemonBattle/api";

// Request function for api calls
// returns response as json object or returns error message
// Used chat gpt to help with this getter function
async function request(path, params = {}) {
  const url = `${BASE}/${path}?${new URLSearchParams(params)}`;

  const res = await fetch(url);
  console.log("[API]", res.status, url);

  if (!res.ok) {
    const txt = await res.text();
    throw new Error(`API ${path} failed: ${res.status} ${txt || "No body"}`);
  }

  /* calculate_* endpoints return JSON, add_player / add_to_team return blank */
  const ct = res.headers.get("content-type") || "";
  return ct.includes("application/json") ? res.json() : res.text();
}


// Api object 
export const Api = {

    // Read functions
    getPokemon : (id = null) => request(`get_pokemon.php${id ? `?id=${id}` : ''}`),
    getMove : (move_id) => request(`get_move.php?id=${move_id}`), 

    // Write / Actions
    addPlayer : (player_name, avatar_url) => request('add_player.php', { player_name, avatar_url }).then(r => r.player_id), 
    addToTeam : (playerId, pokemonId) => request('add_to_team.php', { playerId, pokemonId}), 
    clearTeam : (playerId) => request('clear_player_team.php', { playerId }),
    genRandomTeam : ( num_pokemon, team, character_id) => request('generate_random_team.php', { num_pokemon, team, character_id }),
    turnOrder : (playerPokemonId, enemyPokemonId) => request('calculate_turn_order.php', { playerPokemonId, enemyPokemonId }),
    attack : (attackerId, defenderId, moveName) => request('calculate_attack.php', { attackerId, defenderId, moveName }),
    pickRandomMove : (pokemonId) => request('pick_random_move.php', { pokemonId }),
}; 




