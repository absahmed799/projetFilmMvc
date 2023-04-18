let filtrer = function () {

  let eFiltrer, eTitres, eTitresImages, eLignes;

  let sensTri = "a";
  
  let oFiltrer = {
  
    /**
     * Initialisations pour le tri et le filtrage:
     * ajout des listeners sur les flêches des cellules titres
     * rechargement de la chaîne de filtrage en session storage si elle existe
     * ajout d'un listener sur la balise input de filtrage
     */
    initialiser () {
      eFiltrer      = document.getElementById("filtrer");
      eTitres       = document.querySelectorAll(".t");
      eTitresImages = document.querySelectorAll(".t img");
      eLignes       = document.querySelectorAll(".l");
      
      eTitres.forEach((e, i) => {
        e.setAttribute("data-c", i);
        if (e.firstElementChild) e.firstElementChild.addEventListener("click", oFiltrer.trier);
        if (e.lastElementChild)  e.lastElementChild .addEventListener("click", oFiltrer.trier);
      });
 
      if (eFiltrer.dataset.filtre) {
        let valeurFiltre = sessionStorage[eFiltrer.dataset.filtre];
        if (valeurFiltre) {
          eFiltrer.value = valeurFiltre;
          oFiltrer.selectionner();
        }
      }
      eFiltrer.addEventListener("change", oFiltrer.selectionner); 
    },

    /**
     * Filtrage des lignes du tableau
     * 'display contents' si la ligne contient la chaîne recherchée
     * 'display none' sinon
     * mémorisation de la chaîne de filtrage en session storage
     */
    selectionner () {
      
      let chaine = eFiltrer.value.toLowerCase();
      eLignes.forEach((e) => {
        let test = e.innerText.split("\n");
        e.style.display = (e.innerText.toLowerCase().search(chaine) >= 0) ? "contents" : "none"; 
      });
      if (eFiltrer.dataset.filtre) sessionStorage[eFiltrer.dataset.filtre] = chaine; 
    },

    /**
     * Tri sur le critère de la colonne associée à la flèche cliquée
     * @param {Event} evt 
     */
    trier (evt) {
      let t = [];
      let c = parseInt(evt.target.parentNode.dataset.c) + 1;
      eLignes.forEach((e, i) => {
        t.push({indice: i, val: e.querySelector('div:nth-of-type('+c+')').innerText});
        e.parentNode.removeChild(e);
      });
      eTitresImages.forEach((e) => {                                  // réinitialisation des flèches à 'non actives' (suppression suffixe -a)
        e.src = e.src.replace("-a\.", "\.");
      });
      sensTri = evt.target.src.match(/i-(.)/)[1];                     // lettre a ou d du sens du tri extraite du nom du fichier flèche
      let ret = sensTri === "a" ? 1 : -1;
      evt.target.src = evt.target.src.replace(/(-a)?\.svg/,"-a.svg"); // ajout suffixe -a (flèche active) 
     
      t.sort(function(g, d) {
        let gv = g.val;
        let dv = d.val;
        if (!isNaN(gv)) {
          gv = parseInt(gv);
          dv = parseInt(dv);
          if (gv > dv) return ret;
          if (gv < dv) return -ret;
        } else {
          if (gv.localeCompare(dv) == 1)  return ret;
          if (gv.localeCompare(dv) == -1) return -ret;
        }
        return 0;
      });
      t.forEach((item) => {
        let i = item.indice;
        evt.target.parentNode.parentNode.appendChild(eLignes[i]);
      });
    }

  }
  
  return oFiltrer;
} ();

filtrer.initialiser();