-- SQL commands for setting up Pokemon Database

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

-- Create table for attacks
CREATE TABLE IF NOT EXISTS Attacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    power INT, -- NULL for status moves
    accuracy INT -- NULL for status moves
);

-- Populate Attacks table with data
INSERT INTO Attacks (name, power, accuracy) VALUES
('Growl', NULL, 100),
('Tackle', 40, 100),
('Vine Whip', 45, 100),
('Growth', NULL, NULL),
('Scratch', 40, 100),
('Absorb', 20, 100),
('Ember', 40, 100),
('Tail Whip', NULL, 100),
('Bite', 60, 100),
('Howl', NULL, NULL),
('Pound', 40, 100),
('Bubble Beam', 65, 100),
('Water Gun', 40, 100),
('Electro Ball', NULL, 100),
('Screech', NULL, 85),
('Charm', NULL, 100),
('Gyro Ball', NULL, 100),
('Sand Attack', NULL, 100),
('Defense Curl', NULL, NULL),
('Rock Throw', 50, 90),
('Astonish', 30, 100),
('Mud-Slap', 20, 100),
('Gust', 40, 100),
('Leer', NULL, 100),
('Wing Attack', 60, 100),
('Peck', 35, 100),
('Agility', NULL, NULL);

-- Link Pokemon to attacks (many-to-many)
CREATE TABLE IF NOT EXISTS PokemonAttacks (
    pokemon_id INT NOT NULL,
    attack_id INT NOT NULL,
    FOREIGN KEY (pokemon_id) REFERENCES Pokemon(id),
    FOREIGN KEY (attack_id) REFERENCES Attacks(id),
    PRIMARY KEY (pokemon_id, attack_id)
);

-- Grass-types
INSERT INTO PokemonAttacks (pokemon_id, attack_id) VALUES
((SELECT id FROM Pokemon WHERE name='Bulbasaur'), (SELECT id FROM Attacks WHERE name='Growl')),
((SELECT id FROM Pokemon WHERE name='Bulbasaur'), (SELECT id FROM Attacks WHERE name='Tackle')),

((SELECT id FROM Pokemon WHERE name='Bellsprout'), (SELECT id FROM Attacks WHERE name='Vine Whip')),
((SELECT id FROM Pokemon WHERE name='Bellsprout'), (SELECT id FROM Attacks WHERE name='Growth')),

((SELECT id FROM Pokemon WHERE name='Paras'), (SELECT id FROM Attacks WHERE name='Scratch')),
((SELECT id FROM Pokemon WHERE name='Paras'), (SELECT id FROM Attacks WHERE name='Absorb')),

-- Fire-types
((SELECT id FROM Pokemon WHERE name='Charmander'), (SELECT id FROM Attacks WHERE name='Growl')),
((SELECT id FROM Pokemon WHERE name='Charmander'), (SELECT id FROM Attacks WHERE name='Scratch')),

((SELECT id FROM Pokemon WHERE name='Vulpix'), (SELECT id FROM Attacks WHERE name='Ember')),
((SELECT id FROM Pokemon WHERE name='Vulpix'), (SELECT id FROM Attacks WHERE name='Tail Whip')),

((SELECT id FROM Pokemon WHERE name='Growlithe'), (SELECT id FROM Attacks WHERE name='Bite')),
((SELECT id FROM Pokemon WHERE name='Growlithe'), (SELECT id FROM Attacks WHERE name='Howl')),

-- Water-types
((SELECT id FROM Pokemon WHERE name='Squirtle'), (SELECT id FROM Attacks WHERE name='Tackle')),
((SELECT id FROM Pokemon WHERE name='Squirtle'), (SELECT id FROM Attacks WHERE name='Tail Whip')),

((SELECT id FROM Pokemon WHERE name='Poliwag'), (SELECT id FROM Attacks WHERE name='Pound')),
((SELECT id FROM Pokemon WHERE name='Poliwag'), (SELECT id FROM Attacks WHERE name='Bubble Beam')),

((SELECT id FROM Pokemon WHERE name='Psyduck'), (SELECT id FROM Attacks WHERE name='Scratch')),
((SELECT id FROM Pokemon WHERE name='Psyduck'), (SELECT id FROM Attacks WHERE name='Water Gun')),

-- Electric-types
((SELECT id FROM Pokemon WHERE name='Pikachu'), (SELECT id FROM Attacks WHERE name='Charm')),
((SELECT id FROM Pokemon WHERE name='Pikachu'), (SELECT id FROM Attacks WHERE name='Tail Whip')),

((SELECT id FROM Pokemon WHERE name='Magnemite'), (SELECT id FROM Attacks WHERE name='Tackle')),
((SELECT id FROM Pokemon WHERE name='Magnemite'), (SELECT id FROM Attacks WHERE name='Gyro Ball')),

((SELECT id FROM Pokemon WHERE name='Voltorb'), (SELECT id FROM Attacks WHERE name='Screech')),
((SELECT id FROM Pokemon WHERE name='Voltorb'), (SELECT id FROM Attacks WHERE name='Electro Ball')),

-- Normal-types
((SELECT id FROM Pokemon WHERE name='Rattata'), (SELECT id FROM Attacks WHERE name='Tackle')),
((SELECT id FROM Pokemon WHERE name='Rattata'), (SELECT id FROM Attacks WHERE name='Tail Whip')),

((SELECT id FROM Pokemon WHERE name='Meowth'), (SELECT id FROM Attacks WHERE name='Scratch')),
((SELECT id FROM Pokemon WHERE name='Meowth'), (SELECT id FROM Attacks WHERE name='Bite')),

((SELECT id FROM Pokemon WHERE name='Eevee'), (SELECT id FROM Attacks WHERE name='Growl')),
((SELECT id FROM Pokemon WHERE name='Eevee'), (SELECT id FROM Attacks WHERE name='Sand Attack')),

-- Ground-types
((SELECT id FROM Pokemon WHERE name='Geodude'), (SELECT id FROM Attacks WHERE name='Tackle')),
((SELECT id FROM Pokemon WHERE name='Geodude'), (SELECT id FROM Attacks WHERE name='Rock Throw')),

((SELECT id FROM Pokemon WHERE name='Sandshrew'), (SELECT id FROM Attacks WHERE name='Scratch')),
((SELECT id FROM Pokemon WHERE name='Sandshrew'), (SELECT id FROM Attacks WHERE name='Sand Attack')),

((SELECT id FROM Pokemon WHERE name='Diglett'), (SELECT id FROM Attacks WHERE name='Astonish')),
((SELECT id FROM Pokemon WHERE name='Diglett'), (SELECT id FROM Attacks WHERE name='Mud-Slap')),

-- Normal/Flying-types
((SELECT id FROM Pokemon WHERE name='Pidgey'), (SELECT id FROM Attacks WHERE name='Gust')),
((SELECT id FROM Pokemon WHERE name='Pidgey'), (SELECT id FROM Attacks WHERE name='Sand Attack')),

((SELECT id FROM Pokemon WHERE name='Spearow'), (SELECT id FROM Attacks WHERE name='Leer')),
((SELECT id FROM Pokemon WHERE name='Spearow'), (SELECT id FROM Attacks WHERE name='Wing Attack')),

((SELECT id FROM Pokemon WHERE name='Doduo'), (SELECT id FROM Attacks WHERE name='Peck')),
((SELECT id FROM Pokemon WHERE name='Doduo'), (SELECT id FROM Attacks WHERE name='Agility'));


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