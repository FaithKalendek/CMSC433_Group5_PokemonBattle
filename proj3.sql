-- SQL commands for setting up Pokemon Database

-- Delete any existing tables to start setup fresh
-- Dropping tables in reverse dependency order to avoid foreign key issues
-- Disable foreign key checks temporarily to allow dropping tables (getting foreign key issues anyway)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS PlayerPokemon;
DROP TABLE IF EXISTS OpponentPokemon;
DROP TABLE IF EXISTS PokemonMoves;
DROP TABLE IF EXISTS Moves;
DROP TABLE IF EXISTS Pokemon;
DROP TABLE IF EXISTS Opponents;
DROP TABLE IF EXISTS Players;
SET FOREIGN_KEY_CHECKS = 1;

-- Create table for players
-- Don't put anything in here for setup - player will fill out a form upon game start with data to get added to this table
CREATE TABLE IF NOT EXISTS Players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    avatar_url VARCHAR(255)
);

-- Create table for opponents
CREATE TABLE IF NOT EXISTS Opponents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    avatar_url VARCHAR(255)
);

-- Put a bunch of opponents in here for the player to battle against
INSERT INTO Opponents (name, avatar_url) VALUES
('Professor Oak', 'images/professor_oak.png'),
('Brock', 'images/brock.png'),
('Misty', 'images/misty.png'),
('Team Rocket', 'images/team_rocket.png'),
('Elite Four', 'images/elite_four.png'),
('Champion', 'images/champion.png'),
('Gym Leader', 'images/gym_leader.png'),
('Rival', 'images/rival.png'),
('Legendary Trainer', 'images/legendary_trainer.png'),
('Mystery Opponent', 'images/mystery_opponent.png');

-- Create table for Pokemon
CREATE TABLE IF NOT EXISTS Pokemon (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type VARCHAR(20) NOT NULL,
    max_hp INT NOT NULL,
    attack INT NOT NULL,
    defense INT NOT NULL,
    speed INT NOT NULL
);

-- Populate Pokemon table with data
INSERT INTO Pokemon (name, type, max_hp, attack, defense, speed) VALUES
('Bulbasaur', 'Grass', 45, 49, 49, 45),
('Bellsprout', 'Grass', 50, 75, 35, 40),
('Paras', 'Grass', 35, 70, 55, 25),
('Charmander', 'Fire', 39, 52, 43, 65),
('Vulpix', 'Fire', 38, 41, 40, 65),
('Growlithe', 'Fire', 55, 70, 45, 60),
('Squirtle', 'Water', 44, 48, 65, 43),
('Poliwag', 'Water', 40, 50, 40, 90),
('Psyduck', 'Water', 50, 52, 48, 55),
('Pikachu', 'Electric', 35, 55, 40, 90),
('Magnemite', 'Electric', 25, 35, 70, 45),
('Voltorb', 'Electric', 40, 30, 50, 100),
('Rattata', 'Normal', 30, 56, 35, 72),
('Meowth', 'Normal', 40, 45, 35, 90),
('Eevee', 'Normal', 55, 55, 50, 55),
('Geodude', 'Ground', 40, 80, 100, 20),
('Sandshrew', 'Ground', 50, 75, 85, 40),
('Diglett', 'Ground', 10, 55, 25, 95),
('Pidgey', 'Normal/Flying', 40, 45, 40, 56),
('Spearow', 'Normal/Flying', 40, 60, 30, 70),
('Doduo', 'Normal/Flying', 35, 85, 45, 75);

-- Create table for moves
CREATE TABLE IF NOT EXISTS Moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    power INT, -- NULL for status moves
    accuracy INT -- NULL for status moves
);

