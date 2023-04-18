<?php

/**
 * Classe Contrôleur des requêtes de l'interface frontend
 * 
 */

class Frontend extends Routeur {

  private $film_id;

  private $oUtilConn;
  
  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct() {
    $this->oUtilConn = $_SESSION['oUtilConn'] ?? null; 
    $this->film_id   = $_GET['film_id']       ?? null; 
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Connecter un utilisateur
   */
  public function connecter() {
    $utilisateur = $this->oRequetesSQL->connecter($_POST);
    if ($utilisateur !== false) {
      $_SESSION['oUtilConn'] = new Utilisateur($utilisateur);
    }
    echo json_encode($utilisateur);
  }

  /**
   * Créer un compte utilisateur
   */
  public function creerCompte() {
    $oUtilisateur = new Utilisateur($_POST);
    $erreurs = $oUtilisateur->erreurs;
    if (count($erreurs) > 0) {
      $retour = $erreurs;
    } else {
      $retour = $this->oRequetesSQL->creerCompteClient($_POST);
      if (!is_array($retour) && preg_match('/^[1-9]\d*$/', $retour)) {
        $oUtilisateur->utilisateur_profil = Utilisateur::PROFIL_CLIENT;
        $_SESSION['oUtilConn'] = $oUtilisateur;
      } 
    }
    echo json_encode($retour);
  }

  /**
   * Déconnecter un utilisateur
   */
  public function deconnecter() {
    unset ($_SESSION['oUtilConn']);
    echo json_encode(true);
  }

  /**
   * Lister les films à l'affiche
   * 
   */  
  public function listerAlaffiche() {
    $films = $this->oRequetesSQL->getFilms('enSalle');
    (new Vue)->generer(
      "vListeFilms",
      [
        'oUtilConn' => $this->oUtilConn,
        'titre'     => "À l'affiche",
        'films'     => $films
      ],
      "gabarit-frontend");
  }

  /**
   * Lister les films diffusés prochainement
   * 
   */  
  public function listerProchainement() {
    $films = $this->oRequetesSQL->getFilms('prochainement');
    (new Vue)->generer(
      "vListeFilms",
      [
        'oUtilConn' => $this->oUtilConn,
        'titre'     => "Prochainement",
        'films'     => $films
      ],
      "gabarit-frontend");
  }

  /**
   * Voir les informations d'un film
   * 
   */  
  public function voirFilm() {
    $film = false;
    if (!is_null($this->film_id)) {
      $film = $this->oRequetesSQL->getFilm($this->film_id);
      $realisateurs = $this->oRequetesSQL->getRealisateursFilm($this->film_id);
      $pays         = $this->oRequetesSQL->getPaysFilm($this->film_id);
      $acteurs      = $this->oRequetesSQL->getActeursFilm($this->film_id);

      // si affichage avec vFilm2.twig
      // =============================
      // $seances      = $this->oRequetesSQL->getSeancesFilm($this->film_id); 

      // si affichage avec vFilm.twig
      // ============================
      $seancesTemp  = $this->oRequetesSQL->getSeancesFilm($this->film_id);
      $seances = [];
      foreach ($seancesTemp as $seance) {
        $seances[$seance['seance_date']]['jour']     = $seance['seance_jour'];
        $seances[$seance['seance_date']]['heures'][] = $seance['seance_heure'];
      }
    }
    if (!$film) throw new Exception("Film inexistant.");

    (new Vue)->generer(
      "vFilm",
      [
        'oUtilConn'    => $this->oUtilConn,
        'titre'        => $film['film_titre'],
        'film'         => $film,
        'realisateurs' => $realisateurs,
        'pays'         => $pays,
        'acteurs'      => $acteurs,
        'seances'      => $seances
      ],
      "gabarit-frontend");
  }
}