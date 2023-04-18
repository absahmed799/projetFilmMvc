// Initialisations gestion affichage de l'en-tête/menu

let eEntete = document.querySelector("header");
let ePuce = document.getElementById("puce");
if (sessionStorage.enteteInvisible == "oui") {
  eEntete.classList.add("invisible");
  ePuce.  classList.add("invisible");
}
ePuce.addEventListener("click", afficherEntete);

/**
 *  Affichage/retrait de l'en-tête/menu latéral à gauche 
 */
function afficherEntete() {
  eEntete.style.transition = "width 0.5s, padding 0.5s";
  ePuce.style.transition   = "left 0.5s, transform 0.5s";
  if (sessionStorage.enteteInvisible == "oui") {
    eEntete.classList.remove("invisible");
    ePuce.  classList.remove("invisible");
    sessionStorage.enteteInvisible = "non";
  } else {
    eEntete.classList.add("invisible");
    ePuce.  classList.add("invisible");
    sessionStorage.enteteInvisible = "oui";   
  }
}