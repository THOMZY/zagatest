// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM chargé - Script de centrage et highlight actif");
    
    // Ajout des tooltips Bootstrap pour les dates
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    if (tooltipTriggerList.length > 0) {
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    }
    
    // Fonction pour forcer le centrage exactement au milieu de l'écran
    function forceScrollToCenter(element) {
        if (!element) return;
        
        console.log("Centrage de l'élément:", element.id);
        
        // Retirer highlight de tous les éléments précédemment surlignés
        document.querySelectorAll('.highlight').forEach(el => {
            if (el !== element) el.classList.remove('highlight');
        });
        
        // Calculer la position exacte qui mettrait l'élément au milieu de l'écran
        const windowHeight = window.innerHeight;
        const elementHeight = element.offsetHeight;
        const elementTop = element.getBoundingClientRect().top + window.pageYOffset;
        
        // Position pour centrer exactement
        const targetY = elementTop - (windowHeight / 2) + (elementHeight / 2);
        
        console.log("Défilement vers:", targetY);
        
        // Forcer le défilement immédiat
        window.scrollTo({
            top: targetY,
            behavior: 'instant' // Utiliser 'auto' si 'instant' ne fonctionne pas
        });
        
        // Appliquer la surbrillance
        element.classList.add('highlight');
        
        // Journaliser pour débogage
        console.log("Highlight ajouté à:", element.id);
    }
    
    // Ajouter des liens vers les messages spécifiques
    const messageLinks = document.querySelectorAll('.message-link');
    console.log("Nombre de liens de messages trouvés:", messageLinks.length);
    
    messageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            console.log("Lien cliqué pour:", targetId);
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Force le centrage
                forceScrollToCenter(targetElement);
                
                // Mettre à jour l'URL sans recharger la page
                const newUrl = window.location.pathname + window.location.search + targetId;
                window.history.pushState({path: newUrl}, '', newUrl);
            } else {
                console.log("Élément cible non trouvé:", targetId);
            }
        });
    });
    
    // Vérifier si un hash existe dans l'URL (pour mettre en évidence un message)
    if (window.location.hash) {
        console.log("Hash détecté dans l'URL:", window.location.hash);
        
        // Attendre un peu plus longtemps pour s'assurer que tout est chargé
        setTimeout(() => {
            const targetElement = document.querySelector(window.location.hash);
            if (targetElement) {
                forceScrollToCenter(targetElement);
            } else {
                console.log("Élément de hash non trouvé:", window.location.hash);
            }
        }, 1000); // Attendre 1 seconde
    }
});