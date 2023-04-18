<?php

/**
 * Classe GestionCourriel
 *
 */
class GestionCourriel {

  /**
   * Envoyer un courriel à l'utilisateur pour lui communiquer
   * son identifiant de connexion et son mot de passe
   * @param object $oUtilisateur utilisateur destinataire
   * @return boolean|string, chaîne = chemin du fichier message html en environnement de développement
   *
   */
  public function envoyerMdp(Utilisateur $oUtilisateur) {
    $destinataire  = $oUtilisateur->utilisateur_courriel; 
    $message       = (new Vue)->generer('cMdp',
                                         array(
                                           'titre'        => 'Information',
                                           'http_host'    => $_SERVER['HTTP_HOST'],
                                           'oUtilisateur' => $oUtilisateur
                                         ),
                                         'gabarit-courriel', true);
    if (ENV === "DEV") {    
      $dateEnvoi = date("Y-m-d H-i-s");
      $fichier   = "mocks/courriels/$dateEnvoi-$destinataire.html";
      $nfile     = fopen($fichier, "w");
      fwrite($nfile, $message);
      fclose($nfile);
      return $fichier;
    } else {
      $headers  = 'MIME-Version: 1.0' . "\n";
      $headers .= 'Content-Type: text/html; charset=utf-8' . "\n";
      $headers .= 'From: Le Méliès <support@lemelies.com>' . "\n";
      return mail($destinataire, "Informations", $message, $headers);
    }
  }
}