<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'ID du topic est spécifié
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$topic_id = (int)$_GET['id'];

// Récupérer les informations du topic
$topic_id = (int)$topic_id; // Sécuriser l'ID du topic
$topic_query = $db->query("
    SELECT 
        t.id,
        t.titre,
        t.lastMsgTime,
        f.id AS forum_id,
        f.nom AS forum_name,
        c.id AS category_id,
        c.nom AS category_name
    FROM 
        rf_topics t
    JOIN 
        rf_forums f ON t.idForum = f.id
    JOIN 
        rf_categories c ON f.idCat = c.id
    WHERE 
        t.id = $topic_id
        AND t.visible = '1'
");
$topic = $topic_query->fetch();

// Si le topic n'existe pas, rediriger vers la page d'accueil
if (!$topic) {
    header('Location: index.php');
    exit;
}

// Définir le titre de la page
$page_title = $topic->titre;

// Définir le fil d'Ariane
$breadcrumb = [
    "index.php" => "Accueil",
    "forum.php?id=" . $topic->category_id => $topic->category_name,
    "forum.php?id=" . $topic->forum_id => $topic->forum_name,
    "" => $topic->titre
];

// Récupérer le nombre total de messages dans ce topic
$topic_id = (int)$topic_id; // Sécuriser l'ID du topic
$count_query = $db->query("SELECT COUNT(*) FROM rf_messages WHERE idTopic = $topic_id AND visible = '1'");
$total_messages = $count_query->fetchColumn();

// Pagination
$messages_per_page = 20;
$show_all = isset($_GET['all']) && $_GET['all'] == 1;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

if ($show_all) {
    $offset = 0;
    $messages_per_page = $total_messages; // Tous les messages
    // Ne pas changer current_page ici pour garder la page actuelle
} else {
    $offset = ($current_page - 1) * $messages_per_page;
}

// Conversion des paramètres en entiers pour éviter les problèmes de syntaxe SQL
$topic_id = (int)$topic_id;
$offset = (int)$offset;
$messages_per_page = (int)$messages_per_page;

// Exécution de la requête avec les paramètres directement intégrés
$messages_query = $db->query("
    SELECT 
        m.id,
        m.message,
        m.time,
        u.id AS user_id,
        u.pseudo AS user_name,
        u.time AS date_inscription,
        u.avatar,
        u.nbmess AS user_post_count,
        u.postheader,
        u.signature
    FROM 
        rf_messages m
    JOIN 
        rf_membres u ON m.idPseudo = u.id
    WHERE 
        m.idTopic = $topic_id
        AND m.visible = '1'
    ORDER BY 
        m.time ASC
    LIMIT $offset, $messages_per_page
");

// Fonction pour obtenir le bon chemin d'avatar
function get_avatar_path($avatar) {
    if (empty($avatar)) {
        return 'public/img/avatars/default-avatar.png'; // Chemin corrigé
    }
    
    // Si c'est une URL complète
    if (strpos($avatar, 'http://') === 0 || strpos($avatar, 'https://') === 0) {
        // On garde l'URL externe telle quelle
        return $avatar;
    }
    
    // Sinon, c'est un fichier local
    $local_path = 'public/img/avatars/' . $avatar;
    if (file_exists($local_path)) {
        return $local_path;
    }
    
    // Si le fichier local n'existe pas, utiliser l'avatar par défaut
    return 'public/img/avatars/default-avatar.png'; // Chemin corrigé
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

// Inclure l'en-tête commun
include 'includes/header.php';

// Toujours utiliser le même format d'URL, qu'on soit en mode "Tout" ou non
$pagination_url = "topic.php?id=$topic_id";
$pagination_url .= $show_all ? "&all=1" : "&page=%d";

// Nous utilisons la fonction de pagination de includes/ui/pagination_functions.php
?>

<!-- Titre du topic avec date et nombre de messages -->
<div class="topic-header mb-4 text-center">
    <h2 class="display-6"><?php echo convert_smileys(secure_output($topic->titre)); ?></h2>
    <div class="topic-info-container">
        <div class="topic-info">
            <span class="text-muted">Sujet créé le : </span>
            <span><?php echo format_date($topic->lastMsgTime); ?></span>
        </div>
        <div class="message-count">
            <span class="badge"><?php echo $total_messages; ?> messages</span>
        </div>
    </div>
</div>

<!-- Pagination en haut de page -->
<div class="top-pagination mb-4">
    <?php echo enhanced_pagination($total_messages, $messages_per_page, $current_page, $pagination_url, $show_all); ?>
</div>

<!-- Liste des messages -->
<?php while ($message = $messages_query->fetch()): ?>
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
                            Points: <?php echo $message->user_post_count; ?> | Messages: <?php echo $message->user_post_count; ?> | <?php echo format_date($message->time); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contenu du message -->
            <div class="message-body"><?php echo format_message($message->message); ?></div><?php if (!empty($message->signature)): ?><hr class="signature-separator"><div class="signature-content"><?php echo format_message($message->signature); ?></div><?php endif; ?>
        </div>
    </div>
<?php endwhile; ?>

<!-- Pagination en bas de page -->
<div class="bottom-pagination mb-4">
    <?php echo enhanced_pagination($total_messages, $messages_per_page, $current_page, $pagination_url, $show_all); ?>
</div>

<!-- Notification que le forum est en lecture seule -->
<div class="alert alert-warning mt-4">
    <p class="mb-0">Ce forum est en mode lecture seule. Il n'est pas possible d'ajouter de nouvelles réponses.</p>
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