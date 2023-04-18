<?php

/**
 * Classe Contrôleur des requêtes sur l'entité Realisateur de l'application admin
 */

class AdminRealisateur extends Admin {

  protected $methodes = [
    'l' => ['nom' => 'listerRealisateurs',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    'a' => ['nom' => 'ajouterRealisateur',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]],
    'm' => ['nom' => 'modifierRealisateur',  'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    's' => ['nom' => 'supprimerRealisateur', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
  ];

  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct() {
    $this->realisateur_id = $_GET['realisateur_id'] ?? null;
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Lister les realisateurs
   */
  public function listerRealisateurs() {
    $realisateurs = $this->oRequetesSQL->getRealisateurs();
    (new Vue)->generer(
      'vAdminRealisateurs',
      [
        'oUtilConn'           => self::$oUtilConn,
        'titre'               => 'Gestion des réalisateurs',
        'realisateurs'        => $realisateurs,
        'classRetour'         => $this->classRetour,  
        'messageRetourAction' => $this->messageRetourAction
      ],
      'gabarit-admin');
  }

  /**
   * Ajouter un réalisateur
   */
  public function ajouterRealisateur() {
    if (count($_POST) !== 0) {
      $realisateur  = $_POST;
      $oRealisateur = new Realisateur($realisateur);
      $erreurs = $oRealisateur->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->ajouterRealisateur([
          'realisateur_nom'    => $oRealisateur->realisateur_nom,
          'realisateur_prenom' => $oRealisateur->realisateur_prenom
        ]);
        if (preg_match('/^[1-9]\d*$/', $retour)) {
          $this->messageRetourAction = "Ajout du réalisateur numéro $retour effectué.";
        } else {
          $this->classRetour         = "erreur";         
          $this->messageRetourAction = "Ajout du réalisateur non effectué.";
        }
        $this->listerRealisateurs();
        exit;
      }
    } else {
      $realisateur  = [];
      $erreurs      = [];
    }
    
    (new Vue)->generer(
      'vAdminRealisateurAjouter',
      [
        'oUtilConn'   => self::$oUtilConn,
        'titre'       => 'Ajouter un réalisateur',
        'realisateur' => $realisateur,
        'erreurs'     => $erreurs
      ],
      'gabarit-admin');
  }

  /**
   * Modifier un réalisateur
   */
  public function modifierRealisateur() {
    if (count($_POST) !== 0) {
      $realisateur  = $_POST;
      $oRealisateur = new Realisateur($realisateur);
      $erreurs = $oRealisateur->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->modifierRealisateur([
          'realisateur_id'     => $oRealisateur->realisateur_id, 
          'realisateur_nom'    => $oRealisateur->realisateur_nom,
          'realisateur_prenom' => $oRealisateur->realisateur_prenom
        ]);
        if ($retour === true)  {
          $this->messageRetourAction = "Modification de l'realisateur numéro $this->realisateur_id effectuée.";    
        } else {  
          $this->classRetour         = "erreur";
          $this->messageRetourAction = "Modification de l'realisateur numéro $this->realisateur_id non effectuée.";
        }
        $this->listerRealisateurs();
        exit;
      }
    } else {
      $realisateur  = $this->oRequetesSQL->getRealisateur($this->realisateur_id);
      $erreurs = [];
    }
    
    (new Vue)->generer(
      'vAdminRealisateurModifier',
      [
        'oUtilConn'   => self::$oUtilConn,
        'titre'       => "Modifier l'realisateur numéro $this->realisateur_id",
        'realisateur' => $realisateur,
        'erreurs'     => $erreurs
      ],
      'gabarit-admin');
  }
  
  /**
   * Supprimer un réalisateur
   */
  public function supprimerRealisateur() {
    $retour = $this->oRequetesSQL->supprimerRealisateur($this->realisateur_id);
    if ($retour === false) $this->classRetour = "erreur";
    $this->messageRetourAction = "Suppression du réalisateur numéro $this->realisateur_id ".($retour ? "" : "non ")."effectuée.";
    $this->listerRealisateurs();
  }
}