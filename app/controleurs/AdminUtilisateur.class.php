<?php

/**
 * Classe Contrôleur des requêtes sur l'entité Utilisateur de l'application admin
 */

class AdminUtilisateur extends Admin {

  protected $methodes = [
    'l'           => ['nom'    => 'listerUtilisateurs',  'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]],
    'a'           => ['nom'    =>'ajouterUtilisateur',   'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]],
    'm'           => ['nom'    =>'modifierUtilisateur',  'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]],
    's'           => ['nom'    =>'supprimerUtilisateur', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]],
    'd'           => ['nom'    =>'deconnecter'],
    'generer_mdp' => ['nom'    =>'genererMdp',           'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]]
  ];

  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct() {
    $this->utilisateur_id = $_GET['utilisateur_id'] ?? null; 
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Connecter un utilisateur et lui faire changer son mot de passe s'il n'a pas été saisi
   */
  public function connecter() {
    $renouvelerMdp                 = $_SESSION['renouvelerMdp'] ?? "non";
    $messageErreurConnexion        = "";
    $utilisateur['utilisateur_id'] = $_SESSION['utilisateur_id'] ?? null;
    $erreurs                       = [];
    
    if ($renouvelerMdp === "non") {
      if (count($_POST) !== 0) {
        $utilisateur = $this->oRequetesSQL->connecter($_POST);
        if ($utilisateur !== false) {
          if ($utilisateur['utilisateur_renouveler_mdp'] === "non") {
            unset($utilisateur['utilisateur_renouveler_mdp']);
            $_SESSION['oUtilConn'] = new Utilisateur($utilisateur);
            parent::gererEntite();
            exit;
          } else {
            unset($utilisateur['utilisateur_renouveler_mdp']);
            $_SESSION['oUtilConnSauve'] = new Utilisateur($utilisateur);
            $_SESSION['utilisateur_id'] = $utilisateur['utilisateur_id'];
            $_SESSION['renouvelerMdp']  = "oui";
            $renouvelerMdp = "oui";
          }         
        } else {
          $messageErreurConnexion = "Courriel ou mot de passe incorrect.";
        }
      }
    } else {
      if (count($_POST) !== 0 && !isset($_POST['utilisateur_courriel'])) {
        $utilisateur = $_POST;
        $oUtilisateur = new Utilisateur($utilisateur);
        $erreurs = $oUtilisateur->erreurs;
        if (count($erreurs) === 0) {
          $this->oRequetesSQL->modifierUtilisateurMdpSaisi([
            'utilisateur_id'  => $utilisateur['utilisateur_id'], 
            'utilisateur_mdp' => $oUtilisateur->nouveau_mdp
          ]);
          $_SESSION['oUtilConn'] = $_SESSION['oUtilConnSauve'];
          unset($_SESSION['oUtilConnSauve']);
          unset($_SESSION['utilisateur_id']);
          unset($_SESSION['renouvelerMdp']);
          parent::gererEntite();
          exit;
        }
      }
    } 

    (new Vue)->generer(
      'modaleConnexion',
      [
        'titre'                  => 'Connexion',
        'renouvelerMdp'          => $renouvelerMdp,
        'messageErreurConnexion' => $messageErreurConnexion,
        'utilisateur'            => $utilisateur,
        'erreurs'                => $erreurs
      ],
      'gabarit-admin-min');
  }

  /**
   * Déconnecter un utilisateur
   */
  public function deconnecter() {
    unset ($_SESSION['oUtilConn']);
    parent::gererEntite();
  }

  /**
   * Lister les utilisateurs
   */
  public function listerUtilisateurs() {
    $utilisateurs = $this->oRequetesSQL->getUtilisateurs();

    (new Vue)->generer(
      'vAdminUtilisateurs',
      [
        'oUtilConn'           => self::$oUtilConn,
        'titre'               => 'Gestion des utilisateurs',
        'utilisateurs'        => $utilisateurs,
        'classRetour'         => $this->classRetour,  
        'messageRetourAction' => $this->messageRetourAction
      ],
      'gabarit-admin');
  }

  /**
   * Ajouter un utilisateur
   */
  public function ajouterUtilisateur() {
    if (count($_POST) !== 0) {
      $utilisateur = $_POST;
      $oUtilisateur = new Utilisateur($utilisateur);
      $oUtilisateur->courrielExiste();
      $erreurs = $oUtilisateur->erreurs;
      if (count($erreurs) === 0) {
        $oUtilisateur->genererMdp();
        $retour = $this->oRequetesSQL->ajouterUtilisateur([
          'utilisateur_nom'      => $oUtilisateur->utilisateur_nom,
          'utilisateur_prenom'   => $oUtilisateur->utilisateur_prenom,
          'utilisateur_courriel' => $oUtilisateur->utilisateur_courriel,
          'utilisateur_mdp'      => $oUtilisateur->utilisateur_mdp,
          'utilisateur_profil'   => $oUtilisateur->utilisateur_profil
        ]);
        if ($retour !== Utilisateur::ERR_COURRIEL_EXISTANT) {
          if (preg_match('/^[1-9]\d*$/', $retour)) {
            $this->messageRetourAction = "Ajout de l'utilisateur numéro $retour effectué.";
            $retour = (new GestionCourriel)->envoyerMdp($oUtilisateur); 
            $this->messageRetourAction .= $retour
                                          ? " Courriel envoyé à l'utilisateur."
                                          : " Erreur d'envoi d'un courriel à l'utilisateur.";
            if (ENV === "DEV") {
              $this->messageRetourAction .= "<br>Message dans le fichier <a href='$retour' target='_blank'>$retour</a>";
            }   
          } else {
            $this->classRetour = "erreur";         
            $this->messageRetourAction = "Ajout de l'utilisateur non effectué.";
          }
          $this->listerUtilisateurs();
          exit;
        } else {
          $erreurs['utilisateur_courriel'] = $retour;
        }
      }
    } else {
      $utilisateur = [];
      $erreurs     = [];
    }
    
    (new Vue)->generer(
      'vAdminUtilisateurAjouter',
      [
        'oUtilConn'   => self::$oUtilConn,
        'titre'       => 'Ajouter un utilisateur',
        'utilisateur' => $utilisateur,
        'erreurs'     => $erreurs
      ],
      'gabarit-admin');
  }

  /**
   * Modifier un utilisateur
   */
  public function modifierUtilisateur() {
    if (!preg_match('/^\d+$/', $this->utilisateur_id))
      throw new Exception("Numéro d'utilisateur non renseigné pour une modification");

    if (count($_POST) !== 0) {
    $utilisateur = $_POST;
    $oUtilisateur = new Utilisateur($utilisateur);
    $oUtilisateur->courrielExiste();
    $erreurs = $oUtilisateur->erreurs;
    if (count($erreurs) === 0) {
      $retour = $this->oRequetesSQL->modifierUtilisateur([
        'utilisateur_id'       => $oUtilisateur->utilisateur_id, 
        'utilisateur_courriel' => $oUtilisateur->utilisateur_courriel,
        'utilisateur_nom'      => $oUtilisateur->utilisateur_nom,
        'utilisateur_prenom'   => $oUtilisateur->utilisateur_prenom,
        'utilisateur_profil'   => $oUtilisateur->utilisateur_profil
      ]);
      if ($retour !== Utilisateur::ERR_COURRIEL_EXISTANT) {
        if ($retour === true)  {
          $this->messageRetourAction = "Modification de l'utilisateur numéro $this->utilisateur_id effectuée.";    
        } else {  
          $this->classRetour = "erreur";
          $this->messageRetourAction = "Modification de l'utilisateur numéro $this->utilisateur_id non effectuée.";
        }
        $this->listerUtilisateurs();
        exit;
      } else {
        $erreurs['utilisateur_courriel'] = $retour;
      }
    }
  } else {
    $utilisateur = $this->oRequetesSQL->getUtilisateur($this->utilisateur_id);
    $erreurs = [];
  }
  
  (new Vue)->generer(
    'vAdminUtilisateurModifier',
    [
      'oUtilConn'   => self::$oUtilConn,
      'titre'       => "Modifier l'utilisateur numéro $this->utilisateur_id",
      'utilisateur' => $utilisateur,
      'erreurs'     => $erreurs
    ],
    'gabarit-admin');
  }
  
  /**
   * Supprimer un utilisateur
   */
  public function supprimerUtilisateur() {
    if (!preg_match('/^\d+$/', $this->utilisateur_id))
      throw new Exception("Numéro d'utilisateur incorrect pour une suppression.");

    $retour = $this->oRequetesSQL->supprimerUtilisateur($this->utilisateur_id);
    if ($retour === false) $this->classRetour = "erreur";
    $this->messageRetourAction = "Suppression de l'utilisateur numéro $this->utilisateur_id ".($retour ? "" : "non ")."effectuée.";
    $this->listerUtilisateurs();
  }

  /**
   * Générer un nouveau mot de passe
   */
  public function genererMdp() {
    if (!preg_match('/^\d+$/', $this->utilisateur_id))
      throw new Exception("Numéro d'utilisateur incorrect pour une modification du mot de passe.");

    $utilisateur = $this->oRequetesSQL->getUtilisateur($this->utilisateur_id);
    $oUtilisateur = new Utilisateur($utilisateur);
    $mdp = $oUtilisateur->genererMdp();
    $retour = $this->oRequetesSQL->modifierUtilisateurMdpGenere([
        'utilisateur_id'  => $this->utilisateur_id, 
        'utilisateur_mdp' => $mdp
    ]);
    if ($retour === true)  {
      $this->messageRetourAction = "Modification du mot de passe de l'utilisateur numéro $this->utilisateur_id effectuée.";
      $retour = (new GestionCourriel)->envoyerMdp($oUtilisateur); 
      $this->messageRetourAction .= $retour
                                    ? " Courriel envoyé à l'utilisateur."
                                    : " Erreur d'envoi d'un courriel à l'utilisateur.";
      if (ENV === "DEV") {
        $this->messageRetourAction .= "<br>Message dans le fichier <a href='$retour' target='_blank'>$retour</a>";
      } 
    } else {  
      $this->classRetour = "erreur";
      $this->messageRetourAction = "Modification du mot de passe de l'utilisateur numéro $this->utilisateur_id non effectuée.";
    }
    $this->listerUtilisateurs();
  }
}