<?php

// Individual move calculation functions here (calculate_move.php was getting a little long)

// Helper function to get the correct table name for inserting/updating
function get_table($is_attacker_player, $target = 'defender') {
    if ($target === 'defender') {
        return $is_attacker_player ? 'OpponentPokemon' : 'PlayerPokemon';
    } else {
        return $is_attacker_player ? 'PlayerPokemon' : 'OpponentPokemon';
    }
}

// Type interactions (weak-to/strong-against) lookup here
// Taking ChatGPT's word for it that this is correct b/c I really don't want to spend any more time doing Pokemon research atp
$type_effectiveness = [
    'Grass' => [
        'Grass' => 0.5,
        'Fire' => 0.5,
        'Water' => 2,
        'Electric' => 1,
        'Normal' => 1,
        'Ground' => 2,
        'Normal/Flying' => 0.5, // Flying halves Grass damage, Normal is neutral
    ],
    'Fire' => [
        'Grass' => 2,
        'Fire' => 0.5,
        'Water' => 0.5,
        'Electric' => 1,
        'Normal' => 1,
        'Ground' => 1,
        'Normal/Flying' => 1, // Fire vs Flying is neutral
    ],
    'Water' => [
        'Grass' => 0.5,
        'Fire' => 2,
        'Water' => 0.5,
        'Electric' => 1,
        'Normal' => 1,
        'Ground' => 2,
        'Normal/Flying' => 1, // Water vs Flying neutral
    ],
    'Electric' => [
        'Grass' => 0.5,
        'Fire' => 1,
        'Water' => 2,
        'Electric' => 0.5,
        'Normal' => 1,
        'Ground' => 0,     // Electric has no effect on Ground
        'Normal/Flying' => 2, // Flying doubles Electric damage, Normal neutral
    ],
    'Normal' => [
        'Grass' => 1,
        'Fire' => 1,
        'Water' => 1,
        'Electric' => 1,
        'Normal' => 1,
        'Ground' => 1,
        'Normal/Flying' => 1, // Normal neutral vs both types
    ],
    'Ground' => [
        'Grass' => 0.5,
        'Fire' => 2,
        'Water' => 1,
        'Electric' => 2,
        'Normal' => 1,
        'Ground' => 1,
        'Normal/Flying' => 0, // Ground no effect on Flying (immune)
    ],
];


// Helper fcn for calculating move damage
function calculate_dmg ($attacker, $defender, $power, $move_accuracy) {

    // Accuracy = accuracy debuff modifier (if any) * move's accuracy
    $accuracy = $attacker['accuracy_debuff'] * $move_accuracy;

    // Not dealing with evasion, so don't worry abt it
    
    // Roll to hit (generate random number 1–100)
    $roll = rand(1, 100);
    
    // If roll > accuracy, move misses
    if ($roll > $accuracy) {

        return 0; // Missed, no damage
    
    } else {
    
        // Else, hit

        // Roll again to see if crit
        $roll = rand(1, 100);

        // If roll >= 95, crit
        if ($roll >= 95) {
            // Set crit modifier to 1.5
            $crit_modifier = 1.5;
        } else {
            $crit_modifier = 1; // No crit, normal damage
        }

        // Calculate damage:
        // Use a balanced formula for low-level stats (level = 1)
        // Github Copilot helped me come up with this formula since my last formula was suuuuuuper unbalanced (dmg way too big)
        global $type_effectiveness; // Use global var defined above for lookup
        $type_effectiveness_mod = $type_effectiveness[$attacker['type']][$defender['type']];
        $modifier = $type_effectiveness_mod * $crit_modifier;
        $level = 10; // Level 1 was way too weak
        $base = ((2 * $level / 5 + 2) * $attacker['attack'] * $power) / $defender['defense'];
        $damage = floor((($base / 50) + 2) * $modifier);

        return $damage;
    }

}

