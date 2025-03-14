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

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title h5 mb-0">Mes messages privés</h3>
                <span class="badge bg-light text-dark"><?php echo count($conversations); ?> conversation(s)</span>
            </div>
            
            <?php if (!empty($conversations)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($conversations as $conversation): ?>
                        <a href="message_read.php?id=<?php echo $conversation->id; ?>" class="list-group-item list-group-item-action message-card d-flex flex-column">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center">
                                        <?php if ($conversation->epingle == '1'): ?>
                                            <i class="fas fa-thumbtack text-warning me-2" title="Message épinglé"></i>
                                        <?php endif; ?>
                                        
                                        <h4 class="h6 mb-1">
                                            <?php if ($conversation->lu == '0'): ?>
                                                <span class="badge bg-primary me-1">Nouveau</span>
                                            <?php endif; ?>
                                            <?php echo secure_output($conversation->titre); ?>
                                        </h4>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Conversation avec <?php echo secure_output($conversation->auteur_pseudo); ?>
                                    </p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="small">
                                        <?php echo $conversation->nbMess; ?> message(s)
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <small class="text-muted" data-bs-toggle="tooltip" title="Dernier message">
                                        <?php echo format_date($conversation->last_time); ?>
                                    </small>
                                </div>
                            </div>
                        </a>
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

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>

<?php if (get_user_access_level() == 100): // Lien secret pour les administrateurs ?>
<div class="text-center mt-4 mb-4">
    <a href="mp_admin.php" class="btn btn-sm btn-outline-danger">
        <i class="fas fa-lock me-1"></i> Administration MP
    </a>
</div>
<?php endif; ?>

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