-- Populate Moves table with data
INSERT INTO Moves (name, description, power, accuracy) VALUES
('Growl', 'The user growls in an endearing way, making opposing Pokémon less wary. This lowers their Attack stats by one stage.', NULL, 100),
('Tackle', 'A physical attack in which the user charges and slams into the foe with its whole body.', 40, 100),
('Vine Whip', 'The target is struck with slender, whiplike vines to inflict damage.', 45, 100),
('Growth', 'The user’s body grows all at once, raising its Attack stats by one stage.', NULL, NULL),
('Scratch', 'Hard, pointed, sharp claws rake the target to inflict damage.', 40, 100),
('Absorb', 'A nutrient-draining attack. The user’s HP is restored by half the damage taken by the target.', 20, 100),
('Ember', 'The target is attacked with small flames.', 40, 100),
('Tail Whip', 'The user wags its tail cutely, making opposing Pokémon less wary and lowering their Defense stat by one stage.', NULL, 100),
('Bite', 'The target is bitten with viciously sharp fangs. This may also make the target flinch (30% chance of skipping turn if target has not yet moved).', 60, 100),
('Howl', 'The user howls loudly to raise its spirit, boosting its Attack stat by one stage.', NULL, NULL),
('Pound', 'The target is physically pounded with a long tail, a foreleg, or the like.', 40, 100),
('Bubble Beam', 'A spray of bubbles is forcefully ejected at the target. This may also lower the target’s Speed stat by one stage (10% chance).', 65, 100),
('Water Gun', 'The target is blasted with a forceful shot of water.', 40, 100),
('Electro Ball', 'The user hurls an electric orb at the target. The faster the user is than the target, the greater the move’s power.', NULL, 100),
('Screech', 'An earsplitting screech harshly lowers the target’s Defense stat by two stages.', NULL, 85),
('Charm', 'The user gazes at the target rather charmingly, making it less wary. This harshly lowers the target’s Attack stat by two stages.', NULL, 100),
('Gyro Ball', 'The user tackles the target with a high-speed spin. The slower the user compared to the target, the greater the move’s power.', NULL, 100),
('Sand Attack', 'Sand is hurled in the target’s face, reducing the target’s accuracy by one stage.', NULL, 100),
('Defense Curl', 'The user curls up to conceal weak spots and raise its Defense stat by one stage.', NULL, NULL),
('Rock Throw', 'The user picks up and throws a small rock at the target to attack.', 50, 90),
('Astonish', 'The user attacks the target while shouting in a startling fashion. This may also make the target flinch (30% chance of skipping turn if target has not yet moved).', 30, 100),
('Mud-Slap', 'The user hurls mud in the target’s face to inflict damage and lower its accuracy by one stage.', 20, 100),
('Gust', 'A gust of wind is whipped up by wings and launched at the target to inflict damage.', 40, 100),
('Leer', 'The user gives opposing Pokémon an intimidating leer that lowers the Defense stat by one stage.', NULL, 100),
('Wing Attack', 'The target is struck with large, imposing wings spread wide to inflict damage.', 60, 100),
('Peck', 'The target is jabbed with a sharply pointed beak or horn.', 35, 100),
('Agility', 'The user relaxes and lightens its body to move faster. This sharply raises the Speed stat by two stages.', NULL, NULL);

-- Link Pokemon to moves (many-to-many)
CREATE TABLE IF NOT EXISTS PokemonMoves (
    pokemon_id INT NOT NULL,
    move_id INT NOT NULL,
    FOREIGN KEY (pokemon_id) REFERENCES Pokemon(id),
    FOREIGN KEY (move_id) REFERENCES Moves(id),
    PRIMARY KEY (pokemon_id, move_id)
);

-- Grass-types
INSERT INTO PokemonMoves (pokemon_id, move_id) VALUES
((SELECT id FROM Pokemon WHERE name='Bulbasaur'), (SELECT id FROM Moves WHERE name='Growl')),
((SELECT id FROM Pokemon WHERE name='Bulbasaur'), (SELECT id FROM Moves WHERE name='Tackle')),

((SELECT id FROM Pokemon WHERE name='Bellsprout'), (SELECT id FROM Moves WHERE name='Vine Whip')),
((SELECT id FROM Pokemon WHERE name='Bellsprout'), (SELECT id FROM Moves WHERE name='Growth')),

((SELECT id FROM Pokemon WHERE name='Paras'), (SELECT id FROM Moves WHERE name='Scratch')),
((SELECT id FROM Pokemon WHERE name='Paras'), (SELECT id FROM Moves WHERE name='Absorb')),

-- Fire-types
((SELECT id FROM Pokemon WHERE name='Charmander'), (SELECT id FROM Moves WHERE name='Growl')),
((SELECT id FROM Pokemon WHERE name='Charmander'), (SELECT id FROM Moves WHERE name='Scratch')),

((SELECT id FROM Pokemon WHERE name='Vulpix'), (SELECT id FROM Moves WHERE name='Ember')),
((SELECT id FROM Pokemon WHERE name='Vulpix'), (SELECT id FROM Moves WHERE name='Tail Whip')),

((SELECT id FROM Pokemon WHERE name='Growlithe'), (SELECT id FROM Moves WHERE name='Bite')),
((SELECT id FROM Pokemon WHERE name='Growlithe'), (SELECT id FROM Moves WHERE name='Howl')),

-- Water-types
((SELECT id FROM Pokemon WHERE name='Squirtle'), (SELECT id FROM Moves WHERE name='Tackle')),
((SELECT id FROM Pokemon WHERE name='Squirtle'), (SELECT id FROM Moves WHERE name='Tail Whip')),

((SELECT id FROM Pokemon WHERE name='Poliwag'), (SELECT id FROM Moves WHERE name='Pound')),
((SELECT id FROM Pokemon WHERE name='Poliwag'), (SELECT id FROM Moves WHERE name='Bubble Beam')),