/*Growl:
The user growls in an endearing way, making opposing Pokémon less wary. This lowers their Attack stats by one stage.
Power	—
Accuracy	100 */
function growl ($defender, $pdo, $is_attacker_player) {

    // Growl decreases the defender's attack by 1 stage
    // Since we're just storing modifiers, just decrease attack modifier by one stage (multiply by 2/3)
    $new_defense_modifier = $defender['defense_modifier'] * (2 / 3);

    // Update defender's defense modifier in database
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET defense_modifier = :new_defense_modifier WHERE id = :defender_id
    ");
    $query->execute([':new_defense_modifier' => $new_defense_modifier, ':defender_id' => $defender['id']]);
}

/*Tackle:
A physical attack in which the user charges and slams into the foe with its whole body.
Power	40
Accuracy	100 */
function tackle ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 40, 100);
    if ($damage == 0) {
        // If damage is 0, return a flag indicating no hit
        return ['hit' => false];
    }

    // No effects, just damage, so just calculate new current_hp and update db
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    if ($table === 'PlayerPokemon') {
        $query = $pdo->prepare("UPDATE PlayerPokemon SET current_hp = :new_hp WHERE player_id = :player_id AND pokemon_id = :pokemon_id");
        $query->execute([":new_hp"=> $new_hp, ":player_id" => $defender['player_id'], ":pokemon_id" => $defender['pokemon_id']]);
    } else {
        $query = $pdo->prepare("UPDATE OpponentPokemon SET current_hp = :new_hp WHERE opponent_id = :opponent_id AND pokemon_id = :pokemon_id");
        $query->execute([":new_hp"=> $new_hp, ":opponent_id" => $defender['opponent_id'], ":pokemon_id" => $defender['pokemon_id']]);
    }

    return ['hit' => true];
}

/*Vine Whip:
The target is struck with slender, whiplike vines to inflict damage.
Power	45
Accuracy	100 */
function vine_whip ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 45, 100);
    if ($damage == 0) {
        // If damage is 0, return a flag indicating no hit
        return ['hit' => false];
    }

    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    if ($table === 'PlayerPokemon') {
        $query = $pdo->prepare("UPDATE PlayerPokemon SET current_hp = :new_hp WHERE player_id = :player_id AND pokemon_id = :pokemon_id");
        $query->execute([":new_hp"=> $new_hp, ":player_id" => $defender['player_id'], ":pokemon_id" => $defender['pokemon_id']]);
    } else {
        $query = $pdo->prepare("UPDATE OpponentPokemon SET current_hp = :new_hp WHERE opponent_id = :opponent_id AND pokemon_id = :pokemon_id");
        $query->execute([":new_hp"=> $new_hp, ":opponent_id" => $defender['opponent_id'], ":pokemon_id" => $defender['pokemon_id']]);
    }

    return ['hit' => true];
}

/*Growth:
The user’s body grows all at once, raising its Attack stats by one stage.
Power	—
Accuracy	—
 */
function growth ($attacker, $pdo, $is_attacker_player) {

    // Growth increases the attack modifier by 1 stage
    // Since we're just storing modifiers, just increase attack modifier by one stage (multiply by 3/2)
    $new_attack_modifier = $attacker['attack_modifier'] * (3 / 2);

    // Update attack modifier in database
    $table = get_table($is_attacker_player, 'attacker');
    $query = $pdo->prepare("UPDATE $table SET attack_modifier = :new_attack_modifier WHERE id = :attacker_id
    ");
    $query->execute([':newattack_modifier' => $new_attack_modifier, ':attacker_id' => $attacker['id']]);
}

/*Scratch:
Hard, pointed, sharp claws rake the target to inflict damage.
Power	40
Accuracy	100 */
function scratch ($attacker, $defender, $pdo, $is_attacker_player) {
  
    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 40, 100);
    if ($damage == 0) {
        // If damage is 0, return a flag indicating no hit
        return ['hit' => false];
    }

    // No effects, just damage, so just calculate new current_hp and update db
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    if ($table === 'PlayerPokemon') {
        $query = $pdo->prepare("UPDATE PlayerPokemon SET current_hp = :new_hp WHERE player_id = :player_id AND pokemon_id = :pokemon_id");
        $query->execute([":new_hp"=> $new_hp, ":player_id" => $defender['player_id'], ":pokemon_id" => $defender['pokemon_id']]);
    } else {
        $query = $pdo->prepare("UPDATE OpponentPokemon SET current_hp = :new_hp WHERE opponent_id = :opponent_id AND pokemon_id = :pokemon_id");
        $query->execute([":new_hp"=> $new_hp, ":opponent_id" => $defender['opponent_id'], ":pokemon_id" => $defender['pokemon_id']]);
    }

    return ['hit' => true];
}

