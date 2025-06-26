<?php

require '../pdo.php';

// Connect to database
$pdo = getPokemonPDO();

// Get the player id and pokemon id from the request
$player_name = $_GET['player_name'];
$avatar_url = $_GET['avatar_url'];

// Add the player to the players database
$query = $pdo->prepare("
    INSERT INTO Players (name, avatar_url)
    VALUES (:player_name, :avatar_url)");
$query->execute([":player_name"=> $player_name,":avatar_url"=> $avatar_url]);

?>