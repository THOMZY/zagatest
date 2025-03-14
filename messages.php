<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session_manager.php';

// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Variables pour les résultats de recherche admin
$searched_user = '';
$user_found = false;
$admin_user_id = 0;
$user_conversations = [];

// Traitement du formulaire de recherche admin
if (get_user_access_level() == 100 && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_user'])) {
    $searched_user = trim($_POST['username']);
    
    // Rechercher l'utilisateur par pseudo
    try {
        $user_stmt = $db->prepare("
            SELECT id, pseudo, acces 
            FROM rf_membres 
            WHERE pseudo = :username
        ");
        $user_stmt->bindParam(':username', $searched_user, PDO::PARAM_STR);
        $user_stmt->execute();
        
        if ($user_stmt->rowCount() > 0) {
            $user = $user_stmt->fetch();
            $user_found = true;
            $admin_user_id = $user->id;
            
            // Récupérer les conversations de l'utilisateur
            $conv_stmt = $db->prepare("
                SELECT 
                    mp.id, 
                    mp.titre, 
                    mp.nbMess,
                    mp.lastPost,
                    m.time AS last_time,
                    mb.lu,
                    mb.epingle,
                    u.pseudo AS auteur_pseudo
                FROM 
                    rf_mp_mbr mb
                JOIN 
                    rf_mp mp ON mb.mpid = mp.id
                JOIN 
                    rf_membres u ON mp.auteur = u.id
                LEFT JOIN
                    rf_mp_mess m ON mp.lastPost = m.id
                WHERE 
                    mb.idPseudo = :user_id
                ORDER BY 
                    mb.epingle DESC, m.time DESC
            ");
            $conv_stmt->bindParam(':user_id', $admin_user_id, PDO::PARAM_INT);
            $conv_stmt->execute();
            
            $user_conversations = $conv_stmt->fetchAll();
        }
    } catch (PDOException $e) {
        die("Erreur lors de la recherche d'utilisateur: " . $e->getMessage());
    }
}

// Définir le titre de la page
$page_title = "Mes messages privés";

// Récupérer la liste des conversations (MP) de l'utilisateur
try {
    $stmt = $db->prepare("
        SELECT 
            mp.id, 
            mp.titre, 
            mp.nbMess,
            mp.lastPost,
            m.time AS last_time,
            mb.lu,
            mb.epingle,
            u.pseudo AS auteur_pseudo
        FROM 
            rf_mp_mbr mb
        JOIN 
            rf_mp mp ON mb.mpid = mp.id
        JOIN 
            rf_membres u ON mp.auteur = u.id
        LEFT JOIN
            rf_mp_mess m ON mp.lastPost = m.id
        WHERE 
            mb.idPseudo = :user_id
        ORDER BY 
            mb.epingle DESC, m.time DESC
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $conversations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Erreur lors de la récupération des messages privés: " . $e->getMessage());
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<?php if (get_user_access_level() == 100): ?>
<!-- Section de recherche administrateur -->
<div class="card mb-4">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h3 class="card-title h5 mb-0"><i class="fas fa-shield-alt me-2"></i>Recherche MP (Mode Admin)</h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="row g-3 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Rechercher les MP d'un utilisateur" value="<?php echo htmlspecialchars($searched_user); ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="search_user" class="btn btn-danger w-100">Rechercher</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($user_found): ?>
    <!-- Résultats de la recherche admin -->
    <div class="card mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title h5 mb-0">Messages privés de <?php echo secure_output($searched_user); ?></h3>
            <span class="badge bg-light text-dark"><?php echo count($user_conversations); ?> conversation(s)</span>
        </div>
        
        <?php if (!empty($user_conversations)): ?>
            <div class="list-group list-group-flush">
                <!-- En-tête des colonnes -->
                <div class="list-group-item py-2 bg-dark">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <div style="width: 60px;" class="d-flex"></div>
                        <div style="width: 300px;" class="text-white fw-bold">Titre</div>
                        <div style="width: 200px;" class="text-white fw-bold text-center">Auteur</div>
                        <div style="width: 100px;" class="text-center text-white fw-bold">Messages</div>
                        <div class="text-white fw-bold ms-auto">Dernier message</div>
                    </div>
                </div>
                <?php foreach ($user_conversations as $conversation): ?>
                    <div class="list-group-item py-2">
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <!-- Icônes de statut (largeur fixe) -->
                            <div style="width: 60px;" class="d-flex">
                                <?php if ($conversation->epingle == '1'): ?>
                                    <i class="fas fa-thumbtack text-warning" title="Message épinglé"></i>
                                <?php endif; ?>
                                <?php if ($conversation->lu == '0'): ?>
                                    <span class="badge bg-primary ms-1">Nouveau</span>
                                <?php endif; ?>
                            </div>

                            <!-- Titre (largeur fixe) -->
                            <div style="width: 300px;" class="text-truncate">
                                <a href="message_view.php?id=<?php echo $conversation->id; ?>&user_id=<?php echo $admin_user_id; ?>" 
                                   class="text-decoration-none text-white">
                                    <?php echo secure_output($conversation->titre); ?>
                                </a>
                            </div>

                            <!-- Interlocuteur (largeur fixe) -->
                            <div style="width: 200px;" class="text-truncate text-center">
                                <a href="profile-view.php?username=<?php echo urlencode($conversation->auteur_pseudo); ?>" 
                                   class="text-decoration-none text-info">
                                    <?php echo secure_output($conversation->auteur_pseudo); ?>
                                </a>
                            </div>

                            <!-- Nombre de messages (largeur fixe) -->
                            <div style="width: 100px;" class="text-center">
                                <span class="badge bg-secondary">
                                    <?php echo $conversation->nbMess; ?> message(s)
                                </span>
                            </div>

                            <!-- Date (le reste de l'espace) -->
                            <div class="text-muted ms-auto">
                                <?php echo format_date($conversation->last_time); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card-body">
                <p class="text-muted mb-0">Cet utilisateur n'a aucun message privé.</p>
            </div>
        <?php endif; ?>
    </div>
<?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_user'])): ?>
    <!-- Message si l'utilisateur n'est pas trouvé -->
    <div class="alert alert-warning">
        <p class="mb-0">Aucun utilisateur trouvé avec le nom '<?php echo secure_output($searched_user); ?>'.</p>
    </div>
<?php endif; ?>
<?php endif; ?>

<?php if (!$user_found): ?>
<!-- Section des messages privés personnels -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title h5 mb-0">Mes messages privés</h3>
                <span class="badge bg-light text-dark"><?php echo count($conversations); ?> conversation(s)</span>
            </div>
            
            <?php if (!empty($conversations)): ?>
                <div class="list-group list-group-flush">
                    <!-- En-tête des colonnes -->
                    <div class="list-group-item py-2 bg-dark">
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <div style="width: 60px;" class="d-flex"></div>
                            <div style="width: 300px;" class="text-white fw-bold">Titre</div>
                            <div style="width: 200px;" class="text-white fw-bold text-center">Auteur</div>
                            <div style="width: 100px;" class="text-center text-white fw-bold">Messages</div>
                            <div class="text-white fw-bold ms-auto">Dernier message</div>
                        </div>
                    </div>
                    <?php foreach ($conversations as $conversation): ?>
                        <div class="list-group-item py-2">
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <!-- Icônes de statut (largeur fixe) -->
                                <div style="width: 60px;" class="d-flex">
                                    <?php if ($conversation->epingle == '1'): ?>
                                        <i class="fas fa-thumbtack text-warning" title="Message épinglé"></i>
                                    <?php endif; ?>
                                    <?php if ($conversation->lu == '0'): ?>
                                        <span class="badge bg-primary ms-1">Nouveau</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Titre (largeur fixe) -->
                                <div style="width: 300px;" class="text-truncate">
                                    <a href="message_view.php?id=<?php echo $conversation->id; ?>" 
                                       class="text-decoration-none text-white">
                                        <?php echo secure_output($conversation->titre); ?>
                                    </a>
                                </div>

                                <!-- Interlocuteur (largeur fixe) -->
                                <div style="width: 200px;" class="text-truncate text-center">
                                    <a href="profile-view.php?username=<?php echo urlencode($conversation->auteur_pseudo); ?>" 
                                       class="text-decoration-none text-info">
                                        <?php echo secure_output($conversation->auteur_pseudo); ?>
                                    </a>
                                </div>

                                <!-- Nombre de messages (largeur fixe) -->
                                <div style="width: 100px;" class="text-center">
                                    <span class="badge bg-secondary">
                                        <?php echo $conversation->nbMess; ?> message(s)
                                    </span>
                                </div>

                                <!-- Date (le reste de l'espace) -->
                                <div class="text-muted ms-auto">
                                    <?php echo format_date($conversation->last_time); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card-body">
                    <p class="text-muted mb-0">Vous n'avez aucun message privé.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>

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