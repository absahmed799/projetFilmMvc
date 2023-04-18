<?php

/**
 * Classe de l'entité Pays
 *
 */
class Pays extends Entite
{
  protected $pays_id;
  protected $pays_nom;

  /**
   * Mutateur de la propriété pays_id 
   * @param int $pays_id
   * @return $this
   */    
  public function setPays_id($pays_id) {
    unset($this->erreurs['pays_id']);
    $regExp = '/^[1-9]\d*$/';
    if (!preg_match($regExp, $pays_id)) {
      $this->erreurs['pays_id'] = 'Numéro de pays incorrect.';
    }
    $this->pays_id = $pays_id;
    return $this;
  }    

  /**
   * Mutateur de la propriété pays_nom
   * @param string $pays_nom
   * @return $this
   */    
  public function setPays_nom($pays_nom) {
    unset($this->erreurs['pays_nom']);
    $pays_nom = trim($pays_nom);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $pays_nom)) {
      $this->erreurs['pays_nom'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->pays_nom = $pays_nom;
    return $this;
  }
}