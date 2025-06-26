<?php

/*Input: Attack ID
Output: Name, Description (To be displayed in UI for user to choose from), other move info */

require '../pdo.php';

// Connect to database
$pdo = getPokemonPDO();

// Get the move_id from the request
$move_id = $_GET['move_id'];

$query = $pdo->prepare("SELECT * FROM Attacks WHERE id = :move_id");
$query->execute([':move_id' => $move_id]);

$attack_info = $query->fetch(PDO::FETCH_ASSOC);

echo json_encode($attack_info);

?>