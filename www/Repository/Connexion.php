<?php

namespace App\Repository;

use PDO;
use Exception;

class Connexion
{
    private static ?PDO $instance = null;

    private static string $dsn = "pgsql:host=localhost;port=5432;dbname=votre_bdd;";
    private static string $user = "postgres";
    private static string $password = "votre_mot_de_passe";

    // Empêche l'instanciation directe
    private function __construct() {}

    // Empêche le clonage
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(self::$dsn, self::$user, self::$password);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                die("Erreur SQL : " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
