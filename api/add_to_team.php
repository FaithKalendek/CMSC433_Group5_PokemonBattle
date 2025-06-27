<?php

require '../pdo.php';

// Connect to database
$pdo = getPokemonPDO();

// Get the player/opponent id and pokemon id from the request
if ($_GET['is_player'] == 'true') {
    $player_id = $_GET['character_id'];
} else {
    $opponent_id = $_GET['character_id'];
}

// Get the pokemon id from the request
$pokemon_id = $_GET['pokemon_id'];

// Add the pokemon to the character's team (initialize stats w/ data from pokemon table)
if (isset($player_id)) {
    $query = $pdo->prepare("
    INSERT INTO PlayerPokemon (
        player_id, pokemon_id, current_hp, current_attack, current_defense, current_speed
    )
    SELECT
        :player_id, id, max_hp, attack, defense, speed
    FROM Pokemon
    WHERE id = :pokemon_id
    ");
    $query->execute([":player_id"=> $player_id,":pokemon_id"=> $pokemon_id]);
} else {
    $query = $pdo->prepare("
    INSERT INTO OpponentPokemon (
        opponent_id, pokemon_id, current_hp, current_attack, current_defense, current_speed
    )
    SELECT
        :opponent_id, id, max_hp, attack, defense, speed
    FROM Pokemon
    WHERE id = :pokemon_id
    ");
    $query->execute([":opponent_id"=> $opponent_id,":pokemon_id"=> $pokemon_id]);
}

?>