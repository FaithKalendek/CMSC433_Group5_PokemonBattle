// This is an api wrapper so that I don't have to edit fetch states each time there is a change to them
// import { Api } from './api.js'; 

const BASE = "/api";

// Request function for api calls
// returns response as json object or returns error message
async function request(path, data = null) { 
    const opts = data ? {
        method: 'POST', 
        body: JSON.stringify(data),
        headers: { 'Content-Type' : 'application/json' }, 
    }
    : {};
    
    // fetch call to api
    const response = await fetch(`${BASE}/${path}`, opts);
    // error handling
    if (!response.ok) {
        const msg = await response.text();
        throw new error(`API ${path} failed... ${response.status} ${msg}`); 
    }
    return response.json(); 
}

// Api object 
export const Api = {

    // Read functions
    getPokemon : (id = null) => request(`get_pokemon.php${id ? `?id=${id}` : ''}`),
    getMove : (id) => request(`get_move.php?id=${id}`), 

    // Write / Actions
    addPlayer : (name) => request('add_player.php', { name }), 
    addToTeam : (playerId, pokemonId) => request('add_to_team.php', { playerId, pokemonId}), 
    clearTeam : (playerId) => request('clear_player_team.php', { playerId }),
    genRandomTeam : ( pokemonNum, team, playerId) => request('generate_random_team.php', { pokemonNum, team, playerId }),
    turnOrder : (playerPokemonId, enemyPokemonId) => request('calculate_turn_order.php', { playerPokemonId, enemyPokemonId }),
    attack : (attackerId, defenderId, moveName) => request('calculate_attack.php', { attackerId, defenderId, moveName }),
    pickRandomMove : (pokemonId) => request('pick_random_move.php', { pokemonId }),
}; 




