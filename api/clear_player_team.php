<?php
/* Input: player_id
   Output: Clears the player's team by deleting all entries in PlayerPokemon table for that character_id
*/

require '../pdo.php';

// Connect to database
$pdo = getPokemonPDO();

// Get the player_id from the request
$player_id = $_GET['player_id'];

$query = $pdo->prepare("DELETE FROM PlayerPokemon WHERE player_id = :player_id");
$query->execute([':player_id' => $player_id]);

?>