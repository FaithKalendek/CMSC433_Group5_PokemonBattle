<?php

    try {
    
        require 'pdo.php'; // has getServerPDO and getPokemonPDO functions

        // Connect to the MySQL server
        $serverPDO = getServerPDO();

        // Create the Pokemon database if it does not exist
        $serverPDO->exec("CREATE DATABASE IF NOT EXISTS Pokemon");

        // Connect to the Pokemon database
        $pdo = getPokemonPDO();

        // Gets SQL commands from proj3.sql file and executes them to set up the Pokemon database
        $sql = file_get_contents('proj3.sql');
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if ($statement) {
                $pdo->exec($statement);
            }
        }

        $pdo = null;

    } catch (PDOException $e) {
        die($e->getMessage());
    }
?>