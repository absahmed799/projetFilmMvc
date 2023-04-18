let eUtilisateur       = document.getElementById('utilisateur');
let eConnecter         = document.getElementById('connecter');
let eCreerCompte       = document.getElementById('creerCompte'); 
let eDeconnecter       = document.getElementById('deconnecter'); 
let eModaleConnexion   = document.getElementById('modaleConnexion');

let eMessageErreurConnexion = document.getElementById('messageErreurConnexion');

eConnecter.onclick    = afficherFenetreModale;
frmConnexion.onsubmit = traiterFormulaire;
eDeconnecter.onclick  = deconnecter;

/**
 * Affichage de la fenêtre modale au clic sur le lien Connecter
 */
function afficherFenetreModale() {
  eMessageErreurConnexion.innerHTML = "&nbsp;";
  document.getElementById('modaleConnexion').showModal();
}

/**
 * Traitement du formulaire dans la fenêtre modale suite à l'événement submit
 * @param {Event} event
 */
function traiterFormulaire(event) {
  event.preventDefault();
  let fd = new FormData(frmConnexion);
  fetch('connecter', {method: 'post', body: fd})
  .then (reponse => {
    if (!reponse.ok) {
      throw { message:  "Problème technique sur le serveur" };
    }
    return reponse.json();
  })
  .then (utilisateur => {
    if (!utilisateur) {
      eMessageErreurConnexion.innerHTML = "Courriel ou mot de passe incorrect.";
    } else {
      eUtilisateur.innerHTML = utilisateur.utilisateur_prenom + " " + utilisateur.utilisateur_nom;
      eConnecter  .classList.add('cache');
      eCreerCompte.classList.add('cache');
      eDeconnecter.classList.remove('cache'); 
      eModaleConnexion.close();
    }
  })
  .catch((e) => {
    eMessageErreurConnexion.innerHTML = "Erreur: " + e.message;
  });
}

/**
 * Déconnecter au clic sur le lien Déconnecter
 */
function deconnecter() {
  fetch('deconnecter')
  .then (reponse => {
    if (!reponse.ok) {
      throw { message:  "Problème technique sur le serveur" };
    }
    return reponse.json();
  })
  .then (codeRetour => {
    if (codeRetour) {
      eUtilisateur.innerHTML = "";
      eDeconnecter.classList.add('cache');
      eConnecter  .classList.remove('cache');
      eCreerCompte.classList.remove('cache') 
    }
  })
  .catch((e) => {
    eMessageErreurConnexion.innerHTML = "Erreur: " + e.message;
  });
}