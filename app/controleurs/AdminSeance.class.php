<?php

/**
 * Classe Contrôleur des requêtes sur l'entité Seance de l'application admin
 */

class AdminSeance extends Admin {

  protected $methodes = [
    'l' => ['nom' => 'listerSeances',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    'a' => ['nom' => 'ajouterSeance',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]],
    'm' => ['nom' => 'modifierSeance',  'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    's' => ['nom' => 'supprimerSeance', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]

  ];

  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct() {
    $this->seance_date  = $_GET['seance_date']  ?? null;
    $this->seance_heure = $_GET['seance_heure'] ?? null;
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Lister les séances
   */
  public function listerSeances() {
    $seances = $this->oRequetesSQL->getSeances();
    (new Vue)->generer(
      'vAdminSeances',
      [
        'oUtilConn'           => self::$oUtilConn,
        'titre'               => 'Gestion des séances',
        'seances'             => $seances,
        'classRetour'         => $this->classRetour,  
        'messageRetourAction' => $this->messageRetourAction
      ],
      'gabarit-admin');
  }

  /**
   * Ajouter une séance
   */
  public function ajouterSeance() {
    throw new Exception("Développement en cours.");
  }

  /**
   * Modifier une séance
   */
  public function modifierSeance() {
    throw new Exception("Développement en cours.");
  }
  
  /**
   * Supprimer une séance
   */
  public function supprimerSeance() {
    throw new Exception("Développement en cours.");
  }
}