<?php

/*Input: Pokemon ID, team (player/opponent), character_id (player or opponent)
Output: Current Stats*/

require '../pdo.php'; 

// Connect to database
$pdo = getPokemonPDO();

// Get the pokemon id, team, and character_id from the request
$pokemon_id = $_GET['pokemon_id'];
$team = $_GET['team'];
$character_id = $_GET['character_id'];

// Get pokemon stats from both player team table and pokemon table
// name, type, max_hp, current_hp, current_attack, current_defense, current_speed
// Github Copilot helped me out with figuring out how to do this with a JOIN
if ($team === 'player') {
    $query = $pdo->prepare("
        SELECT 
            player_pokemon.*, 
            pokemon.name, 
            pokemon.type, 
            pokemon.max_hp
        FROM PlayerPokemon AS player_pokemon
        JOIN Pokemon AS pokemon ON player_pokemon.pokemon_id = pokemon.id
        WHERE player_pokemon.pokemon_id = :pokemon_id 
          AND player_pokemon.player_id = :character_id
    ");
} else {
    $query = $pdo->prepare("
        SELECT 
            opponent_pokemon.*, 
            pokemon.name, 
            pokemon.type, 
            pokemon.max_hp
        FROM OpponentPokemon AS opponent_pokemon
        JOIN Pokemon AS pokemon ON opponent_pokemon.pokemon_id = pokemon.id
        WHERE opponent_pokemon.pokemon_id = :pokemon_id 
          AND opponent_pokemon.opponent_id = :character_id
    ");
}
$query->execute(['pokemon_id' => $pokemon_id, 'character_id' => $character_id]);
$stats = $query->fetch(PDO::FETCH_ASSOC);

// Return stats as JSON
echo json_encode($stats);

?>