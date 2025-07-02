<?php

// Input: Attacker ID, Defender ID, Move name
// Output: Result of the attack (hit/miss, damage, new HP, effects)
// Side effect: Updates the database with new HP/status

require '../pdo.php';

// Connect to database
$pdo = getPokemonPDO();

// Get attacker ID, defender ID, move ID, and is_attacker_player from the request
$attacker_id = $_GET['attacker_id'];
$defender_id = $_GET['defender_id'];
$move_id = $_GET['move_id'];
$is_attacker_player = ($_GET['is_attacker_player'] === 'true'); // convert to boolean
$player_id = $_GET['player_id'];
$opponent_id = $_GET['opponent_id'];

// Get attacker and defender stats from database
// If is_attacker_player is true, get attacker from PlayerPokemon and defender from OpponentPokemon
// Else, opposite
if ($is_attacker_player) {

    // Get attacker from PlayerPokemon
    $query = $pdo->prepare("
        SELECT * FROM PlayerPokemon WHERE player_id = :player_id AND pokemon_id = :attacker_id
    ");
    $query->execute([':player_id' => $player_id, ':attacker_id' => $attacker_id]);
    $attacker = $query->fetch(PDO::FETCH_ASSOC);

    // Get defender from OpponentPokemon
    $query = $pdo->prepare("
        SELECT * FROM OpponentPokemon WHERE opponent_id = :opponent_id AND pokemon_id = :defender_id
    ");
    $query->execute([':opponent_id' => $opponent_id, ':defender_id' => $defender_id]);
    $defender = $query->fetch(PDO::FETCH_ASSOC);

} else {

    // Get attacker from OpponentPokemon
    $query = $pdo->prepare("
        SELECT * FROM OpponentPokemon WHERE opponent_id = :opponent_id AND pokemon_id = :attacker_id
    ");
    $query->execute([':opponent_id' => $opponent_id, ':attacker_id' => $attacker_id]);
    $attacker = $query->fetch(PDO::FETCH_ASSOC);

    // Get defender from PlayerPokemon
    $query = $pdo->prepare("
        SELECT * FROM PlayerPokemon WHERE player_id = :player_id AND pokemon_id = :defender_id
    ");
    $query->execute([':player_id' => $player_id, ':defender_id' => $defender_id]);
    $defender = $query->fetch(PDO::FETCH_ASSOC);    
}

if (!$attacker || !$defender) {
    echo json_encode(['error' => 'Attacker or defender not found.']);
    exit;
}

// Fetch names from Pokemon table
$query = $pdo->prepare("SELECT name FROM Pokemon WHERE id = :pokemon_id");
$query->execute([':pokemon_id' => $attacker['pokemon_id']]);
$attacker['name'] = $query->fetchColumn();

$query->execute([':pokemon_id' => $defender['pokemon_id']]);
$defender['name'] = $query->fetchColumn();

// Fetch types from Pokemon table
$query = $pdo->prepare("SELECT type FROM Pokemon WHERE id = :pokemon_id");
$query->execute([':pokemon_id' => $attacker['pokemon_id']]);
$attacker['type'] = $query->fetchColumn();

$query->execute([':pokemon_id' => $defender['pokemon_id']]);
$defender['type'] = $query->fetchColumn();

// Get move info from database
$query = $pdo->prepare("
    SELECT * FROM Moves WHERE id = :move_id
");
$query->execute([':move_id' => $move_id]);
$move = $query->fetch(PDO::FETCH_ASSOC);


/* Gonna write specific move logic in functions for organization so the switch statements below don't get super chaotic */

// Move logic/calculation functions in move_logic.php
require 'move_logic.php';

// If power is null, status move or special case
if ($move['power'] == null) {
    switch ($move['name']) {
        case 'Growl':
            growl($defender, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! " . $defender['name'] . "'s attack was decreased by 1 stage."
            ]);
            break;
        case 'Growth':
            growth($attacker, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . "! " . $attacker['name'] . "'s attack was increased by 1 stage."
            ]);
            break;     
        case 'Tail Whip':
            tail_whip($defender, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! " . $defender['name'] . "'s defense was decreased by 1 stage."
            ]);
            break;
        case 'Leer':
            leer($defender, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! " . $defender['name'] . "'s defense was decreased by 1 stage."
            ]);
            break;
        case 'Charm':
            charm($defender, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! " . $defender['name'] . "'s attack was harshly decreased!"
            ]);
            break;
        case 'Agility':
            agility($attacker, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . "! " . $attacker['name'] . "'s speed sharply rose!"
            ]);
            break;
        case 'Howl':
            howl($attacker, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . "! " . $attacker['name'] . "'s attack rose!"
            ]);
            break;
        case 'Defense Curl':
            defense_curl($attacker, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . "! " . $attacker['name'] . "'s defense rose!"
            ]);
            break;
        case 'Screech':
            screech($defender, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! " . $defender['name'] . "'s defense was harshly decreased!"
            ]);
            break;
        case 'Sand Attack':
            sand_attack($defender, $pdo, $is_attacker_player);
            echo json_encode([
                'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! " . $defender['name'] . "'s accuracy fell!"
            ]);
            break;
        case 'Gyro Ball':
            $result = gyro_ball($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Electro Ball':
            $result = electro_ball($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
    }

} else {
    // Else, Calculate damage, update db
    // Return result
    switch ($move['name']) {
        case 'Tackle':
            $result = tackle($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Vine Whip':
            $result = vine_whip($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Scratch':
            $result = scratch($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Absorb':
            $result = absorb($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! " . $attacker['name'] . " restored some HP!"
                ]);
            }
            break;
        case 'Ember':
            $result = ember($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Bite':
            $result = bite($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else if (isset($result['flinch']) && $result['flinch']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! " . $defender['name'] . " flinches!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Pound':
            $result = pound($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Bubble Beam':
            $result = bubble_beam($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Water Gun':
            $result = water_gun($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Electro Ball':
            $result = electro_ball($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Gyro Ball':
            $result = gyro_ball($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Rock Throw':
            $result = rock_throw($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Astonish':
            $result = astonish($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else if (isset($result['flinch']) && $result['flinch']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! " . $defender['name'] . " flinches!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Mud-Slap':
            $result = mud_slap($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Gust':
            $result = gust($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Wing Attack':
            $result = wing_attack($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
        case 'Peck':
            $result = peck($attacker, $defender, $pdo, $is_attacker_player);
            if (isset($result['hit']) && !$result['hit']) {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . ", but it missed!"
                ]);
            } else {
                echo json_encode([
                    'result' => $attacker['name'] . " used " . $move['name'] . " on " . $defender['name'] . "! "
                ]);
            }
            break;
    }
}
?>