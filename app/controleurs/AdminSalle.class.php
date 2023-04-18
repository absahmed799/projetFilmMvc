<?php

/**
 * Classe Contrôleur des requêtes sur l'entité Salle de l'application admin
 */

class AdminSalle extends Admin {

  protected $methodes = [
    'l' => ['nom' => 'listerSalles',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    'a' => ['nom' => 'ajouterSalle',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]],
    'm' => ['nom' => 'modifierSalle',  'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    's' => ['nom' => 'supprimerSalle', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]

  ];

  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct() {
    $this->salle_numero = $_GET['salle_numero'] ?? null;
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Lister les salles
   */
  public function listerSalles() {
    $salles = $this->oRequetesSQL->getSalles();
    (new Vue)->generer(
      'vAdminSalles',
      [
        'oUtilConn'           => self::$oUtilConn,
        'titre'               => 'Gestion des salles',
        'salles'              => $salles,
        'classRetour'         => $this->classRetour,  
        'messageRetourAction' => $this->messageRetourAction
      ],
      'gabarit-admin');
  }

  /**
   * Ajouter une salle
   */
  public function ajouterSalle() {
    if (count($_POST) !== 0) {
      $salle = $_POST;
      $oSalle = new Salle($salle);
      $erreurs = $oSalle->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->ajouterSalle([
          'salle_numero'    => $oSalle->salle_numero,
          'salle_nb_places' => $oSalle->salle_nb_places
        ]);
        if (preg_match('/^[1-9]\d*$/', $retour)) {
          $this->messageRetourAction = "Ajout de la salle numéro $oSalle->salle_numero effectué.";
        } else {
          $this->classRetour = "erreur";         
          $this->messageRetourAction = "Ajout de la salle non effectué.";
        }
        $this->listerSalles();
        exit;
      }
    } else {
      $salle   = [];
      $erreurs = [];
    }
    
    (new Vue)->generer(
      'vAdminSalleAjouter',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre'     => 'Ajouter une salle',
        'salle'     => $salle,
        'erreurs'   => $erreurs
      ],
      'gabarit-admin');
  }

  /**
   * Modifier une salle
   */
  public function modifierSalle() {
    if (count($_POST) !== 0) {
      $salle = $_POST;
      $oSalle = new Salle($salle);
      $erreurs = $oSalle->erreurs;
      if (count($erreurs) === 0) {
        $retour = $this->oRequetesSQL->modifierSalle([
          'salle_numero'    => $oSalle->salle_numero, 
          'salle_nb_places' => $oSalle->salle_nb_places
        ]);
        if ($retour === true)  {
          $this->messageRetourAction = "Modification de la salle numéro $this->salle_numero effectuée.";    
        } else {  
          $this->classRetour = "erreur";
          $this->messageRetourAction = "Modification de la salle numéro $this->salle_numero non effectuée.";
        }
        $this->listerSalles();
        exit;
      }
    } else {
      $salle = $this->oRequetesSQL->getSalle($this->salle_numero);
      $erreurs = [];
    }
    
    (new Vue)->generer(
      'vAdminSalleModifier',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre'     => "Modifier la salle numéro $this->salle_numero",
        'salle'     => $salle,
        'erreurs'   => $erreurs
      ],
      'gabarit-admin');
  }
  
  /**
   * Supprimer une salle
   */
  public function supprimerSalle() {
    $retour = $this->oRequetesSQL->supprimerSalle($this->salle_numero);
    if ($retour === false) $this->classRetour = "erreur";
    $this->messageRetourAction = "Suppression de la salle numéro $this->salle_numero ".($retour ? "" : "non ")."effectuée.";
    $this->listerSalles();
  }
}