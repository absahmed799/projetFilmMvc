<?php

/**
 * Classe des requêtes SQL
 *
 */
class RequetesSQL extends RequetesPDO {

  /* GESTION DES UTILISATEURS 
     ======================== */

  /**
   * Récupération des utilisateurs
   * @return array tableau d'objets Utilisateur
   */ 
  public function getUtilisateurs() {
    $this->sql = "
      SELECT utilisateur_id, utilisateur_nom, utilisateur_prenom, utilisateur_courriel,
             utilisateur_renouveler_mdp, utilisateur_profil
      FROM utilisateur ORDER BY utilisateur_id DESC";
     return $this->getLignes();
  }

  /**
   * Récupération d'un utilisateur
   * @param int $utilisateur_id, clé du utilisateur  
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne
   */ 
  public function getUtilisateur($utilisateur_id) {
    $this->sql = "
      SELECT utilisateur_id, utilisateur_nom, utilisateur_prenom, utilisateur_courriel,
             utilisateur_renouveler_mdp, utilisateur_profil
      FROM utilisateur
      WHERE utilisateur_id = :utilisateur_id";
    return $this->getLignes(['utilisateur_id' => $utilisateur_id], RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Contrôler si adresse courriel non déjà utilisée par un autre utilisateur que utilisateur_id
   * @param array $champs tableau utilisateur_courriel et utilisateur_id (0 si dans toute la table)
   * @return array|false utilisateur avec ce courriel, false si courriel disponible
   */ 
  public function controlerCourriel($champs) {
    $this->sql = 'SELECT utilisateur_id FROM utilisateur
                  WHERE utilisateur_courriel = :utilisateur_courriel AND utilisateur_id != :utilisateur_id';
    return $this->getLignes($champs, RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Connecter un utilisateur
   * @param array $champs, tableau avec les champs utilisateur_courriel et utilisateur_mdp  
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne
   */ 
  public function connecter($champs) {
    $this->sql = "
      SELECT utilisateur_id, utilisateur_nom, utilisateur_prenom,
             utilisateur_courriel, utilisateur_renouveler_mdp, utilisateur_profil
      FROM utilisateur
      WHERE utilisateur_courriel = :utilisateur_courriel AND utilisateur_mdp = SHA2(:utilisateur_mdp, 512)";
    return $this->getLignes($champs, RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Ajouter un utilisateur
   * @param array $champs tableau des champs de l'utilisateur 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function ajouterUtilisateur($champs) {
    $utilisateur = $this->controlerCourriel(
      ['utilisateur_courriel' => $champs['utilisateur_courriel'], 'utilisateur_id' => 0]);
    if ($utilisateur !== false)
      return Utilisateur::ERR_COURRIEL_EXISTANT;
    $this->sql = '
      INSERT INTO utilisateur SET
      utilisateur_nom            = :utilisateur_nom,
      utilisateur_prenom         = :utilisateur_prenom,
      utilisateur_courriel       = :utilisateur_courriel,
      utilisateur_mdp            = SHA2(:utilisateur_mdp, 512),
      utilisateur_renouveler_mdp = "oui",
      utilisateur_profil         = :utilisateur_profil';
    return $this->CUDLigne($champs);
  }

  /**
   * Créer un compte utilisateur dans le frontend
   * @param array $champs tableau des champs de l'utilisateur 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function creerCompteClient($champs) {
    $utilisateur = $this->controlerCourriel(
      ['utilisateur_courriel' => $champs['utilisateur_courriel'], 'utilisateur_id' => 0]);
    if ($utilisateur !== false)
      return ['utilisateur_courriel' => Utilisateur::ERR_COURRIEL_EXISTANT];
    unset($champs['nouveau_mdp_bis']);  
    $this->sql = '
      INSERT INTO utilisateur SET
      utilisateur_nom            = :utilisateur_nom,
      utilisateur_prenom         = :utilisateur_prenom,
      utilisateur_courriel       = :utilisateur_courriel,
      utilisateur_mdp            = SHA2(:nouveau_mdp, 512),
      utilisateur_renouveler_mdp = "non",
      utilisateur_profil         = "'.Utilisateur::PROFIL_CLIENT.'"';
    return $this->CUDLigne($champs);
  }


  /**
   * Modifier un utilisateur
   * @param array $champs tableau des champs de l'utilisateur 
   * @return boolean|string true si modifié, message d'erreur sinon
   */ 
  public function modifierUtilisateur($champs) {
    $utilisateur = $this->controlerCourriel(
      ['utilisateur_courriel' => $champs['utilisateur_courriel'], 'utilisateur_id' => $champs['utilisateur_id']]);
    if ($utilisateur !== false)
      return Utilisateur::ERR_COURRIEL_EXISTANT;
    $this->sql = '
      UPDATE utilisateur SET
      utilisateur_nom      = :utilisateur_nom,
      utilisateur_prenom   = :utilisateur_prenom,
      utilisateur_courriel = :utilisateur_courriel,
      utilisateur_profil   = :utilisateur_profil
      WHERE utilisateur_id = :utilisateur_id
      AND utilisateur_id > 4'; // ne pas modifier les 4 premiers utilisateurs du jeu d'essai
    return $this->CUDLigne($champs);
  }

  /**
   * Modifier le mot de passe d'un utilisateur
   * @param array $champs tableau des champs de l'utilisateur 
   * @return boolean true si modifié, false sinon
   */ 
  public function modifierUtilisateurMdpGenere($champs) {
    $this->sql = '
      UPDATE utilisateur SET
      utilisateur_mdp            = SHA2(:utilisateur_mdp, 512),
      utilisateur_renouveler_mdp = "oui"
      WHERE utilisateur_id = :utilisateur_id
      AND utilisateur_id > 4'; // ne pas modifier les 4 premiers utilisateurs du jeu d'essai
    return $this->CUDLigne($champs);
  }

  /**
   * Modifier le mot de passe saisi d'un utilisateur
   * @param array $champs tableau des champs de l'utilisateur 
   * @return boolean true si modifié, false sinon
   */ 
  public function modifierUtilisateurMdpSaisi($champs) {
    $this->sql = '
      UPDATE utilisateur SET
      utilisateur_mdp            = SHA2(:utilisateur_mdp, 512), 
      utilisateur_renouveler_mdp = "non"
      WHERE utilisateur_id = :utilisateur_id
      AND utilisateur_id > 4'; // ne pas modifier les 4 premiers utilisateurs du jeu d'essai
    return $this->CUDLigne($champs);
  }

  /**
   * Supprimer un utilisateur
   * @param int $utilisateur_id clé primaire
   * @return boolean|string true si suppression effectuée, message d'erreur sinon
   */ 
  public function supprimerUtilisateur($utilisateur_id) {
    $this->sql = '
      DELETE FROM utilisateur WHERE utilisateur_id = :utilisateur_id
      AND utilisateur_id NOT IN (SELECT DISTINCT f_r_film_id FROM film_realisateur)
      AND utilisateur_id NOT IN (SELECT DISTINCT f_a_film_id FROM film_acteur)
      AND utilisateur_id NOT IN (SELECT DISTINCT f_p_film_id FROM film_pays)
      AND utilisateur_id NOT IN (SELECT DISTINCT seance_film_id FROM seance)
      AND utilisateur_id > 4'; // ne pas supprimer les 4 premiers utilisateurs du jeu d'essai
    return $this->CUDLigne(['utilisateur_id' => $utilisateur_id]);
  }

  /* GESTION DES FILMS 
     ================= */

  /**
   * Récupération des films à l'affiche ou prochainement ou pour l'interface admin
   * @param  string $critere
   * @return array tableau des lignes produites par la select   
   */ 
  public function getFilms($critere = 'enSalle') {
    $oAujourdhui = ENV === "DEV" ? new DateTime(MOCK_NOW) : new DateTime();
    $aujourdhui  = $oAujourdhui->format('Y-m-d');
    $dernierJour = $oAujourdhui->modify('+6 day')->format('Y-m-d');
    $this->sql = "
      SELECT film_id, film_titre, film_duree, film_annee_sortie, film_resume,
             film_affiche, film_bande_annonce, film_statut, genre_nom";
       
    // alimenter les colonnes nombres de réalisateurs,... de la page d'administration
    if ($critere === 'admin') {
      $this->sql .= ", nb_realisateurs, nb_acteurs, nb_pays";   
    }
       
    $this->sql .= "
      FROM film
      INNER JOIN genre ON genre_id = film_genre_id";
       
    // alimenter les colonnes nombres de réalisateurs,... de la page d'administration
    if ($critere === 'admin') {
      $this->sql .= "
        LEFT JOIN (SELECT COUNT(*) AS nb_realisateurs, f_r_film_id FROM film_realisateur GROUP BY f_r_film_id) AS FR
          ON f_r_film_id = film_id
        LEFT JOIN (SELECT COUNT(*) AS nb_acteurs, f_a_film_id FROM film_acteur GROUP BY f_a_film_id) AS FA
          ON f_a_film_id = film_id
        LEFT JOIN (SELECT COUNT(*) AS nb_pays, f_p_film_id FROM film_pays GROUP BY f_p_film_id) AS FP
          ON f_p_film_id = film_id
        ORDER BY film_id DESC";
    } else {
      // filtrages pour les pages du frontend
      $this->sql .= " WHERE film_statut = ".Film::STATUT_VISIBLE;

      switch($critere) {
        case 'enSalle':
          $this->sql .= " AND film_id IN (SELECT DISTINCT seance_film_id FROM seance
                                         WHERE seance_date >='$aujourdhui' AND seance_date <= '$dernierJour')";
          break;
        case 'prochainement':
          $this->sql .= " AND film_id NOT IN (SELECT DISTINCT seance_film_id FROM seance
                                             WHERE seance_date <= '$dernierJour')";
          break;
      }
      $this->sql .= " ORDER BY film_annee_sortie DESC, film_titre ASC";
    }      
    return $this->getLignes();
  }

  /**
   * Récupération d'un film
   * @param int    $film_id, clé du film 
   * @param string $mode, admin si pour l'interface d'administration
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne  
   */ 
  public function getFilm($film_id, $mode = null) {
    $this->sql = "
      SELECT film_id, film_titre, film_duree, film_annee_sortie, film_resume,
             film_affiche, film_bande_annonce, film_statut, film_genre_id, genre_nom
      FROM film
      INNER JOIN genre ON genre_id = film_genre_id
      WHERE film_id = :film_id";
    if ($mode !== 'admin')
      $this->sql .=" AND film_statut = ".Film::STATUT_VISIBLE;
 
    return $this->getLignes(['film_id' => $film_id], RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Récupération des réalisateurs d'un film
   * @param int $film_id, clé du film
   * @return array tableau des lignes produites par la select 
   */ 
  public function getRealisateursFilm($film_id) {
    $this->sql = "
      SELECT realisateur_nom, realisateur_prenom
      FROM realisateur
      INNER JOIN film_realisateur ON f_r_realisateur_id = realisateur_id
      WHERE f_r_film_id = :film_id";

    return $this->getLignes(['film_id' => $film_id]);
  }

  /**
   * Récupération des pays d'un film
   * @param int $film_id, clé du film
   * @return array tableau des lignes produites par la select 
   */ 
  public function getPaysFilm($film_id) {
    $this->sql = "
      SELECT pays_nom
      FROM pays
      INNER JOIN film_pays ON f_p_pays_id = pays_id
      WHERE f_p_film_id = :film_id";

    return $this->getLignes(['film_id' => $film_id]);
  }

  /**
   * Récupération des acteurs d'un film
   * @param int $film_id, clé du film
   * @return array tableau des lignes produites par la select 
   */ 
  public function getActeursFilm($film_id) {
    $this->sql = "
      SELECT acteur_nom, acteur_prenom
      FROM acteur
      INNER JOIN film_acteur ON f_a_acteur_id = acteur_id
      WHERE f_a_film_id = :film_id
      ORDER BY f_a_priorite ASC";

    return $this->getLignes(['film_id' => $film_id]);
  }

  /**
   * Récupération des séances d'un film
   * @param int $film_id, clé du film
   * @return array tableau des lignes produites par la select 
   */ 
  public function getSeancesFilm($film_id) {
    $oAujourdhui = ENV === "DEV" ? new DateTime(MOCK_NOW) : new DateTime();
    $aujourdhui  = $oAujourdhui->format('Y-m-d');
    $dernierJour = $oAujourdhui->modify('+6 day')->format('Y-m-d');
    $this->sql = "
      SELECT DATE_FORMAT(seance_date, '%W') AS seance_jour, seance_date, seance_heure
      FROM seance
      INNER JOIN film ON seance_film_id = film_id
      WHERE seance_film_id = :film_id AND seance_date >='$aujourdhui' AND seance_date <= '$dernierJour'
      ORDER BY seance_date, seance_heure";

    return $this->getLignes(['film_id' => $film_id]);
  }

  /**
   * Ajouter un film
   * @param array $champs tableau des champs du film 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function ajouterFilm($champs) {
    try {
      $this->debuterTransaction();
      $this->sql = '
        INSERT INTO film SET
        film_titre         = :film_titre,
        film_duree         = :film_duree,
        film_annee_sortie  = :film_annee_sortie,
        film_resume        = :film_resume,
        film_statut        = :film_statut,
        film_genre_id      = :film_genre_id';
      $film_id = $this->CUDLigne($champs); 
      
      $this->modifierFilmAffiche($film_id);

      $this->validerTransaction();
      return  $film_id;

    } catch(Exception $e) {
      $this->annulerTransaction();
      return $e->getMessage();
    }
  }

  /**
   * Modifier un film
   * @param array $champs tableau avec les champs à modifier et la clé film_id
   * @return boolean true si modification effectuée, false sinon
   */ 
  public function modifierFilm($champs) {
    try  {             
      $this->debuterTransaction();
      $this->sql = 'UPDATE film SET';
      foreach ($champs as $nom => $valeur) if ($nom != 'film_id') $this->sql .= " $nom = :$nom,";
      $this->sql = substr($this->sql, 0, -1);
      $this->sql .= ' WHERE film_id = :film_id';
      $retour = $this->CUDLigne($champs); 

      $retourAffiche = $this->modifierFilmAffiche($champs['film_id']);
      
      $this->validerTransaction();
      return $retour || $retourAffiche;

    } catch(Exception $e) {
      $this->annulerTransaction();
      return $e->getMessage();
    }
  }

  /**
   * Modifier l'affiche d'un film
   * @param int $film_id
   * @return boolean true si téléversement, false sinon
   */ 
  public function modifierFilmAffiche($film_id) {
    if ($_FILES['film_affiche']['tmp_name'] !== "") {
      $this->sql = 'UPDATE film SET film_affiche = :film_affiche WHERE film_id = :film_id';
      $champs['film_id']      = $film_id;
      $champs['film_affiche'] = "medias/affiches/a-$film_id-".time().".jpg";
      $this->CUDLigne($champs);
      foreach (glob("medias/affiches/a-$film_id-*") as $fichier) {
        if (!@unlink($fichier)) 
          throw new Exception("Erreur dans la suppression de l'ancien fichier image de l'affiche.");
      } 
      if (!@move_uploaded_file($_FILES['film_affiche']['tmp_name'], $champs['film_affiche']))
        throw new Exception("Le stockage du fichier image de l'affiche a échoué.");
      return true; 
    }
    return false;
  }

  /**
   * Supprimer un film
   * @param int $film_id clé primaire
   * @return boolean|string true si suppression effectuée, message d'erreur sinon
   */ 
  public function supprimerFilm($film_id) {
    try {
      $this->debuterTransaction();
      $this->sql = '
        DELETE FROM film WHERE film_id = :film_id
        AND film_id NOT IN (SELECT DISTINCT f_r_film_id FROM film_realisateur)
        AND film_id NOT IN (SELECT DISTINCT f_a_film_id FROM film_acteur)
        AND film_id NOT IN (SELECT DISTINCT f_p_film_id FROM film_pays)';
      if (!$this->CUDLigne(['film_id' => $film_id]))
        throw new Exception("");
      foreach (glob("medias/affiches/a-$film_id-*") as $fichier) {
        if (!@unlink($fichier)) 
          throw new Exception("Erreur dans la suppression du fichier image de l'affiche.");
      }
      $this->validerTransaction();
      return true;
    } catch(Exception $e) {
      $this->annulerTransaction();
      return $e->getMessage();
    }
  }  

  /* GESTION DES GENRES 
     ================== */

  /**
   * Récupération des genres pour l'interface admin
   * @return array tableau des lignes produites par la select   
   */ 
  public function getGenres() {
    $this->sql = "
      SELECT genre_id, genre_nom, nb_films
      FROM genre
      LEFT JOIN (SELECT COUNT(*) AS nb_films, film_genre_id FROM film GROUP BY film_genre_id) AS FG
          ON film_genre_id = genre_id
      ORDER BY genre_id DESC";
    return $this->getLignes();
  }

  /**
   * Récupération d'un genre
   * @param int $genre_id, clé du genre  
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne
   */ 
  public function getGenre($genre_id) {
    $this->sql = "
      SELECT genre_id, genre_nom
      FROM genre
      WHERE genre_id = :genre_id";
    return $this->getLignes(['genre_id' => $genre_id], RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Ajouter un genre
   * @param array $champs tableau des champs du genre 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function ajouterGenre($champs) {
    $this->sql = '
      INSERT INTO genre SET
      genre_id  = :genre_id,
      genre_nom = :genre_nom
      ON DUPLICATE KEY UPDATE genre_id = :genre_id';
    return $this->CUDLigne($champs);
  }

  /**
   * Modifier un genre
   * @param array $champs tableau des champs du genre
   * @return boolean|string true si modifié, message d'erreur sinon
   */ 
  public function modifierGenre($champs) {
    $this->sql = '
      UPDATE genre SET
      genre_nom = :genre_nom
      WHERE genre_id = :genre_id';
    return $this->CUDLigne($champs);
  }

  /**
   * Supprimer un genre
   * @param int $genre_id clé primaire
   * @return boolean|string true si suppression effectuée, message d'erreur sinon
   */ 
  public function supprimerGenre($genre_id) {
    $this->sql = '
      DELETE FROM genre WHERE genre_id = :genre_id
      AND genre_id NOT IN (SELECT DISTINCT film_genre_id FROM film)';
    return $this->CUDLigne(['genre_id' => $genre_id]);
  }

  /* GESTION DES REALISATEURS 
     ======================== */

  /**
   * Récupération des réalisateurs pour l'interface admin
   * @return array tableau des lignes produites par la select   
   */ 
  public function getRealisateurs() {
    $this->sql = "
      SELECT realisateur_id, realisateur_nom, realisateur_prenom, nb_films
      FROM realisateur
      LEFT JOIN (SELECT COUNT(*) AS nb_films, f_r_realisateur_id FROM film_realisateur GROUP BY f_r_realisateur_id) AS FR
          ON f_r_realisateur_id = realisateur_id
      ORDER BY realisateur_id DESC";
    return $this->getLignes();
  }

  /**
   * Récupération d'un réalisateur
   * @param int $realisateur_id, clé du réalisateur  
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne
   */ 
  public function getRealisateur($realisateur_id) {
    $this->sql = "
      SELECT realisateur_id, realisateur_nom, realisateur_prenom
      FROM realisateur
      WHERE realisateur_id = :realisateur_id";
    return $this->getLignes(['realisateur_id' => $realisateur_id], RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Ajouter un réalisateur
   * @param array $champs tableau des champs du réalisateur 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function ajouterRealisateur($champs) {
    $this->sql = '
      INSERT INTO realisateur SET
      realisateur_nom    = :realisateur_nom,
      realisateur_prenom = :realisateur_prenom';
    return $this->CUDLigne($champs);
  }

  /**
   * Modifier un réalisateur
   * @param array $champs tableau des champs du réalisateur
   * @return boolean|string true si modifié, message d'erreur sinon
   */ 
  public function modifierRealisateur($champs) {
    $this->sql = '
      UPDATE realisateur SET
      realisateur_nom    = :realisateur_nom,
      realisateur_prenom = :realisateur_prenom
      WHERE realisateur_id = :realisateur_id';
    return $this->CUDLigne($champs);
  }

  /**
   * Supprimer un réalisateur
   * @param int $realisateur_id clé primaire
   * @return boolean|string true si suppression effectuée, message d'erreur sinon
   */ 
  public function supprimerRealisateur($realisateur_id) {
    $this->sql = '
      DELETE FROM realisateur WHERE realisateur_id = :realisateur_id
      AND realisateur_id NOT IN (SELECT DISTINCT f_r_realisateur_id FROM film_realisateur)';
    return $this->CUDLigne(['realisateur_id' => $realisateur_id]);
  }

  /* GESTION DES ACTEURS 
     =================== */

  /**
   * Récupération des acteurs pour l'interface admin
   * @return array tableau des lignes produites par la select   
   */ 
  public function getActeurs() {
    $this->sql = "
      SELECT acteur_id, acteur_nom, acteur_prenom, nb_films
      FROM acteur
      LEFT JOIN (SELECT COUNT(*) AS nb_films, f_a_acteur_id FROM film_acteur GROUP BY f_a_acteur_id) AS FA
          ON f_a_acteur_id = acteur_id
      ORDER BY acteur_id DESC";
    return $this->getLignes();
  }

  /**
   * Récupération d'un acteur
   * @param int $acteur_id, clé du acteur  
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne
   */ 
  public function getActeur($acteur_id) {
    $this->sql = "
      SELECT acteur_id, acteur_nom, acteur_prenom
      FROM acteur
      WHERE acteur_id = :acteur_id";
    return $this->getLignes(['acteur_id' => $acteur_id], RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Ajouter un acteur
   * @param array $champs tableau des champs du acteur 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function ajouterActeur($champs) {
    $this->sql = '
      INSERT INTO acteur SET
      acteur_nom    = :acteur_nom,
      acteur_prenom = :acteur_prenom';
    return $this->CUDLigne($champs);
  }

  /**
   * Modifier un acteur
   * @param array $champs tableau des champs du acteur
   * @return boolean|string true si modifié, message d'erreur sinon
   */ 
  public function modifierActeur($champs) {
    $this->sql = '
      UPDATE acteur SET
      acteur_nom    = :acteur_nom,
      acteur_prenom = :acteur_prenom
      WHERE acteur_id = :acteur_id';
    return $this->CUDLigne($champs);
  }

  /**
   * Supprimer un acteur
   * @param int $acteur_id clé primaire
   * @return boolean|string true si suppression effectuée, message d'erreur sinon
   */ 
  public function supprimerActeur($acteur_id) {
    $this->sql = '
      DELETE FROM acteur WHERE acteur_id = :acteur_id
      AND acteur_id NOT IN (SELECT DISTINCT f_a_acteur_id FROM film_acteur)';
    return $this->CUDLigne(['acteur_id' => $acteur_id]);
  }

  /* GESTION DES PAYS 
     ================ */

  /**
   * Récupération des pays pour l'interface admin
   * @return array tableau des lignes produites par la select   
   */ 
  public function getPays() {
    $this->sql = "
      SELECT pays_id, pays_nom, nb_films
      FROM pays
      LEFT JOIN (SELECT COUNT(*) AS nb_films, f_p_pays_id FROM film_pays GROUP BY f_p_pays_id) AS FP
          ON f_p_pays_id = pays_id
      ORDER BY pays_id DESC";
    return $this->getLignes();
  }

  /**
   * Récupération d'un pays
   * @param int $pays_id, clé du pays  
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne
   */ 
  public function getUnPays($pays_id) {
    $this->sql = '
      SELECT pays_id, pays_nom
      FROM pays
      WHERE pays_id = :pays_id';
    return $this->getLignes(['pays_id' => $pays_id], RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Ajouter un pays
   * @param array $champs tableau des champs du pays 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function ajouterPays($champs) {
    $this->sql = '
      INSERT INTO pays SET
      pays_nom = :pays_nom';
    return $this->CUDLigne($champs);
  }

  /**
   * Modifier un pays
   * @param array $champs tableau des champs du pays
   * @return boolean|string true si modifié, message d'erreur sinon
   */ 
  public function modifierPays($champs) {
    $this->sql = '
      UPDATE pays SET
      pays_nom = :pays_nom
      WHERE pays_id = :pays_id';
    return $this->CUDLigne($champs);
  }

  /**
   * Supprimer un pays
   * @param int $pays_id clé primaire
   * @return boolean|string true si suppression effectuée, message d'erreur sinon
   */ 
  public function supprimerPays($pays_id) {
    $this->sql = '
      DELETE FROM pays WHERE pays_id = :pays_id
      AND pays_id NOT IN (SELECT DISTINCT f_p_pays_id FROM film_pays)';
    return $this->CUDLigne(['pays_id' => $pays_id]);
  }

  /* GESTION DES SÉANCES
     =================== */

  /**
   * Récupération des séances pour l'interface admin
   * @return array tableau des lignes produites par la select   
   */ 
  public function getSeances() {
    $this->sql = "
      SELECT seance_film_id, seance_salle_numero, seance_date, seance_heure, film_titre
      FROM seance
      INNER JOIN film ON seance_film_id = film_id
      ORDER BY seance_date DESC, seance_heure DESC";
    return $this->getLignes();
  }

  /* GESTION DES SALLES
     ================== */

  /**
   * Récupération des salles pour l'interface admin
   * @return array tableau des lignes produites par la select   
   */ 
  public function getSalles() {
    $this->sql = "
      SELECT salle_numero, salle_nb_places, nb_seances
      FROM salle
      LEFT JOIN (SELECT COUNT(*) AS nb_seances, seance_salle_numero FROM seance GROUP BY seance_salle_numero) AS SS
          ON seance_salle_numero = salle_numero
      ORDER BY salle_numero DESC";
    return $this->getLignes();
  }

  /**
   * Récupération d'une salle
   * @param int $salle_numero, clé de la salle  
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne
   */ 
  public function getSalle($salle_numero) {
    $this->sql = "
      SELECT salle_numero, salle_nb_places
      FROM salle
      WHERE salle_numero = :salle_numero";
    return $this->getLignes(['salle_numero' => $salle_numero], RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Ajouter une salle
   * @param array $champs tableau des champs de la salle 
   * @return int|string clé primaire de la ligne ajoutée, message d'erreur sinon
   */ 
  public function ajouterSalle($champs) {
    $this->sql = '
      INSERT INTO salle SET
      salle_numero    = :salle_numero,
      salle_nb_places = :salle_nb_places
      ON DUPLICATE KEY UPDATE salle_numero = :salle_numero';
    return $this->CUDLigne($champs);
  }

  /**
   * Modifier un salle
   * @param array $champs tableau des champs de la salle
   * @return boolean|string true si modifié, message d'erreur sinon
   */ 
  public function modifierSalle($champs) {
    $this->sql = '
      UPDATE salle SET
      salle_nb_places = :salle_nb_places
      WHERE salle_numero = :salle_numero';
    return $this->CUDLigne($champs);
  }

  /**
   * Supprimer un salle
   * @param int $salle_numero clé primaire
   * @return boolean|string true si suppression effectuée, message d'erreur sinon
   */ 
  public function supprimerSalle($salle_numero) {
    $this->sql = '
      DELETE FROM salle WHERE salle_numero = :salle_numero
      AND salle_numero NOT IN (SELECT DISTINCT seance_salle_numero FROM seance)';
    return $this->CUDLigne(['salle_numero' => $salle_numero]);
  }
}