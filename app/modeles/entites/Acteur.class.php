<?php

/**
 * Classe de l'entité Acteur
 *
 */
class Acteur extends Entite
{
  protected $acteur_id;
  protected $acteur_nom;
  protected $acteur_prenom;

  /**
   * Mutateur de la propriété acteur_id 
   * @param int $acteur_id
   * @return $this
   */    
  public function setActeur_id($acteur_id) {
    unset($this->erreurs['acteur_id']);
    $regExp = '/^[1-9]\d*$/';
    if (!preg_match($regExp, $acteur_id)) {
      $this->erreurs['acteur_id'] = 'Numéro incorrect.';
    }
    $this->acteur_id = $acteur_id;
    return $this;
  }    

  /**
   * Mutateur de la propriété acteur_nom 
   * @param string $acteur_nom
   * @return $this
   */    
  public function setActeur_nom($acteur_nom) {
    unset($this->erreurs['acteur_nom']);
    $acteur_nom = trim($acteur_nom);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $acteur_nom)) {
      $this->erreurs['acteur_nom'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->acteur_nom = $acteur_nom;
    return $this;
  }

  /**
   * Mutateur de la propriété acteur_prenom 
   * @param string $acteur_prenom
   * @return $this
   */    
  public function setActeur_prenom($acteur_prenom) {
    unset($this->erreurs['acteur_prenom']);
    $acteur_prenom = trim($acteur_prenom);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $acteur_prenom)) {
      $this->erreurs['acteur_prenom'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->acteur_prenom = $acteur_prenom;
    return $this;
  }
}