// This is an api wrapper so that I don't have to edit fetch states each time there is a change to them
// import { Api } from './api.js';

const BASE = "http://localhost/CMSC433_Group5_PokemonBattle/api";

function tryParse(text) {
  try {
    return JSON.parse(text);
  } catch {}

  // Handle “}{” → “},{”
  const glued = text.replace(/}\s*{/g, "},{");
  try {
    return JSON.parse("[" + glued + "]");
  } catch {}

  return text;
}

/**
 * Low-level request helper.
 * Always does GET, attaches params as query-string.
 * Parses text, normalizes single objects into [object].
 */
export async function request(path, params = {}) {
  const url = `${BASE}/${path}?${new URLSearchParams(params)}`;
  const res = await fetch(url);
  console.log("[API]", res.status, url);

  if (!res.ok) {
    const txt = await res.text();
    throw new Error(`API ${path} failed: ${res.status} ${txt || "No body"}`);
  }

  // parse whatever comes back
  let data = tryParse(await res.text());

  // **Normalize single-object → single-element array**
  if (data !== null && typeof data === "object" && !Array.isArray(data)) {
    data = [data];
  }

  return data;
}
// Api object
export const Api = {
  // Read functions
  getPokemon: (pokemon_id = null, team, character_id) =>
    request("get_pokemon.php", { pokemon_id, team, character_id }),
  getMove: (move_id) => request(`get_move.php?move_id=${move_id}`),

  // Write / Actions
  addPlayer: (player_name, avatar_url) =>
    request("add_player.php", { player_name, avatar_url }).then((r) => {
      const single = r.length > 0 ? r[0] : r;
      return single.player_id;
    }),
  addToTeam: (character_id, pokemon_id) =>
    request("add_to_team.php", { character_id, pokemon_id }),
  clearTeam: (playerId) => request("clear_player_team.php", { playerId }),
  genRandomTeam: (num_pokemon, team, character_id) =>
    request("generate_random_team.php", { num_pokemon, team, character_id }),
  turnOrder: (attacker_id, defender_id, attacker_side) =>
    request("calculate_turn_order.php", {
      attacker_id,
      defender_id,
      attacker_side,
    }).then((r) => (Array.isArray(r) ? r[0].first : r.first)),
  attack: (
    attacker_id,
    defender_id,
    move_id,
    is_attacker_player,
    player_id,
    opponent_id
  ) =>
    request("calculate_move.php", {
      attacker_id,
      defender_id,
      move_id,
      is_attacker_player,
      player_id,
      opponent_id,
    }),
    pickRandomMove: (pokemon_id) =>
    request("pick_random_move.php", { pokemon_id }),
};
