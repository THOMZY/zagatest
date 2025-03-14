<?php
// Inclure le gestionnaire de session (le placer au début du header)
require_once 'includes/session_manager.php';
?>
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
    
    <!-- jQuery first, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <header class="text-white mb-4">
        <div class="container py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h1 class="h3 mb-2 mb-md-0"><a href="index.php" class="text-white text-decoration-none">Archives du Forum</a></h1>
                
                <!-- Barre d'outils avec bouton smiley, recherche et connexion -->
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
                    <form action="search.php" method="GET" class="d-flex me-2">
                        <input type="text" name="term" class="form-control form-control-sm me-2" placeholder="Rechercher..." aria-label="Rechercher">
                        <button type="submit" class="btn btn-outline-light btn-sm">Rechercher</button>
                    </form>
                    
                    <!-- Bouton connexion/déconnexion -->
                    <?php if (is_logged_in()): ?>
                        <div class="dropdown">
                            <a href="#" class="btn btn-primary btn-sm dropdown-toggle d-flex align-items-center" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i>
                                <span><?php echo secure_output(get_logged_username()); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="dropdownMenuLink">
                                <li><a class="dropdown-item" href="profile-view.php?username=<?php echo urlencode(get_logged_username()); ?>">Mon profil</a></li>
                                <li><a class="dropdown-item" href="messages.php">Mes messages privés</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-sign-in-alt me-1"></i> Connexion
                        </a>
                    <?php endif; ?>
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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">Paramètres d'affichage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showSignaturesSwitch" checked>
                        <label class="form-check-label" for="showSignaturesSwitch">Afficher les signatures</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Récupérer les paramètres sauvegardés
        const settings = JSON.parse(localStorage.getItem('forumSettings') || '{"showSignatures": true}');
        const showSignaturesSwitch = document.getElementById('showSignaturesSwitch');
        
        // Initialiser l'état du switch
        showSignaturesSwitch.checked = settings.showSignatures !== false;
        
        // Gérer le changement de paramètre
        showSignaturesSwitch.addEventListener('change', function() {
            // Mettre à jour les paramètres
            settings.showSignatures = this.checked;
            localStorage.setItem('forumSettings', JSON.stringify(settings));
            
            // Émettre un événement pour notifier le changement
            window.dispatchEvent(new CustomEvent('settingsChanged', {
                detail: settings
            }));
        });
    });
    </script>
    
    </body>
</html>