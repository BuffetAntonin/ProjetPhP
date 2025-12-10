<?php

namespace App\Repository;

use PDO;
use Exception;
use PDOException;
use App\Models\Page;
use App\Repository\Connexion;

class PageRepository
{
    // Instance unique (Singleton)
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
        $sql = 'INSERT INTO public."page" (titre, slug, contenu, id_utilisateur, est_publie, date_creation, date_modification) VALUES (:titre, :slug, :contenu, :id_user, :publie, :cree, :modif)';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':titre',   $page->getTitre());
            $stmt->bindValue(':slug',    $page->getSlug());
            $stmt->bindValue(':contenu', $page->getContenu());
            $stmt->bindValue(':id_user', $page->getIdUtilisateur(), PDO::PARAM_INT);
            // PostgreSQL comprend très bien les booléens via PDO::PARAM_BOOL
            $stmt->bindValue(':publie',  $page->isEstPublie(), PDO::PARAM_BOOL);
            $stmt->bindValue(':cree',    $page->getDateCreation()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':modif',   $page->getDateModification()->format('Y-m-d H:i:s'));
            
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new Exception("Ce slug existe déjà dans la base de données.");
            }
            throw $e;
        }
    }

    public function findById(int $idPage , int $idUser): ?Page
    {
        $sql = "SELECT * FROM public.page WHERE id_page = :id and id_utilisateur = :idUser";
        $stmt = $this->db->prepare($sql);
        // Utilisation de bindValue pour forcer le type entier
        $stmt->bindValue(':idUser', $idUser, PDO::PARAM_INT);
        $stmt->bindValue(':id', $idPage, PDO::PARAM_INT);
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
        $p->setIdPage($row['id_page']);
        return $p;
    }

        public function findAllByUserId(int $userId): array
    {
        // 1. On récupère d'abord le rôle de l'utilisateur
        $roleSql = "SELECT id_role FROM public.users WHERE id = :id";
        $roleStmt = $this->db->prepare($roleSql);
        $roleStmt->execute([':id' => $userId]);
        
        // fetchColumn récupère directement la valeur de id_role
        $roleId = (int) $roleStmt->fetchColumn();

        // 2. On prépare la requête pour les pages selon le rôle
        if ($roleId === 1) {
            // C'est un ADMIN : On prend TOUT (pas de WHERE)
            $sql = "SELECT * FROM public.page ORDER BY date_creation DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            // C'est un USER normal : On filtre par son ID
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
            $pageObject->setIdPage($row['id_page']);
            
            $pagesList[] = $pageObject;
        }

        return $pagesList;
    }

    public function deletePage(int $idPage): bool
    {
        $sql = "DELETE FROM public.page WHERE id_page = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idPage, PDO::PARAM_INT);
        return $stmt->execute();
    }


    public function updateStatus(int $idPage): bool
    {
        // 1. On récupère le statut actuel
        $sql = "SELECT est_publie FROM public.page WHERE id_page = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idPage, PDO::PARAM_INT);
        $stmt->execute();
        $estPublie = (bool)$stmt->fetchColumn();

        // 2. On met à jour avec l'INVERSE (not)
        $sql = "UPDATE public.page SET est_publie = :etat WHERE id_page = :id";
        $stmt = $this->db->prepare($sql);
        
        // On utilise bindValue avec PARAM_BOOL pour gérer le booléen proprement
        $stmt->bindValue(':etat', !$estPublie, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $idPage, PDO::PARAM_INT);
        
        return $stmt->execute();
    }


        public function updatePage(Page $page): bool
    {
        // On met à jour le titre, slug, contenu, statut et la date de modif
        // On NE touche PAS à la date de création ni à l'id_utilisateur (le propriétaire ne change pas)
        $sql = 'UPDATE public."page" 
                SET titre = :titre, 
                    slug = :slug, 
                    contenu = :contenu, 
                    est_publie = :publie, 
                    date_modification = :modif
                WHERE id_page = :id';

        try {
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindValue(':titre',   $page->getTitre());
            $stmt->bindValue(':slug',    $page->getSlug());
            $stmt->bindValue(':contenu', $page->getContenu());
            $stmt->bindValue(':publie',  $page->isEstPublie(), PDO::PARAM_BOOL);
            
            // On met la date de modification à "Maintenant"
            $stmt->bindValue(':modif',   date('Y-m-d H:i:s'));
            
            // L'ID est indispensable pour le WHERE
            $stmt->bindValue(':id',      $page->getIdPage(), PDO::PARAM_INT);

            $stmt->execute();
            return true;

        } catch (PDOException $e) {
            // Gestion du doublon de Slug (si on renomme vers un slug qui existe déjà)
            if ($e->getCode() === '23505') {
                throw new Exception("Ce slug est déjà pris par une autre page.");
            }
            throw $e;
        }
    }


    public function findAllPublished(): array
    {
        // On récupère uniquement celles qui sont publiées (true)
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
            $p->setIdPage($row['id_page']);
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
        $p->setIdPage($row['id_page']);
        return $p;
    }

}