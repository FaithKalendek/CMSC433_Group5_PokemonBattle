<?php

/*Input: Attacker and defender Pokémon ids, flag for attacker side (player or opponent)
Output: Which Pokémon goes first (or the order as an array/object)*/

// No moves dealing with priority, so just compare speed stats

// Connect to database
require '../pdo.php'; // Has getPokemonPDO fcn for connecting to Pokemon database
$pdo = getPokemonPDO();

// Get pokemon speed stats from database
// Get attacker speed (table to select from based on attacker_side flag passed in)

$attacker_id = $_GET['attacker_id'];
$defender_id = $_GET['defender_id'];

if ($_GET['attacker_side'] === 'player') {
    
    // Get attacker speed from PlayerPokemon table
    $query = $pdo->prepare("SELECT speed FROM PlayerPokemon WHERE id = :id");
    $query->execute(['id' => $attacker_id]);
    $attacker_speed = $query->fetch(PDO::FETCH_ASSOC);

    // Get defender speed from OpponentPokemon table
    $query = $pdo->prepare("SELECT speed FROM OpponentPokemon WHERE id = :id");
    $query->execute(['id' => $defender_id]);
    $defender_speed = $query->fetch(PDO::FETCH_ASSOC);

} else {

    // Get attacker speed from OpponentPokemon table
    $query = $pdo->prepare("SELECT speed FROM OpponentPokemon WHERE id = :id");
    $query->execute(['id' => $attacker_id]);
    $attacker_speed = $query->fetch(PDO::FETCH_ASSOC);

    // Get defender speed from PlayerPokemon table
    $query = $pdo->prepare("SELECT speed FROM PlayerPokemon WHERE id = :id");
    $query->execute(['id' => $defender_id]);
    $defender_speed = $query->fetch(PDO::FETCH_ASSOC);
}

// Return pokemon with higher speed
// If speeds are equal, break tie randomly
if ($attacker_speed['speed'] > $defender_speed['speed']) {
    $first = 'attacker';
} elseif ($attacker_speed['speed'] < $defender_speed['speed']) {
    $first = 'defender';
} else {
    $first = rand(0, 1) ? 'attacker' : 'defender';
}

echo json_encode(['first' => $first]);

?>