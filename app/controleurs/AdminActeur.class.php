<?php

/**
 * Classe Contrôleur des requêtes sur l'entité Acteur de l'application admin
 */

class AdminActeur extends Admin {

  protected $methodes = [
    'l' => ['nom' => 'listerActeurs',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    'a' => ['nom' => 'ajouterActeur',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]],
    'm' => ['nom' => 'modifierActeur',  'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    's' => ['nom' => 'supprimerActeur', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
  ];

  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct() {
    $this->acteur_id = $_GET['acteur_id'] ?? null;
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Lister les Acteurs
   */
  public function listerActeurs() {
    $acteurs = $this->oRequetesSQL->getActeurs();
    (new Vue)->generer(
      'vAdminActeurs',
      [
        'oUtilConn'           => self::$oUtilConn,
        'titre'               => 'Gestion des Acteurs',
        'acteurs'             => $acteurs,
        'classRetour'         => $this->classRetour,  
        'messageRetourAction' => $this->messageRetourAction
      ],
      'gabarit-admin');
  }

  /**
   * Ajouter un acteur
   */
  public function ajouterActeur() {
    if (count($_POST) !== 0) {
      $acteur = $_POST;
      $oActeur = new Acteur($acteur);
      $erreurs = $oActeur->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->ajouterActeur([
          'acteur_nom'    => $oActeur->acteur_nom,
          'acteur_prenom' => $oActeur->acteur_prenom
        ]);
        if (preg_match('/^[1-9]\d*$/', $retour)) {
          $this->messageRetourAction = "Ajout de l'acteur numéro $retour effectué.";
        } else {
          $this->classRetour = "erreur";         
          $this->messageRetourAction = "Ajout de l'acteur non effectué.";
        }
        $this->listerActeurs();
        exit;
      }
    } else {
      $acteur  = [];
      $erreurs = [];
    }
    
    (new Vue)->generer(
      'vAdminActeurAjouter',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre'     => 'Ajouter un acteur',
        'acteur'    => $acteur,
        'erreurs'   => $erreurs
      ],
      'gabarit-admin');
  }

  /**
   * Modifier un acteur
   */
  public function modifierActeur() {
    if (count($_POST) !== 0) {
      $acteur = $_POST;
      $oActeur = new Acteur($acteur);
      $erreurs = $oActeur->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->modifierActeur([
          'acteur_id'     => $oActeur->acteur_id, 
          'acteur_nom'    => $oActeur->acteur_nom,
          'acteur_prenom' => $oActeur->acteur_prenom
        ]);
        if ($retour === true)  {
          $this->messageRetourAction = "Modification de l'acteur numéro $this->acteur_id effectuée.";    
        } else {  
          $this->classRetour = "erreur";
          $this->messageRetourAction = "Modification de l'acteur numéro $this->acteur_id non effectuée.";
        }
        $this->listerActeurs();
        exit;
      }
    } else {
      $acteur  = $this->oRequetesSQL->getActeur($this->acteur_id);
      $erreurs = [];
    }
    
    (new Vue)->generer(
      'vAdminActeurModifier',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre'     => "Modifier l'acteur numéro $this->acteur_id",
        'acteur'    => $acteur,
        'erreurs'   => $erreurs
      ],
      'gabarit-admin');
  }
  
  /**
   * Supprimer un acteur
   */
  public function supprimerActeur() {
    $retour = $this->oRequetesSQL->supprimerActeur($this->acteur_id);
    if ($retour === false) $this->classRetour = "erreur";
    $this->messageRetourAction = "Suppression de l'acteur numéro $this->acteur_id ".($retour ? "" : "non ")."effectuée.";
    $this->listerActeurs();
  }
  
}