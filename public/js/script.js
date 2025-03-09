// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    // Ajout des tooltips Bootstrap pour les dates
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    
    // Ajouter des liens vers les messages spécifiques
    const messageLinks = document.querySelectorAll('.message-link');
    
    messageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Faire défiler jusqu'au message
                targetElement.scrollIntoView({ behavior: 'smooth' });
                
                // Mettre en évidence le message
                targetElement.classList.add('highlight');
                setTimeout(() => {
                    targetElement.classList.remove('highlight');
                }, 2000);
            }
        });
    });
});
