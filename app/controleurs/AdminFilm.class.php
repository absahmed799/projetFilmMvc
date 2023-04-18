<?php

/**
 * Classe Contrôleur des requêtes sur l'entité Film de l'application admin
 */

class AdminFilm extends Admin {

  protected $methodes = [
    'l' => ['nom' => 'listerFilms',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    'a' => ['nom' => 'ajouterFilm',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]],
    'm' => ['nom' => 'modifierFilm',  'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    's' => ['nom' => 'supprimerFilm', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
  ];

  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct() {
    $this->film_id = $_GET['film_id'] ?? null;
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Lister les films
   */
  public function listerFilms() {
    $films = $this->oRequetesSQL->getFilms('admin');
    (new Vue)->generer(
      'vAdminFilms',
      [
        'oUtilConn'           => self::$oUtilConn,
        'titre'               => 'Gestion des films',
        'films'               => $films,
        'classRetour'         => $this->classRetour,  
        'messageRetourAction' => $this->messageRetourAction        
      ],
      'gabarit-admin');
  }

  /**
   * Ajouter un film
   */
  public function ajouterFilm() {
    if (count($_POST) !== 0) {
      $film = $_POST;
      $oFilm = new Film($film);
      $oFilm->setFilm_affiche($_FILES['film_affiche']['name']); // pour contrôler le suffixe
      $erreurs = $oFilm->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->ajouterFilm([
          'film_titre'        => $oFilm->film_titre,
          'film_duree'        => $oFilm->film_duree,
          'film_annee_sortie' => $oFilm->film_annee_sortie,
          'film_resume'       => $oFilm->film_resume,
          'film_statut'       => $oFilm->film_statut,
          'film_genre_id'     => $oFilm->film_genre_id
        ]);
        if (preg_match('/^[1-9]\d*$/', $retour)) {         
          $this->messageRetourAction = "Ajout du film numéro $retour effectué.";
        } else {
          $this->classRetour = "erreur";
          $this->messageRetourAction = "Ajout du film non effectué. ".$retour;
        }
        $this->listerFilms();
        exit;
      }
    } else {
      $film    = [];
      $erreurs = [];
    }
    $genres = $this->oRequetesSQL->getGenres();

    (new Vue)->generer(
      'vAdminFilmAjouter',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre'     => 'Ajouter un film',
        'film'      => $film,
        'genres'    => $genres,
        'erreurs'   => $erreurs
      ],
      'gabarit-admin');
  }

  /**
   * Modifier un film
   */
  public function modifierFilm() {
    if (!preg_match('/^\d+$/', $this->film_id))
      throw new Exception("Numéro du film non renseigné pour une modification");

    if (count($_POST) !== 0) {
      $film = $_POST;
      $oFilm = new Film($film);
      if ($_FILES['film_affiche']['tmp_name'] !== "") $oFilm->setFilm_affiche($_FILES['film_affiche']['name']);
      $erreurs = $oFilm->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->modifierFilm([
          'film_id'           => $oFilm->film_id, 
          'film_titre'        => $oFilm->film_titre,
          'film_duree'        => $oFilm->film_duree,
          'film_annee_sortie' => $oFilm->film_annee_sortie,
          'film_resume'       => $oFilm->film_resume,
          'film_statut'       => $oFilm->film_statut,
          'film_genre_id'     => $oFilm->film_genre_id,
        ]);
        $this->messageRetourAction = "";
        if ($retour === true) {   
          $this->messageRetourAction .= "Modification du film numéro $this->film_id effectuée.";
        } else {
          $this->classRetour = "erreur";
          $this->messageRetourAction .= "modification du film numéro $this->film_id non effectuée.";
          if ($retour !== false) $this->messageRetourAction .= " ".$retour; 
        }
        $this->listerFilms();
        exit;
      }
    } else {
      $film = $this->oRequetesSQL->getFilm($this->film_id, 'admin');
      $erreurs = [];
    }
    $genres = $this->oRequetesSQL->getGenres();
    
    (new Vue)->generer(
      'vAdminFilmModifier',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre'     => "Modifier le film numéro $this->film_id",
        'film'      => $film,
        'genres'    => $genres,
        'erreurs'   => $erreurs
      ],
      'gabarit-admin');
  }
  
  /**
   * Supprimer un film
   */
  public function supprimerFilm() {
    if (!preg_match('/^\d+$/', $this->film_id))
      throw new Exception("Numéro de film incorrect pour une suppression.");

    $retour = $this->oRequetesSQL->supprimerFilm($this->film_id);
    if ($retour === true) {
      $this->messageRetourAction = "Suppression du film numéro $this->film_id effectuée.";
    } else {
      $this->classRetour = "erreur";
      $this->messageRetourAction = "Suppression du film numéro $this->film_id non effectuée. ".$retour;
    }
    $this->listerFilms();
  }
}