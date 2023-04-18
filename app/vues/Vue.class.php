<?php

class Vue {

  /**
   * Générer et afficher la page html complète associée à la vue
   * -----------------------------------------------------------
   * @param string $vue 
   * @param array $donnees, variables à insérer dans la page
   * @param string $gabarit
   * @param boolean $courriel
   * @return string si $courriel true, void sinon 
   */
  public function generer($vue, $donnees=array(), $gabarit="gabarit-frontend", $courriel = false) {

    require_once 'app/vues/vendor/autoload.php';
    $loader = new \Twig\Loader\FilesystemLoader('app/vues/templates');
    $twig = new \Twig\Environment($loader, [
      // 'cache' => 'app/vues/templates/cache',
    ]);
    $dossierVue =    stristr($gabarit, 'frontend') ? 'frontend'
                  : (stristr($gabarit, 'admin')    ? 'admin'
                  : (stristr($gabarit, 'courriel') ? 'courriels'
                  : (stristr($gabarit, 'erreur')   ? 'erreurs'
                  : '')));
    $donnees['templateMain'] = "$dossierVue/$vue.twig";
    $html = $twig->render("gabarits/$gabarit.twig", $donnees);
    if ($courriel) return $html;
    echo $html;
  }
}