<?php

/**
 * Classe de l'entité Realisateur
 *
 */
class Realisateur extends Entite
{
  protected $realisateur_id;
  protected $realisateur_nom;
  protected $realisateur_prenom;

  /**
   * Mutateur de la propriété realisateur_id 
   * @param int $realisateur_id
   * @return $this
   */    
  public function setRealisateur_id($realisateur_id) {
    unset($this->erreurs['realisateur_id']);
    $regExp = '/^[1-9]\d*$/';
    if (!preg_match($regExp, $realisateur_id)) {
      $this->erreurs['realisateur_id'] = 'Numéro incorrect.';
    }
    $this->realisateur_id = $realisateur_id;
    return $this;
  }    

  /**
   * Mutateur de la propriété realisateur_nom 
   * @param string $realisateur_nom
   * @return $this
   */    
  public function setRealisateur_nom($realisateur_nom) {
    unset($this->erreurs['realisateur_nom']);
    $realisateur_nom = trim($realisateur_nom);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $realisateur_nom)) {
      $this->erreurs['realisateur_nom'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->realisateur_nom = $realisateur_nom;
    return $this;
  }

  /**
   * Mutateur de la propriété realisateur_prenom 
   * @param string $realisateur_prenom
   * @return $this
   */    
  public function setRealisateur_prenom($realisateur_prenom) {
    unset($this->erreurs['realisateur_prenom']);
    $realisateur_prenom = trim($realisateur_prenom);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $realisateur_prenom)) {
      $this->erreurs['realisateur_prenom'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->realisateur_prenom = $realisateur_prenom;
    return $this;
  }
}