<?php
namespace App\Repository;

use PDO;
use App\Models\Categorie;

class CategorieRepository
{
    private PDO $db;
    private static ?CategorieRepository $instance = null;

    private function __construct() {
        $this->db = Connexion::getInstance();
    }

    public static function getInstance(): self {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM public.categorie ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function insert(Categorie $cat): int
    {
        $sql = "INSERT INTO public.categorie (nom) VALUES (:nom) RETURNING id_categorie";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nom', $cat->getNom());
        $stmt->execute();
        
        // On récupère l'ID généré
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['id_categorie'];
    }
}