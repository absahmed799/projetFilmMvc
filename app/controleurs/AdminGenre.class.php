<?php

/**
 * Classe Contrôleur des requêtes sur l'entité Genre de l'application admin
 */

class AdminGenre extends Admin {

  protected $methodes = [
    'l' => ['nom' => 'listerGenres',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    'a' => ['nom' => 'ajouterGenre',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]],
    'm' => ['nom' => 'modifierGenre',  'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    's' => ['nom' => 'supprimerGenre', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
  ];

  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct() {
    $this->genre_id = $_GET['genre_id'] ?? null;
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Lister les genres
   */
  public function listerGenres() {
    $genres = $this->oRequetesSQL->getGenres();
    (new Vue)->generer(
      'vAdminGenres',
      [
        'oUtilConn'           => self::$oUtilConn,
        'titre'               => 'Gestion des genres',
        'genres'              => $genres,
        'classRetour'         => $this->classRetour,  
        'messageRetourAction' => $this->messageRetourAction
      ],
      'gabarit-admin');
  }

  /**
   * Ajouter un genre
   */
  public function ajouterGenre() {
    if (count($_POST) !== 0) {
      $genre = $_POST;
      $oGenre = new Genre($genre);
      $erreurs = $oGenre->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->ajouterGenre([
          'genre_id'  => $oGenre->genre_id,
          'genre_nom' => $oGenre->genre_nom
        ]);
        if (preg_match('/^[1-9]\d*$/', $retour)) {
          $this->messageRetourAction = "Ajout du genre numéro $oGenre->genre_id effectué.";
        } else {
          $this->classRetour = "erreur";         
          $this->messageRetourAction = "Ajout du genre non effectué.";
        }
        $this->listerGenres();
        exit;
      }
    } else {
      $genre   = [];
      $erreurs = [];
    }
    
    (new Vue)->generer(
      'vAdminGenreAjouter',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre'     => 'Ajouter un genre',
        'genre'     => $genre,
        'erreurs'   => $erreurs
      ],
      'gabarit-admin');
  }

  /**
   * Modifier un genre
   */
  public function modifierGenre() {
    if (count($_POST) !== 0) {
      $genre = $_POST;
      $oGenre = new Genre($genre);
      $erreurs = $oGenre->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->modifierGenre([
          'genre_id'  => $oGenre->genre_id, 
          'genre_nom' => $oGenre->genre_nom
        ]);
        if ($retour === true)  {
          $this->messageRetourAction = "Modification du genre numéro $this->genre_id effectuée.";    
        } else {  
          $this->classRetour = "erreur";
          $this->messageRetourAction = "Modification du genre numéro $this->genre_id non effectuée.";
        }
        $this->listerGenres();
        exit;
      }
    } else {
      $genre = $this->oRequetesSQL->getGenre($this->genre_id);
      $erreurs = [];
    }
    
    (new Vue)->generer(
      'vAdminGenreModifier',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre'     => "Modifier le genre numéro $this->genre_id",
        'genre'     => $genre,
        'erreurs'   => $erreurs
      ],
      'gabarit-admin');
  }
    
  /**
   * Supprimer un genre
   */
  public function supprimerGenre() {
    $retour = $this->oRequetesSQL->supprimerGenre($this->genre_id);
    if ($retour === false) $this->classRetour = "erreur";
    $this->messageRetourAction = "Suppression du genre numéro $this->genre_id ".($retour ? "" : "non ")."effectuée.";
    $this->listerGenres();
  }
}