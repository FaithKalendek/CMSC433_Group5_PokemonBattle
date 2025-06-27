<?php

// Input: Attacker ID, Defender ID, Move name
// Output: Result of the attack (hit/miss, damage, new HP, effects)
// Side effect: Updates the database with new HP/status

/*Attack Calculation Implementation Plan:

get attacker and defender pokemon info from database

get move info from database

If accuracy is null, status move, no damage to calculate
    Apply Buff/Debuff Effect (calculate new stat value and update database)
    return effect result
Else, Calculate damage
    Accuracy = accuracy debuff modifier (if any) * move's accuracy
    Not dealing with evasion, so don't worry abt it
    Roll to hit (generate random number 1–100)
    If roll > accuracy, move misses
        return miss result
    Else, hit
        Roll again to see if crit
        If roll >= 95, crit
            Set crit modifier to 1.5
        Calculate damage:
            Base damage = (attacker's attack * move's power) / defender's defense
            Apply type effectiveness multiplier
            Apply crit multiplier (default 1, 1.5 if crit)
            Round down to nearest integer
            Subtract damage from defender’s current HP
            If HP drops to 0 or below, the Pokémon faints.
            Apply Move Effects (calculate chance if needed)
            Update Database with all changes
            Return hit result with damage, new HP, and any effects
 */

require '../pdo.php';

// Connect to database
$pdo = getPokemonPDO();

// Get attacker ID, defender ID, move ID, and is_attacker_player from the request
$attacker_id = $_GET['attacker_id'];
$defender_id = $_GET['defender_id'];
$move_id = $_GET['move_id'];
$is_attacker_player = $_GET['is_attacker_player'];

// Get attacker and defender stats from database
// If is_attacker_player is true, get attacker from PlayerPokemon and defender from OpponentPokemon
// Else, opposite
if ($is_attacker_player === 'true') {

    // Get attacker from PlayerPokemon
    $query = $pdo->prepare("
        SELECT * FROM PlayerPokemon WHERE id = :attacker_id
    ");
    $query->execute(['attacker_id' => $attacker_id]);
    $attacker = $query->fetch(PDO::FETCH_ASSOC);

    // Get defender from OpponentPokemon
    $query = $pdo->prepare("
        SELECT * FROM OpponentPokemon WHERE id = :defender_id
    ");
    $query->execute(['defender_id' => $defender_id]);
    $defender = $query->fetch(PDO::FETCH_ASSOC);

} else {

    // Get attacker from OpponentPokemon
    $query = $pdo->prepare("
        SELECT * FROM OpponentPokemon WHERE id = :attacker_id
    ");
    $query->execute(['attacker_id' => $attacker_id]);
    $attacker = $query->fetch(PDO::FETCH_ASSOC);

    // Get defender from PlayerPokemon
    $query = $pdo->prepare("
        SELECT * FROM PlayerPokemon WHERE id = :defender_id
    ");
    $query->execute(['defender_id' => $defender_id]);
    $defender = $query->fetch(PDO::FETCH_ASSOC);    
}

// Get move info from database
$query = $pdo->prepare("
    SELECT * FROM Moves WHERE id = :move_id
");
$query->execute(['move_id' => $move_id]);
$move = $query->fetch(PDO::FETCH_ASSOC);


/* Gonna write specific move logic in functions for organization so the switch statements below don't get super chaotic */

// Move logic/calculation functions in move_logic.php
require 'move_logic.php';

// If accuracy is null, status move
if ($move['accuracy'] == null) {

    // Apply Buff/Debuff Effect (calculate new stat value and update database)
    // return result
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