/*Absorb:
A nutrient-draining attack. The user’s HP is restored by half the damage taken by the target.
Power	20
Accuracy	100
 */
function absorb ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 20, 100);
    if ($damage == 0) {
        // If damage is 0, return a flag indicating no hit
        return ['hit' => false];
    }

    // Calculate new current_hp for BOTH attacker and defender and update db

    // Update defender's current_hp
    $new_defender_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_defender_hp WHERE id = :defender_id
    ");
    $query->execute([':new_defender_hp' => $new_defender_hp, ':defender_id' => $defender['id']]);

    // Update attacker's current_hp (restore half the damage dealt)
    $hp_restored = floor($damage / 2);
    $new_attacker_hp = min($attacker['max_hp'], $attacker['current_hp'] + $hp_restored);
    $table = get_table($is_attacker_player, 'attacker');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_attacker_hp WHERE id = :attacker_id
    ");
    $query->execute([':new_attacker_hp' => $new_attacker_hp, ':attacker_id' => $attacker['id']]);

    return ['hit' => true];
}

/*Ember:
The target is attacked with small flames.
Power	40
Accuracy	100 */
function ember ($attacker, $defender, $pdo, $is_attacker_player) {
  
    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 40, 100);
    if ($damage == 0) {
        // If damage is 0, return a flag indicating no hit
        return ['hit' => false];
    }

    // No effects, just damage, so just calculate new current_hp and update db
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id
    ");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    return ['hit' => true];
}

/*Tail Whip:
The user wags its tail cutely, making opposing Pokémon less wary and lowering their Defense stat by one stage.
Power	—
Accuracy	100
 */
function tail_whip ($defender, $pdo, $is_attacker_player) {

    // Tail Whip decreases the defender's defense by 1 stage
    // Since we're just storing modifiers, just decrease defense modifier by one stage (multiply by 2/3)
    $new_defense_modifier = $defender['defense_modifier'] * (2 / 3);

    // Update defender's defense modifier in database
    $table = get_table($is_attacker_player, 'defender');
    if ($table === 'PlayerPokemon') {
        $query = $pdo->prepare("UPDATE PlayerPokemon SET defense_modifier = :new_defense_modifier WHERE player_id = :player_id AND pokemon_id = :pokemon_id");
        $query->execute([':new_defense_modifier' => $new_defense_modifier, ':player_id' => $defender['player_id'], ':pokemon_id' => $defender['pokemon_id']]);
    } else {
        $query = $pdo->prepare("UPDATE OpponentPokemon SET defense_modifier = :new_defense_modifier WHERE opponent_id = :opponent_id AND pokemon_id = :pokemon_id");
        $query->execute([':new_defense_modifier' => $new_defense_modifier, ':opponent_id' => $defender['opponent_id'], ':pokemon_id' => $defender['pokemon_id']]);
    }
}

/*Bite:
The target is bitten with viciously sharp fangs. This may also make the target flinch (30% chance of skipping turn if target has not yet moved).
Power	60
Accuracy	100
 */
