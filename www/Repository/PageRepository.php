<?php

namespace App\Repository;

use PDO;
use Exception;
use PDOException;
use App\Models\Page;
use App\Repository\Connexion;

class PageRepository
{
    // Unique Instance (Singleton)
    private static ?PageRepository $instance = null;
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

    public function insertPage(Page $page): bool
    {
        // SQL remains untouched (French DB columns)
        $sql = 'INSERT INTO public."page" (titre, slug, contenu, id_utilisateur, est_publie, date_creation, date_modification) VALUES (:titre, :slug, :contenu, :id_user, :publie, :cree, :modif)';

        try {
            $stmt = $this->db->prepare($sql);
            
            // Binding: SQL params -> English Object Methods
            $stmt->bindValue(':titre',   $page->getTitle());
            $stmt->bindValue(':slug',    $page->getSlug());
            $stmt->bindValue(':contenu', $page->getContent());
            $stmt->bindValue(':id_user', $page->getUserId(), PDO::PARAM_INT);
            $stmt->bindValue(':publie',  $page->isPublished(), PDO::PARAM_BOOL);
            
            // Formatting dates
            $stmt->bindValue(':cree',    $page->getCreatedAt()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':modif',   $page->getUpdatedAt()->format('Y-m-d H:i:s'));
            
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new Exception("Ce slug existe déjà dans la base de données.");
            }
            throw $e;
        }
    }

    public function findById(int $pageId, int $userId): ?Page
    {
        $roleSql = "SELECT id_role FROM public.users WHERE id = :id";
        $roleStmt = $this->db->prepare($roleSql);
        $roleStmt->execute([':id' => $userId]);
        
        $roleId = (int) $roleStmt->fetchColumn();

        if ($roleId === 1) {
 
            $sql = "SELECT * FROM public.page WHERE id_page = :pageId";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':pageId', $pageId, \PDO::PARAM_INT);
        } else {

            $sql = "SELECT * FROM public.page WHERE id_page = :pageId AND id_utilisateur = :userId";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':pageId', $pageId, \PDO::PARAM_INT);
            $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        $page = new Page(
            $row['titre'],
            $row['slug'],
            $row['contenu'],
            $row['id_utilisateur'],
            $row['est_publie']
        );
        
        $page->setId($row['id_page']);
        
        if (isset($row['date_creation'])) {
            $page->setCreatedAt($row['date_creation']);
        }
        if (isset($row['date_modification'])) {
            $page->setUpdatedAt($row['date_modification']);
        }
        
        return $page;
    }

    public function findAllByUserId(int $userId): array
    {
        // 1. Get User Role
        $roleSql = "SELECT id_role FROM public.users WHERE id = :id";
        $roleStmt = $this->db->prepare($roleSql);
        $roleStmt->execute([':id' => $userId]);
        
        $roleId = (int) $roleStmt->fetchColumn();

        // 2. Prepare Query based on role
        if ($roleId === 1) {
            // ADMIN: Get everything
            $sql = "SELECT * FROM public.page ORDER BY date_creation DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            // USER: Filter by ID
            $sql = "SELECT * FROM public.page WHERE id_utilisateur = :id ORDER BY date_creation DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
        }
        
        $pagesList = [];
        
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $pageObject = new Page(
                $row['titre'],
                $row['slug'],
                $row['contenu'],
                $row['id_utilisateur'],
                $row['est_publie']
            );
            $pageObject->setId($row['id_page']);
            
            $pagesList[] = $pageObject;
        }

        return $pagesList;
    }

    public function deletePage(int $pageId): bool
    {
        $sql = "DELETE FROM public.page WHERE id_page = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $pageId, PDO::PARAM_INT);
        return $stmt->execute();
    }


    public function updateStatus(int $pageId): bool
    {
        // 1. Get current status
        $sql = "SELECT est_publie FROM public.page WHERE id_page = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $pageId, PDO::PARAM_INT);
        $stmt->execute();
        
        $isPublished = (bool)$stmt->fetchColumn();

        // 2. Update with inverse value
        $sql = "UPDATE public.page SET est_publie = :etat WHERE id_page = :id";
        $stmt = $this->db->prepare($sql);
        
        // Using negation (!) on the boolean variable
        $stmt->bindValue(':etat', !$isPublished, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $pageId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }


    public function updatePage(Page $page): bool
    {
        // SQL untouched
        $sql = 'UPDATE public."page" 
                SET titre = :titre, 
                    slug = :slug, 
                    contenu = :contenu, 
                    est_publie = :publie, 
                    date_modification = :modif
                WHERE id_page = :id';

        try {
            $stmt = $this->db->prepare($sql);
            
            // English getters used here
            $stmt->bindValue(':titre',   $page->getTitle());
            $stmt->bindValue(':slug',    $page->getSlug());
            $stmt->bindValue(':contenu', $page->getContent());
            $stmt->bindValue(':publie',  $page->isPublished(), PDO::PARAM_BOOL);
            
            // Set modification date to "Now"
            $stmt->bindValue(':modif',   date('Y-m-d H:i:s'));
            
            // ID is essential for WHERE clause
            $stmt->bindValue(':id',      $page->getId(), PDO::PARAM_INT);

            $stmt->execute();
            return true;

        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new Exception("Ce slug est déjà pris par une autre page.");
            }
            throw $e;
        }
    }


    public function findAllPublished(): array
    {
        $sql = "SELECT * FROM public.page WHERE est_publie = true ORDER BY titre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $pages = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $p = new Page(
                $row['titre'],
                $row['slug'],
                $row['contenu'],
                $row['id_utilisateur'],
                $row['est_publie']
            );
            $p->setId($row['id_page']);
            $pages[] = $p;
        }
        return $pages;
    }

    public function findBySlug(string $slug): ?Page
    {
        $sql = "SELECT * FROM public.page WHERE slug = :slug AND est_publie = true LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        $p = new Page(
            $row['titre'],
            $row['slug'],
            $row['contenu'],
            $row['id_utilisateur'],
            $row['est_publie']
        );
        $p->setId($row['id_page']);
        return $p;
    }

    public function isAdmin(int $userId): bool
    {
        $sql = "SELECT id_role FROM public.users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        
        // On récupère l'ID du rôle
        $roleId = (int) $stmt->fetchColumn();

        // On retourne true si le rôle est 1 (Admin), sinon false
        return $roleId === 1;
    }
}