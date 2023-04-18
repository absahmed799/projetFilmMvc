let eUtilisateur       = document.getElementById('utilisateur');
let eConnecter         = document.getElementById('connecter');
let eCreerCompte       = document.getElementById('creerCompte'); 
let eDeconnecter       = document.getElementById('deconnecter'); 
let eModaleCreerCompte = document.getElementById('modaleCreerCompte');

let eMessageErreurCreerCompte = document.getElementById('messageErreurCreerCompte');

let listInput       = document.querySelectorAll('#frmCreerCompte [name]');
let listSpanErreurs = document.querySelectorAll('#frmCreerCompte [id^="err_"]');

let controlesChamps = {
  utilisateur_nom : {
    requis    : true,
    regExp    : /^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i,
    msgRegExp : "Au moins 2 caractères alphabétiques pour chaque mot."
  },
  utilisateur_prenom : {
    requis    : true,
    regExp    : /^[a-zÀ-ÖØ-öø-ÿ]{2,}( [a-zÀ-ÖØ-öø-ÿ]{2,})*$/i,
    msgRegExp : "Au moins 2 caractères alphabétiques pour chaque mot."
  },
  utilisateur_courriel : {
    requis    : true,
    regExp    : /^[a-z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-z0-9]{1,61}(\.[a-z0-9]{1,61})+$/i, // https://html.spec.whatwg.org/multipage/input.html#email-state-(type=email)
    msgRegExp : "Format incorrect."    
  },
  nouveau_mdp : {
    requis    : true,
    regExp    : /^(?=.*[%!:&].*)(?=.*\d.*)(?=.*[A-Z].*)(?=.*[a-z].*)\S{10,}$/,
    msgRegExp : "Minimum 10 caractères, avec au moins un caractère spécial non alphanumérique (comme par exemple: %!:&), une lettre majuscule, une lettre minuscule et un chiffre."
  },
  nouveau_mdp_bis : {
    requis    : true,
    identique : "nouveau_mdp"
  }	
};
let erreur = false; // true si au moins un champ erroné

eCreerCompte.onclick    = afficherFenetreModale;
frmCreerCompte.onchange = controlerChamp;
frmCreerCompte.onsubmit = traiterFormulaire;

/**
 * Affichage de la fenêtre modale
 */
function afficherFenetreModale() {
  listInput.forEach( e => e.value = "");
  listSpanErreurs.forEach(e => e.innerText ="");
  eModaleCreerCompte.showModal();
}

/**
 * Traitement du formulaire dans la fenêtre modale suite à l'événement submit
 * @param {Event} event
 */
function traiterFormulaire(event) {
  event.preventDefault();

  // contrôle de tous les champs
  // ===========================
  erreur = false;
  for (let nomChamp in controlesChamps) {
    let controles = controlesChamps[nomChamp];
    valider(nomChamp, controles.requis, controles.regExp, controles.msgRegExp, controles.identique);
  }
  if (erreur) return;
  
  // envoi des données au serveur si aucune erreur
  // =============================================
  let fd = new FormData(frmCreerCompte);
  fetch('creerCompte', {method: 'post', body: fd})
  .then (reponse => {
    if (!reponse.ok) {
      throw { message:  "Problème technique sur le serveur" };
    }
    return reponse.json();
  })
  .then (retour => {
    if (!/^[1-9]\d*$/.test(retour)) {
      if (retour) {
        for (let champ in retour) {
          document.getElementById("err_"+champ).innerText = retour[champ];
        }
      } else {
        throw { message : "Création de compte non effectuée." };
      }
    } else {
      eUtilisateur.innerHTML = frmCreerCompte.utilisateur_prenom.value + " " + frmCreerCompte.utilisateur_nom.value;
      eConnecter  .classList.add('cache');
      eCreerCompte.classList.add('cache');
      eDeconnecter.classList.remove('cache'); 
      eModaleCreerCompte.close();
    }
  })
  .catch((e) => {
    eMessageErreurCreerCompte.innerHTML = "Erreur: " + e.message;
  });
}

/**
 * Contrôle d'un champ de saisie suite à l'événement change
 * @param {Event} event 
 */
function controlerChamp(event) {
  let nomChamp = event.target.name;
  let controles = controlesChamps[nomChamp];
  valider(nomChamp, controles.requis, controles.regExp, controles.msgRegExp, controles.identique);
}

/**
 * Validation d'un champ input de type text ou password 
 * @param {string}  nomChamp
 * @param {boolean} requis
 * @param {RegExp}  regExp
 * @param {string}  msgRegExp
 * @param {string}  identique
 */
function valider(nomChamp, requis = null, regExp = null, msgRegExp = null, identique = null) {
  let val = "";
  let e = frmCreerCompte[nomChamp];
  e.value = e.value.trim();
  val = e.value;

  let msgErr = "";
  if (val === "" && requis)                                                           msgErr = "Obligatoire";
  if (msgErr === "" && regExp !== null && !regExp.test(val))                          msgErr = msgRegExp;
  if (msgErr === "" && identique !== null && val !== frmCreerCompte[identique].value) msgErr = "Doit être identique au champ précédent";

  let idSpan = "err_" + nomChamp;
  document.getElementById(idSpan).innerHTML = msgErr;

  if (msgErr !== "") erreur = true;
}