function bite ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 60, 100);

    if ($damage == 0) {
        // If damage is 0, return a flag indicating no hit
        return ['hit' => false];
    }

    // Update defender's current_hp in database
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id
    ");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    // Roll to see if defender flinches (30% chance)
    $roll = rand(1, 100);
    if ($roll <= 30) {
        // Defender flinches, skip their next turn (to be handled in frontend)
        // Return a flag to frontend indicating this
        return ['hit' => true, 'flinch' => true];
    } else {
        // Defender does not flinch
        return ['hit' => true, 'flinch' => false];
    }
}

/*Howl:
The user howls loudly to raise its spirit, boosting its Attack stat by one stage.
Power	—
Accuracy	— */
function howl ($attacker, $pdo, $is_attacker_player) {

    // Howl increases the attack modifier by 1 stage
    // Since we're just storing modifiers, just increase attack modifier by one stage (multiply by 3/2)
    $new_attack_modifier = $attacker['attack_modifier'] * (3 / 2);

    // Update attack modifier in database
    $table = get_table($is_attacker_player, 'attacker');
    $query = $pdo->prepare("UPDATE $table SET attack_modifier = :new_attack_modifier WHERE id = :attacker_id
    ");
    $query->execute([':newattack_modifier' => $new_attack_modifier, ':attacker_id' => $attacker['id']]);
    
}

/*Pound:
The target is physically pounded with a long tail, a foreleg, or the like.
Power	40
Accuracy	100 */
function pound ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 40, 100);
    if ($damage == 0) {
        // If damage is 0, return a flag indicating no hit
        return ['hit' => false];
    }

    // No effects, just damage, so just calculate new current_hp and update db
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([":new_hp"=> $new_hp, ":defender_id" => $defender['id']]);

    return ['hit' => true];
}

/*Bubble Beam:
A spray of bubbles is forcefully ejected at the target. This may also lower the target’s Speed stat by one stage (10% chance).
Power	65
Accuracy	100 */
function bubble_beam ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 65, 100);
    if ($damage == 0) {
        // If damage is 0, return a flag indicating no hit
        return ['hit' => false];
    }

    // Update defender's current_hp in db
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([":new_hp"=> $new_hp, ":defender_id" => $defender['id']]);

    // Roll to see if speed decrease (10% chance)
    $roll = rand(1, 100);
    if ($roll <= 10) {
        // Decrease defender's speed by 1 stage
        $new_speed_modifier = $defender['speed_modifier'] * (2 / 3);

        // Update defender's speed modifier in database
        $query = $pdo->prepare("UPDATE $table SET speed_modifier = :new_speed_modifier WHERE id = :defender_id");
        $query->execute([':new_speed_modifier' => $new_speed_modifier, ':defender_id' => $defender['id']]);
    }

    return ['hit' => true];
}

/*Water Gun:
The target is blasted with a forceful shot of water.
Power	40
Accuracy	100 */
function water_gun ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 40, 100);
    if ($damage == 0) {
        // If damage is 0, return a flag indicating no hit
        return ['hit' => false];
    }

    // No effects, just damage, so just calculate new current_hp and update db
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([":new_hp"=> $new_hp, ":defender_id" => $defender['id']]);

    return ['hit' => true];
}

/*Electro Ball:
The user hurls an electric orb at the target. The faster the user is than the target, the greater the move’s power.
Power	—
Accuracy	100
----
r = UserSpeed ÷ TargetSpeed
Ratio (r)	Power
4 ≤ r      	150
3 ≤ r < 4	120
2 ≤ r < 3	80
1 ≤ r < 2	60
r < 1	40
 */
function electro_ball ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate speed ratio
    $speed_ratio = $attacker['speed'] / $defender['speed'];

    // Determine power based on speed ratio
    if ($speed_ratio >= 4) {
        $power = 150;
    } elseif ($speed_ratio >= 3) {
        $power = 120;
    } elseif ($speed_ratio >= 2) {
        $power = 80;
    } elseif ($speed_ratio >= 1) {
        $power = 60;
    } else {
        $power = 40;
    }

    // Calculate damage using the power determined above
    $damage = calculate_dmg($attacker, $defender, $power, 100); // Move accuracy is always 100 for this move

    if ($damage == 0) {
        return ['hit' => false];
    }

    // Update defender's current_hp in database
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    return ['hit' => true];
}

