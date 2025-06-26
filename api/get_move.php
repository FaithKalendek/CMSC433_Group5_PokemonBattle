<?php

/*Input: Attack ID
Output: Name, Description (To be displayed in UI for user to choose from), other move info */

require '../pdo.php';

// Connect to database
$pdo = getPokemonPDO();

// Get the attack_id from the request
$attack_id = $_GET['attack_id'];

$query = $pdo->prepare("SELECT * FROM Attacks WHERE id = :attack_id");
$query->execute([':attack_id' => $attack_id]);

$attack_info = $query->fetch(PDO::FETCH_ASSOC);

echo json_encode($attack_info);

?>