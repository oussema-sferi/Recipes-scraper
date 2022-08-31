<?php
namespace App\Database;
use PDO;
use PDOException;
class DatabaseOperations
{
    const SERVER_NAME = "localhost";
    const USERNAME = "root";
    const PASSWORD = "";
    private $conn;
    private int $recipesCounter = 0;
    public function __construct() {
        $this->conn = new PDO("mysql:host=" . self::SERVER_NAME, self::USERNAME, self::PASSWORD);
    }
    function createDB()
    {
        try {
            //Set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "CREATE DATABASE IF NOT EXISTS myrecipesdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $this->conn->exec($sql);
            // sql to create table
            echo "Database created successfully\n";
            //return $conn;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
    function createTable()
    {
        try {
            $this->conn = new PDO("mysql:host=" . self::SERVER_NAME . ";dbname=myrecipesdb", self::USERNAME, self::PASSWORD);
            //Set the PDO error mode to exception
            $this->conn->exec("set names utf8mb4");
            $statement = 'CREATE TABLE IF NOT EXISTS recipe ( 
            recipe_id   INT AUTO_INCREMENT,
            link  TEXT NULL, 
            title TEXT NULL, 
            small_description TEXT NULL, 
            ingredients   JSON NULL,
            energy_per_serving   JSON NULL,
            instructions   JSON NULL,
            images   JSON NULL,
            PRIMARY KEY(recipe_id)
        )';
            $this->conn->exec($statement);
            echo "recipe Table created successfully\n";
            //return $conn;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    function insertDataDB($link, $title, $description, $ingredients, $energy, $instructions, $images = null)
    {
        try {
            $statement = $this->conn->prepare( "INSERT INTO recipe (link, title, small_description, ingredients, energy_per_serving, instructions, images) VALUES (:slink, :stitle, :sdescription, :singredients, :senergy, :sinstructions, :simages)");
            $statement->execute([
                'slink' => $link,
                'stitle' => $title,
                'sdescription' => $description,
                'singredients' => $ingredients,
                'senergy' => $energy,
                'sinstructions' => $instructions,
                'simages' => $images,
            ]);
            //$conn->exec($sql);
            // sql to create table
            $this->recipesCounter++;
            echo "recipe $this->recipesCounter inserted successfully\n";
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}
