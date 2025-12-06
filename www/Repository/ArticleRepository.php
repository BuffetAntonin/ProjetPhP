<?php

namespace App\Repository;

use PDO;
use Exception;
use PDOException;

class ArticleRepository
{
    // Instance unique (Singleton)
    private static ?ArticleRepository $instance = null;
    private PDO $db;

    private function __construct()
    {
        $this->db = Connexion::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function findAllCategories(): array
    {
        $stmt = $this->db->query("SELECT * FROM public.categorie ORDER BY nom_categorie ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}