<?php

/**
 * Classe Routeur
 * analyse l'uri et exécute la méthode associée  
 *
 */
class Routeur {

  private $routes = [
  // uri,             classe,     méthode
  // ------------------------------------
    ["admin",         "Admin",    "gererEntite"],
    ["",              "Frontend", "listerAlaffiche"],
    ["prochainement", "Frontend", "listerProchainement"],
    ["film",          "Frontend", "voirFilm"],
    ["connecter",     "Frontend", "connecter"],
    ["creerCompte",   "Frontend", "creerCompte"],
    ["deconnecter",   "Frontend", "deconnecter"]
  ];

  protected $oRequetesSQL; // objet RequetesSQL utilisé par tous les contrôleurs

  // const BASE_URI = '/31B/TP3/'; // dossier racine du site par rapport au dossier racine d'Apache 
  const BASE_URI = '/'; // pour le PHP Server de Visual Studio Code

  const ERROR_FORBIDDEN = "HTTP 403";
  const ERROR_NOT_FOUND = "HTTP 404";
  
  /**
   * Valider l'URI
   * et instancier la méthode du contrôleur correspondante
   *
   */
  public function router() {
    try {

      // contrôle de l'uri si l'action coïncide

      $uri =  $_SERVER['REQUEST_URI'];
      if (strpos($uri, '?')) $uri = strstr($uri, '?', true);

      foreach ($this->routes as $route) {

        $routeUri     = self::BASE_URI.$route[0];
        $routeClasse  = $route[1];
        $routeMethode = $route[2];
        
        if ($routeUri ===  $uri) {
          // on exécute la méthode associée à l'uri
          $oRouteClasse = new $routeClasse;
          $oRouteClasse->$routeMethode();  
          exit;
        }
      }
      // aucune route ne correspond à l'uri
      throw new Exception(self::ERROR_NOT_FOUND);
    }
    catch (Error | Exception $e) {
      $this->erreur($e);
    }
  }

  /**
   * Méthode qui envoie un compte-rendu d'erreur
   * @param Exception $e
   */
  public function erreur($e) {
    $message = $e->getMessage();
    if ($message == self::ERROR_FORBIDDEN) {
      header('HTTP/1.1 403 Forbidden');
    } else if ($message == self::ERROR_NOT_FOUND) {
      header('HTTP/1.1 404 Not Found');
      (new Vue)->generer('vErreur404', [], 'gabarit-erreur');
    } else {
      header('HTTP/1.1 500 Internal Server Error');
      (new Vue)->generer("vErreur500",['e' => $e], 'gabarit-erreur');
    }
    exit;
  }
}