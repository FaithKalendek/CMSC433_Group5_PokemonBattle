<?php

// PDO for connecting to the MySQL server without specifying a database (for creating database)
function getServerPDO() {

    $connString = "mysql:host=localhost";
    $user = "root";
    $pass = "";

    try {

        $pdo = new PDO($connString, $user, $pass);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;

    } catch (PDOException $e) {
        die("Server connection failed: " . $e->getMessage());
    }
}

// PDO for connecting to the Pokemon database
function getPokemonPDO() {

    $connString = "mysql:host=localhost;dbname=Pokemon";
    $user = "root";
    $pass = "";
    
    try {
    
        $pdo = new PDO($connString, $user, $pass);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

?>