/*Screech:
An earsplitting screech harshly lowers the target’s Defense stat by two stages.
Power	—
Accuracy	85 */
function screech ($defender, $pdo, $is_attacker_player) {

    // Screech decreases the defender's defense by 2 stages
    // Since we're just storing modifiers, just decrease defense modifier by two stages (multiply by 4/9)
    $new_defense_modifier = $defender['defense_modifier'] * (4 / 9);

    // Update defender's defense modifier in database
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET defense_modifier = :new_defense_modifier WHERE id = :defender_id");
    $query->execute([':new_defense_modifier' => $new_defense_modifier, ':defender_id' => $defender['id']]);
}

/*Charm:
The user gazes at the target rather charmingly, making it less wary. This harshly lowers the target’s Attack stat by two stages.
Power	—
Accuracy	100 */
function charm ($defender, $pdo, $is_attacker_player) {

    // Charm decreases the defender's attack by 2 stages
    // Since we're just storing modifiers, just decrease attack modifier by two stages (multiply by 4/9)
    $new_attack_modifier = $defender['attack_modifier'] * (4 / 9);

    // Update attack modifier in database
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET attack_modifier = :new_attack_modifier WHERE id = :defender_id");
    $query->execute([':new_attack_modifier' => $new_attack_modifier, ':defender_id' => $defender['id']]);
}

/*Gyro Ball:
The user tackles the target with a high-speed spin. 
The slower the user compared to the target, the greater the move’s power
Power	—
Accuracy	100
----
(Power = 25 × TargetSpeed ÷ UserSpeed) */
function gyro_ball ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate power based on speed ratio
    if ($attacker['speed'] == 0) {
        $power = 0; // If attacker speed is 0, power is 0 to avoid division by zero
    } else {
        $power = 25 * $defender['speed'] / $attacker['speed'];
    }

    // Calculate damage using the power determined above
    $damage = calculate_dmg($attacker, $defender, $power, 100); 

    if ($damage == 0) {
        return ['hit' => false];
    }

    // Update defender's current_hp in database
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    return ['hit' => true];
}

/*Sand Attack:
Sand is hurled in the target’s face, reducing the target’s accuracy by one stage.
Power	—
Accuracy	100
 */
function sand_attack ($defender, $pdo, $is_attacker_player) {

    // Sand Attack decreases the defender's accuracy by 1 stage
    // Since we're just storing modifiers, just decrease accuracy modifier by one stage (multiply by 2/3)
    $new_accuracy_debuff = $defender['accuracy_debuff'] * (2 / 3);

    // Update defender's accuracy modifier in database
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET accuracy_debuff = :new_accuracy_debuff WHERE id = :defender_id");
    $query->execute([':new_accuracy_debuff' => $new_accuracy_debuff, ':defender_id' => $defender['id']]);
}

/*Defense Curl:
The user curls up to conceal weak spots and raise its Defense stat by one stage.
Power	—
Accuracy	—
 */
function defense_curl ($attacker, $pdo, $is_attacker_player) {

    // Defense Curl increases the defense modifier by 1 stage
    // Since we're just storing modifiers, just increase defense modifier by one stage (multiply by 3/2)
    $new_defense_modifier = $attacker['defense_modifier'] * (3 / 2);

    // Update defense modifier in database
    $table = get_table($is_attacker_player, 'attacker');
    $query = $pdo->prepare("UPDATE $table SET defense_modifier = :new_defense_modifier WHERE id = :attacker_id");
    $query->execute([':new_defense_modifier' => $new_defense_modifier, ':attacker_id' => $attacker['id']]);
}

/*Rock Throw:
The user picks up and throws a small rock at the target to attack.
Power	50
Accuracy	90
 */
