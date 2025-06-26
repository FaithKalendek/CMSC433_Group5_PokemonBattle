<?php

// Input: Attacker ID, Defender ID, Move name
// Output: Result of the attack (hit/miss, damage, new HP, effects)
// Side effect: Updates the database with new HP/status

// Type interactions (weak-to/strong-against) lookup here
// Taking ChatGPT's word for it that this is correct b/c I really don't want to spend any more time doing Pokemon research atp
$typeEffectiveness = [
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

?>