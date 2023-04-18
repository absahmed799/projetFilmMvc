<?php

/**
 * Classe de l'entité Salle
 *
 */
class Salle extends Entite
{
  protected $salle_numero;
  protected $salle_nb_places;

  /**
   * Mutateur de la propriété salle_numero 
   * @param int $salle_numero
   * @return $this
   */    
  public function setSalle_numero($salle_numero) {
    unset($this->erreurs['salle_numero']);
    $regExp = '/^[1-9]\d*$/';
    if (!preg_match($regExp, $salle_numero)) {
      $this->erreurs['salle_numero'] = 'Numéro de salle incorrect.';
    }
    $this->salle_numero = $salle_numero;
    return $this;
  }    

  /**
   * Mutateur de la propriété salle_nb_places
   * @param string $salle_nb_places
   * @return $this
   */    
  public function setSalle_nb_places($salle_nb_places) {
    unset($this->erreurs['salle_nb_places']);
    $regExp = '/^[1-9]\d*$/';
    if (!preg_match($regExp, $salle_nb_places)) {
      $this->erreurs['salle_nb_places'] = 'Doit être un nombre entier positif.';
    }
    $this->salle_nb_places = $salle_nb_places;
    return $this;
  }
}