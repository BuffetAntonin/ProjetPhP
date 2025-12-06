<?php

namespace App\Repository;

use PDO;
use Exception;

class Connexion
{
    private static ?PDO $instance = null;

    private static string $dsn = "pgsql:dbname=devdb;host=db";

    // Empêche l'instanciation directe
    private function __construct() {}

    // Empêche le clonage
    private function __clone() {}

    public static function getInstance(): PDO
    {
        $user = getenv('POSTGRES_USER') ?: 'devuser';
        $password = getenv('POSTGRES_PASSWORD') ?: 'devpass';
        
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(self::$dsn, $user, $password);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                die("Erreur SQL : " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