((SELECT id FROM Pokemon WHERE name='Psyduck'), (SELECT id FROM Moves WHERE name='Scratch')),
((SELECT id FROM Pokemon WHERE name='Psyduck'), (SELECT id FROM Moves WHERE name='Water Gun')),

-- Electric-types
((SELECT id FROM Pokemon WHERE name='Pikachu'), (SELECT id FROM Moves WHERE name='Charm')),
((SELECT id FROM Pokemon WHERE name='Pikachu'), (SELECT id FROM Moves WHERE name='Tail Whip')),

((SELECT id FROM Pokemon WHERE name='Magnemite'), (SELECT id FROM Moves WHERE name='Tackle')),
((SELECT id FROM Pokemon WHERE name='Magnemite'), (SELECT id FROM Moves WHERE name='Gyro Ball')),

((SELECT id FROM Pokemon WHERE name='Voltorb'), (SELECT id FROM Moves WHERE name='Screech')),
((SELECT id FROM Pokemon WHERE name='Voltorb'), (SELECT id FROM Moves WHERE name='Electro Ball')),

-- Normal-types
((SELECT id FROM Pokemon WHERE name='Rattata'), (SELECT id FROM Moves WHERE name='Tackle')),
((SELECT id FROM Pokemon WHERE name='Rattata'), (SELECT id FROM Moves WHERE name='Tail Whip')),

((SELECT id FROM Pokemon WHERE name='Meowth'), (SELECT id FROM Moves WHERE name='Scratch')),
((SELECT id FROM Pokemon WHERE name='Meowth'), (SELECT id FROM Moves WHERE name='Bite')),

((SELECT id FROM Pokemon WHERE name='Eevee'), (SELECT id FROM Moves WHERE name='Growl')),
((SELECT id FROM Pokemon WHERE name='Eevee'), (SELECT id FROM Moves WHERE name='Sand Attack')),

-- Ground-types
((SELECT id FROM Pokemon WHERE name='Geodude'), (SELECT id FROM Moves WHERE name='Tackle')),
((SELECT id FROM Pokemon WHERE name='Geodude'), (SELECT id FROM Moves WHERE name='Rock Throw')),

((SELECT id FROM Pokemon WHERE name='Sandshrew'), (SELECT id FROM Moves WHERE name='Scratch')),
((SELECT id FROM Pokemon WHERE name='Sandshrew'), (SELECT id FROM Moves WHERE name='Sand Attack')),

((SELECT id FROM Pokemon WHERE name='Diglett'), (SELECT id FROM Moves WHERE name='Astonish')),
((SELECT id FROM Pokemon WHERE name='Diglett'), (SELECT id FROM Moves WHERE name='Mud-Slap')),

-- Normal/Flying-types
((SELECT id FROM Pokemon WHERE name='Pidgey'), (SELECT id FROM Moves WHERE name='Gust')),
((SELECT id FROM Pokemon WHERE name='Pidgey'), (SELECT id FROM Moves WHERE name='Sand Attack')),

((SELECT id FROM Pokemon WHERE name='Spearow'), (SELECT id FROM Moves WHERE name='Leer')),
((SELECT id FROM Pokemon WHERE name='Spearow'), (SELECT id FROM Moves WHERE name='Wing Attack')),

((SELECT id FROM Pokemon WHERE name='Doduo'), (SELECT id FROM Moves WHERE name='Peck')),
((SELECT id FROM Pokemon WHERE name='Doduo'), (SELECT id FROM Moves WHERE name='Agility'));


-- Connect players to team's Pokemon (one-to-many)
-- Keep track of current stats for each Pokémon
CREATE TABLE IF NOT EXISTS PlayerPokemon (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    pokemon_id INT NOT NULL,
    current_hp INT NOT NULL,
    current_attack INT NOT NULL,
    current_defense INT NOT NULL,
    current_speed INT NOT NULL,
    accuracy_debuff INT DEFAULT 0, -- For moves that lower accuracy
    FOREIGN KEY (player_id) REFERENCES Players(id),
    FOREIGN KEY (pokemon_id) REFERENCES Pokemon(id)
);

-- Connect opponents to their pokemon (one-to-many)
-- Keep track of current stats for each Pokémon
-- This table will be refreshed and populated with new pokemon (randomly) each time a new battle starts
CREATE TABLE IF NOT EXISTS OpponentPokemon (
    id INT AUTO_INCREMENT PRIMARY KEY,
    opponent_id INT NOT NULL,
    pokemon_id INT NOT NULL,
    current_hp INT NOT NULL,
    current_attack INT NOT NULL,
    current_defense INT NOT NULL,
    current_speed INT NOT NULL,
    accuracy_debuff INT DEFAULT 0, -- For moves that lower accuracy
    FOREIGN KEY (opponent_id) REFERENCES Opponents(id),
    FOREIGN KEY (pokemon_id) REFERENCES Pokemon(id)
);