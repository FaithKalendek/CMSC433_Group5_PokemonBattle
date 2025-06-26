<?php

/*Input: Pokémon ID
Output: Randomly chosen attack (from that Pokémon’s available moves)*/

// Each pokemon only has 2 moves, so just generate a random number 0 or 1 to pick one of the two

require '../pdo.php';

// Connect to database
$pdo = getPokemonPDO();

// Get the pokemon_id from the request
$pokemon_id = $_GET['pokemon_id'];

// Get the move IDs for the given pokemon from PokemonMoves table
$query = $pdo->prepare("SELECT move_id FROM PokemonMoves WHERE pokemon_id = :pokemon_id");
$query->execute([':pokemon_id' => $pokemon_id]);
$move_ids = $query->fetchAll(PDO::FETCH_COLUMN);

// Generate a random index (0 = first move, 1 = second move)
$chosen_index = rand(0, 1);

// Return the chosen move ID as JSON
echo json_encode(['move_id' => $move_ids[$chosen_index]]); 

?>