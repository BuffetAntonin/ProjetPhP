<?php

namespace App;

use App\Controllers\Base;
use App\Repository\PageRepository;
// Ensure session cookie is available site-wide and session is started
\session_set_cookie_params(0, "/");
\session_start();
/*
 *
 * TP : Routing
 *
 * Faire en sorte que toutes les requêtes HTTP pointent sur le fichier index.php se trouvant dans public
 * Se baser ensuite sur le fichier routes.yml pour appeler la bonne classe dans le dossier controller et
 * la bonne methode (ce que l'on appel une action dans un controller)
 *
 * Exemple :
 * http://localhost:8080/contact
 * Doit créer une instance de Base et appeler la méthode (action) : contact
 * $controller = new Base();
 * $controller->contact();
 *
 * Pensez à effectuer tous les nettoyages et toutes les vérifications pour
 * afficher des erreurs (des simples die suffiront dans un premier temps)
 *
 * Rendu : Mail y.skrzypczyk@gmail.com
 * Objet du mail : 3IW1 - TP routing - Nom Prénom
 * Contenu du mail : fichier index.php et les autres fichiers créés s'il y en a
 *
 * Bon courage
 */

spl_autoload_register(function ($class){
    $class = str_ireplace(["\\", "App"], ["/", ".."],$class);
    if(file_exists($class.".php")){
        include $class.".php";
    }
});


$requestUri = strtok($_SERVER["REQUEST_URI"], "?");
if(strlen($requestUri)>1)
    $requestUri = rtrim($requestUri, "/");
$requestUri = strtolower($requestUri);

$routes = \yaml_parse_file("../routes.yml");


if(!empty($routes[$requestUri])){
    $controllerName = $routes[$requestUri]["controller"];
    $actionName = $routes[$requestUri]["action"];
} 
else {
    // 2. Si pas trouvé, on regarde si c'est une page dynamique en BDD
    // On doit enlever le slash initial pour chercher le slug (ex: "/test" -> "test")
    $slug = ltrim($requestUri, '/'); 
    
    $page = PageRepository::getInstance()->findBySlug($slug);

    if($page) {
        // Si la page existe, on force le controller Base et l'action showDynamicPage
        $controllerName = "Base";
        $actionName = "showPage";
        // Astuce : on passe le slug via $_GET pour le récupérer dans le controller ou on modifie l'appel
        // Pour faire simple ici, modifions l'appel de méthode plus bas
        $paramToPass = $slug; 
    }
}

// Si après YAML et BDD, on a toujours rien -> 404
if(!$controllerName || !$actionName){
    die("Aucune route pour cette uri : page 404");
}

// C'est ici que ton snippet intervient pour unifier les noms
$controller = $controllerName;
$action = $actionName;

// Vérification de l'existence du fichier
if(!file_exists("../Controllers/".$controller.".php")){
    die("Aucun fichier controller pour cette uri");
}


include "../Controllers/".$controller.".php";

$controller = "App\\Controllers\\".$controller;
if(!class_exists($controller)){
    die("La classe du controller n'existe pas");
}

$objetController = new $controller();

if(!method_exists($objetController, $action)){
    die("La methode du controller n'existe pas");
}

if (isset($paramToPass)) {
    $objetController->$action($paramToPass);
} else {
    $objetController->$action();
}