function rock_throw ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 50, 90); // Power is 50, accuracy is 90

    if ($damage == 0) {
        return ['hit' => false];
    }

    // Update defender's current_hp in database
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    return ['hit' => true];
}

/*Astonish:
The user attacks the target while shouting in a startling fashion. This may also make the target flinch (30% chance of skipping turn if target has not yet moved).
Power	30
Accuracy	100
 */
function astonish ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 30, 100); // Power is 30, accuracy is 100

    if ($damage == 0) {
        return ['hit' => false];
    }

    // Update defender's current_hp in database
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    // Roll to see if defender flinches (30% chance)
    $roll = rand(1, 100);
    if ($roll <= 30) {
        return ['hit' => true, 'flinch' => true];
    }

    return ['hit' => true, 'flinch' => false];
}

function mud_slap ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 20, 100); // Power is 20, accuracy is 100

    if ($damage == 0) {
        return ['hit' => false];
    }

    // Update defender's current_hp in database
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    // Lower defender's accuracy by 1 stage
    $new_accuracy_debuff = $defender['accuracy_debuff'] * (2 / 3);
    $query = $pdo->prepare("UPDATE $table SET accuracy_debuff = :new_accuracy_debuff WHERE id = :defender_id");
    $query->execute([':new_accuracy_debuff' => $new_accuracy_debuff, ':defender_id' => $defender['id']]);

    return ['hit' => true];
}

function gust ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 40, 100); // Power is 40, accuracy is 100

    if ($damage == 0) {
        return ['hit' => false];
    }

    // Update defender's current_hp in database
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    return ['hit' => true];
}

/*Leer:
The user gives opposing Pokémon an intimidating leer that lowers the Defense stat by one stage.
Power	—
Accuracy	100 */
function leer ($defender, $pdo, $is_attacker_player) {

    // Leer decreases the defender's defense by 1 stage
    // Since we're just storing modifiers, just decrease defense modifier by one stage (multiply by 2/3)
    $new_defense_modifier = $defender['defense_modifier'] * (2 / 3);

    // Update defender's defense modifier in database
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET defense_modifier = :new_defense_modifier WHERE id = :defender_id");
    $query->execute([':new_defense_modifier' => $new_defense_modifier, ':defender_id' => $defender['id']]);
}

function wing_attack ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 60, 100); // Power is 60, accuracy is 100

    if ($damage == 0) {
        return ['hit' => false];
    }

    // Update defender's current_hp in database
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    return ['hit' => true];
}

function peck ($attacker, $defender, $pdo, $is_attacker_player) {

    // Calculate damage
    $damage = calculate_dmg($attacker, $defender, 35, 100); // Power is 35, accuracy is 100

    if ($damage == 0) {
        return ['hit' => false];
    }

    // Update defender's current_hp in database
    $new_hp = max(0, $defender['current_hp'] - $damage);
    $table = get_table($is_attacker_player, 'defender');
    $query = $pdo->prepare("UPDATE $table SET current_hp = :new_hp WHERE id = :defender_id");
    $query->execute([':new_hp' => $new_hp, ':defender_id' => $defender['id']]);

    return ['hit' => true];
}

/*Agility:
The user relaxes and lightens its body to move faster. This sharply raises the Speed stat by two stages.
Type	Psychic
Category	Status  Status
Power	—
Accuracy	—
 */
function agility ($attacker, $pdo, $is_attacker_player) {

    // Agility increases the speed modifier by 2 stages
    // Since we're just storing modifiers, just increase speed modifier by two stages (multiply by 9/4)
    $new_speed_modifier = $attacker['speed_modifier'] * (9 / 4);

    // Update speed modifier in database
    $table = get_table($is_attacker_player, 'attacker');
    $query = $pdo->prepare("UPDATE $table SET speed_modifier = :new_speed_modifier WHERE id = :attacker_id");
    $query->execute([':new_speed_modifier' => $new_speed_modifier, ':attacker_id' => $attacker['id']]);
}

?>