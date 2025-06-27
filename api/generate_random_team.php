<?php
/* For testing purposes, gonna have a flag for whether to add to player team or opponent team
Input: number of pokemon to generate, team (player/opponent), character_id (player or opponent)
Output: Pokemon info for all added to team from get_pokemon endpoint
*/

require '../pdo.php';

// Connect to database
$pdo = getPokemonPDO();

// Get the number of pokemon to generate, team, and character_id from the request
$num_pokemon = $_GET['num_pokemon'];
$team = $_GET['team'];
$character_id = $_GET['character_id'];

// There are 21 pokemon in the database
// Generate $num_pokemon unique random pokemon ids
$random_pokemon_ids = [];

// Generate until we have the desired number of unique pokemon ids
while (count($random_pokemon_ids) < $num_pokemon) {

    $random_id = rand(1, 21); // Generate random number btwn 1 and 21

    // If the random id is not already in the array, add it
    if (!in_array($random_id, $random_pokemon_ids)) {
        $random_pokemon_ids[] = $random_id;
    }
}

// Delete existing pokemon for the team before adding new ones
if ($team === 'player') {
    $deleteQuery = $pdo->prepare("DELETE FROM PlayerPokemon WHERE player_id = :character_id");
} else {
    $deleteQuery = $pdo->prepare("DELETE FROM OpponentPokemon WHERE opponent_id = :character_id");
}
$deleteQuery->execute([':character_id' => $character_id]);

// Add all the randomly generated pokemon to the team using add_to_team endpoint
// Just gonna call it with a url so I don't have to refactor all my code into a function, 
// but I realize this is inefficient (making an http req even though it's local)
foreach ($random_pokemon_ids as $pokemon_id) {
    $url = "http://localhost/CMSC433_Group5_PokemonBattle/api/add_to_team.php?team=$team&character_id=$character_id&pokemon_id=$pokemon_id";
    $result = file_get_contents($url);
}

// Return pokemon info for all added to team using get_pokemon endpoint
foreach ($random_pokemon_ids as $pokemon_id) {
    $url = "http://localhost/CMSC433_Group5_PokemonBattle/api/get_pokemon.php?pokemon_id=$pokemon_id&team=$team&character_id=$character_id";
    $result = file_get_contents($url);
    echo $result; 
}

?>