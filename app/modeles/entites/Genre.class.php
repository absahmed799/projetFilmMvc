<?php

/**
 * Classe de l'entité Genre
 *
 */
class Genre extends Entite
{
  protected $genre_id;
  protected $genre_nom;

  /**
   * Mutateur de la propriété genre_id 
   * @param int $genre_id
   * @return $this
   */    
  public function setGenre_id($genre_id) {
    unset($this->erreurs['genre_id']);
    $regExp = '/^[1-9]\d*$/';
    if (!preg_match($regExp, $genre_id)) {
      $this->erreurs['genre_id'] = 'Numéro de genre incorrect.';
    }
    $this->genre_id = $genre_id;
    return $this;
  }    

  /**
   * Mutateur de la propriété genre_nom
   * @param string $genre_nom
   * @return $this
   */    
  public function setGenre_nom($genre_nom) {
    unset($this->erreurs['genre_nom']);
    $genre_nom = trim($genre_nom);
    $regExp = '/^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i';
    if (!preg_match($regExp, $genre_nom)) {
      $this->erreurs['genre_nom'] = 'Au moins 2 caractères alphabétiques pour chaque mot.';
    }
    $this->genre_nom = $genre_nom;
    return $this;
  }
}