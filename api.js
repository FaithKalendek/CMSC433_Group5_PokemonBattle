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
    
    try {
        const response = await fetch(`${BASE}/${path}`, opts);

        if (!response.ok) {
            const message = await response.text();
            throw new Error(`Error ${response.status}: ${message}`);
        }
        return await response.json();

    } catch (error) {
        console.error(`API request failed: ${error.message}`);
        return { error: error.message };
    }
}


// Api object 
export const Api = {

    // Read functions
    getPokemon : (id = null) => request(`get_pokemon.php${id ? `?id=${id}` : ''}`),
    getMove : (id) => request(`get_move.php?id=${id}`), 

    // Write / Actions
    addPlayer : (player_name, avatar_url) => request('add_player.php', { player_name, avatar_url }), 
    addToTeam : (playerId, pokemonId) => request('add_to_team.php', { playerId, pokemonId}), 
    clearTeam : (playerId) => request('clear_player_team.php', { playerId }),
    genRandomTeam : ( pokemonNum, team, playerId) => request('generate_random_team.php', { pokemonNum, team, playerId }),
    turnOrder : (playerPokemonId, enemyPokemonId) => request('calculate_turn_order.php', { playerPokemonId, enemyPokemonId }),
    attack : (attackerId, defenderId, moveName) => request('calculate_attack.php', { attackerId, defenderId, moveName }),
    pickRandomMove : (pokemonId) => request('pick_random_move.php', { pokemonId }),
}; 




