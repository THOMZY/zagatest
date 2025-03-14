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

// Vérifier si l'ID du message est spécifié
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: messages.php");
    exit;
}

$mp_id = (int)$_GET['id'];

// Vérifier si l'utilisateur a accès à ce message privé
try {
    $access_stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM rf_mp_mbr 
        WHERE mpid = :mp_id AND idPseudo = :user_id
    ");
    $access_stmt->bindParam(':mp_id', $mp_id, PDO::PARAM_INT);
    $access_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $access_stmt->execute();
    
    $has_access = $access_stmt->fetchColumn();
    
    if (!$has_access) {
        header("Location: messages.php");
        exit;
    }
    
    // Marquer le message comme lu
    $update_stmt = $db->prepare("
        UPDATE rf_mp_mbr 
        SET lu = '1' 
        WHERE mpid = :mp_id AND idPseudo = :user_id
    ");
    $update_stmt->bindParam(':mp_id', $mp_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $update_stmt->execute();
    
    // Récupérer les informations sur la conversation
    $conv_stmt = $db->prepare("
        SELECT 
            mp.id,
            mp.titre,
            mp.auteur,
            u.pseudo AS auteur_pseudo,
            mb.epingle
        FROM 
            rf_mp mp
        JOIN 
            rf_membres u ON mp.auteur = u.id
        JOIN 
            rf_mp_mbr mb ON mp.id = mb.mpid AND mb.idPseudo = :user_id
        WHERE 
            mp.id = :mp_id
    ");
    $conv_stmt->bindParam(':mp_id', $mp_id, PDO::PARAM_INT);
    $conv_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $conv_stmt->execute();
    
    $conversation = $conv_stmt->fetch();
    
    if (!$conversation) {
        header("Location: messages.php");
        exit;
    }
    
    // Définir le titre de la page
    $page_title = "MP: " . $conversation->titre;
    
    // Récupérer les messages de la conversation
    $messages_stmt = $db->prepare("
        SELECT 
            m.id,
            m.message,
            m.time,
            u.id AS user_id,
            u.pseudo AS user_name,
            u.avatar,
            u.signature,
            u.postheader
        FROM 
            rf_mp_mess m
        JOIN 
            rf_membres u ON m.idPseudo = u.id
        WHERE 
            m.idMp = :mp_id
        ORDER BY 
            m.time ASC
    ");
    $messages_stmt->bindParam(':mp_id', $mp_id, PDO::PARAM_INT);
    $messages_stmt->execute();
    
    $messages = $messages_stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données: " . $e->getMessage());
}

// Fonction pour obtenir le bon chemin d'avatar
function get_avatar_path($avatar) {
    if (empty($avatar)) {
        return 'public/img/avatars/default-avatar.png';
    }
    
    // Si c'est une URL complète
    if (strpos($avatar, 'http://') === 0 || strpos($avatar, 'https://') === 0) {
        return $avatar;
    }
    
    // Sinon, c'est un fichier local
    $local_path = 'public/img/avatars/' . $avatar;
    if (file_exists($local_path)) {
        return $local_path;
    }
    
    return 'public/img/avatars/default-avatar.png';
}

// Fonction pour obtenir le bon chemin du header
function get_header_path($header) {
    if (empty($header)) {
        return '';
    }
    
    // Si c'est une URL complète
    if (strpos($header, 'http://') === 0 || strpos($header, 'https://') === 0) {
        // On garde l'URL externe telle quelle
        return $header;
    }
    
    // Sinon, c'est un fichier local
    $local_path = 'public/img/bars/' . $header;
    if (file_exists($local_path)) {
        return $local_path;
    }
    
    return '';
}

// Épingler/Désépingler la conversation
if (isset($_GET['toggle_pin']) && $_GET['toggle_pin'] == 1) {
    try {
        $new_status = $conversation->epingle == '1' ? '0' : '1';
        
        $pin_stmt = $db->prepare("
            UPDATE rf_mp_mbr 
            SET epingle = :status 
            WHERE mpid = :mp_id AND idPseudo = :user_id
        ");
        $pin_stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
        $pin_stmt->bindParam(':mp_id', $mp_id, PDO::PARAM_INT);
        $pin_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $pin_stmt->execute();
        
        // Rediriger pour éviter les soumissions multiples
        header("Location: message_read.php?id=" . $mp_id);
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de la mise à jour: " . $e->getMessage());
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Fil d'Ariane et actions -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="messages.php">Messages privés</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo secure_output($conversation->titre); ?></li>
        </ol>
    </nav>
    <div class="btn-group">
        <a href="message_read.php?id=<?php echo $mp_id; ?>&toggle_pin=1" class="btn btn-sm <?php echo $conversation->epingle == '1' ? 'btn-warning' : 'btn-outline-warning'; ?>">
            <i class="fas fa-thumbtack me-1"></i> <?php echo $conversation->epingle == '1' ? 'Désépingler' : 'Épingler'; ?>
        </a>
    </div>
</div>

<!-- Titre du topic avec date et nombre de messages -->
<div class="topic-header mb-4 text-center">
    <h2 class="display-6"><?php echo secure_output($conversation->titre); ?></h2>
    <div class="topic-info-container">
        <div class="topic-info">
            <span class="text-muted">Conversation avec: </span>
            <span><?php echo secure_output($conversation->auteur_pseudo); ?></span>
        </div>
        <div class="message-count">
            <span class="badge"><?php echo count($messages); ?> messages</span>
        </div>
    </div>
</div>

<!-- Liste des messages -->
<?php foreach ($messages as $message): ?>
    <div id="message-<?php echo $message->id; ?>" class="message-card mb-4">
        <div class="message-outer-container">
            <!-- Avatar à gauche -->
            <div class="avatar-container">
                <img src="<?php echo get_avatar_path($message->avatar); ?>" alt="Avatar de <?php echo secure_output($message->user_name); ?>" class="user-avatar-img">
            </div>
            
            <!-- Header avec informations utilisateur -->
            <?php 
            $header_path = get_header_path($message->postheader);
            $hasCustomHeader = !empty($header_path);
            ?>
            
            <div class="header-container <?php echo $hasCustomHeader ? 'has-custom-background' : ''; ?>">
                <?php if ($hasCustomHeader): ?>
                <img src="<?php echo $header_path; ?>" alt="Background" class="header-background">
                <?php endif; ?>
                
                <div class="header-content">
                    <div class="link-button-container">
                        <a href="#message-<?php echo $message->id; ?>" class="message-link" title="Lien direct vers ce message">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link" viewBox="0 0 16 16">
                                <path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9c-.086 0-.17.01-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/>
                                <path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4.02 4.02 0 0 1-.82 1H12a3 3 0 1 0 0-6H9z"/>
                            </svg>
                        </a>
                    </div>
                    <div class="user-info-header">
                        <div class="username"><?php echo secure_output($message->user_name); ?></div>
                        <div class="user-details">
                            <?php echo format_date($message->time); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contenu du message -->
            <div class="message-body"><?php echo format_message($message->message); ?></div>
            <?php if (!empty($message->signature)): ?>
                <hr class="signature-separator">
                <div class="signature-content"><?php echo format_message($message->signature); ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<!-- Notification que le forum est en lecture seule -->
<div class="alert alert-warning mt-4">
    <p class="mb-0">Ce forum est en mode lecture seule. Il n'est pas possible d'ajouter de nouvelles réponses aux messages privés.</p>
</div>

<!-- Script pour gérer l'affichage des signatures -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer le paramètre des signatures
    const settings = JSON.parse(localStorage.getItem('forumSettings') || '{"showSignatures": true}');
    const signatures = document.querySelectorAll('.signature-content, .signature-separator');
    
    // Fonction pour mettre à jour l'affichage des signatures
    function updateSignatures(show) {
        signatures.forEach(signature => {
            signature.style.display = show ? 'block' : 'none';
        });
    }
    
    // Appliquer l'état initial
    updateSignatures(settings.showSignatures);
    
    // Écouter les changements de paramètres
    window.addEventListener('settingsChanged', function(event) {
        updateSignatures(event.detail.showSignatures);
    });
});
</script>

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