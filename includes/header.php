<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="public/img/favicon.ico" type="image/x-icon">
    <title><?php echo isset($page_title) ? convert_smileys(secure_output($page_title)) . ' - ' : ''; ?>Archives du Forum</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome pour l'icône smiley -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- CSS personnalisé -->
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="text-white mb-4">
        <div class="container py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h1 class="h3 mb-2 mb-md-0"><a href="index.php" class="text-white text-decoration-none">Archives du Forum</a></h1>
                
                <!-- Barre d'outils avec bouton smiley et recherche -->
                <div class="d-flex align-items-center">
                    <!-- Bouton smiley -->
                    <a href="smiley.php" class="btn btn-outline-light btn-sm me-2" title="Liste des smileys">
                        <i class="far fa-smile"></i>
                    </a>
                    
                    <!-- Bouton trombi -->
                    <a href="trombi.php" class="btn btn-outline-light btn-sm me-2" title="Trombinoscope">
                        <i class="fas fa-users"></i>
                    </a>
                    
                    <!-- Bouton paramètres -->
                    <button type="button" class="btn btn-outline-light btn-sm me-2" title="Paramètres" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="fas fa-cog"></i>
                    </button>
                    
                    <!-- Formulaire de recherche -->
                    <form action="search.php" method="GET" class="d-flex">
                        <input type="text" name="term" class="form-control form-control-sm me-2" placeholder="Rechercher..." aria-label="Rechercher">
                        <button type="submit" class="btn btn-outline-light btn-sm">Rechercher</button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container mb-4">
        <?php if (isset($page_title)): ?>
            <h2 class="mb-3"><?php echo convert_smileys(secure_output($page_title)); ?></h2>
        <?php endif; ?>
        
        <?php if (isset($breadcrumb)): ?>
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                    <?php foreach ($breadcrumb as $url => $label): ?>
                        <?php if ($url): ?>
                            <li class="breadcrumb-item"><a href="<?php echo $url; ?>"><?php echo convert_smileys(secure_output($label)); ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo convert_smileys(secure_output($label)); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
    </main>
    
    <!-- Modal des paramètres -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="settingsModalLabel">Paramètres</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group list-group-flush bg-dark" id="settingsList">
                        <div class="list-group-item bg-dark text-white border-secondary">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Signatures des utilisateurs</h6>
                                    <small class="text-muted">Afficher les signatures en bas des messages</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="showSignatures" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script pour gérer les paramètres -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Récupérer les paramètres sauvegardés
        const savedSettings = JSON.parse(localStorage.getItem('forumSettings') || '{"showSignatures": true}');
        
        // Éléments du DOM
        const showSignaturesToggle = document.getElementById('showSignatures');
        
        // Fonction pour sauvegarder les paramètres
        function saveSettings(settings) {
            localStorage.setItem('forumSettings', JSON.stringify(settings));
            // Émettre un événement personnalisé pour notifier les changements
            window.dispatchEvent(new CustomEvent('settingsChanged', { detail: settings }));
        }
        
        // Fonction pour mettre à jour l'interface en fonction des paramètres
        function updateUI(settings) {
            showSignaturesToggle.checked = settings.showSignatures !== false;
        }
        
        // Écouteur d'événement pour le toggle des signatures
        showSignaturesToggle.addEventListener('change', function() {
            const settings = {
                ...savedSettings,
                showSignatures: this.checked
            };
            saveSettings(settings);
        });
        
        // Initialiser l'interface avec les paramètres sauvegardés
        updateUI(savedSettings);
    });
    </script>
</body>
</html>