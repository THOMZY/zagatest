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
    "forum.php?id=" . $topic->category_id => $topic->category_name,
    "forum.php?id=" . $topic->forum_id => $topic->forum_name,
    "" => $topic->titre
];

// Pagination
$messages_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $messages_per_page;

// Récupérer le nombre total de messages dans ce topic
$topic_id = (int)$topic_id; // Sécuriser l'ID du topic
$count_query = $db->query("SELECT COUNT(*) FROM rf_messages WHERE idTopic = $topic_id AND visible = '1'");
$total_messages = $count_query->fetchColumn();

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
        u.postheader
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

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Informations du topic -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <p class="mb-0">
                <strong>Sujet créé le :</strong> <?php echo format_date($topic->lastMsgTime); ?>
            </p>
            <span class="badge bg-secondary"><?php echo $total_messages; ?> messages</span>
        </div>
    </div>
</div>

<!-- Liste des messages -->
<?php while ($message = $messages_query->fetch()): ?>
    <div id="message-<?php echo $message->id; ?>" class="message-card">
        <div class="message-header d-flex justify-content-between align-items-center">
            <span>
                Message #<?php echo $message->id; ?> - 
                <strong><?php echo format_date($message->time); ?></strong>
            </span>
            <a href="#message-<?php echo $message->id; ?>" class="btn btn-sm btn-outline-secondary message-link">Lien</a>
        </div>
        
        <?php 
        $header_path = get_header_path($message->postheader);
        if (!empty($header_path)): 
        ?>
        <div class="post-header-container">
            <img src="<?php echo $header_path; ?>" alt="Post Header" class="post-header-image img-fluid">
        </div>
        <?php endif; ?>
        
        <div class="row message-layout g-0">
            <div class="col-md-2">
                <div class="user-info h-100" data-username-initial="<?php echo strtoupper(substr($message->user_name, 0, 1)); ?>">
                    <img src="<?php echo get_avatar_path($message->avatar); ?>" alt="Avatar" class="user-avatar">
                    <h5 class="h6 mb-1"><?php echo secure_output($message->user_name); ?></h5>
                    <small>Inscrit le <?php echo format_date($message->date_inscription); ?></small>
                    <small><?php echo $message->user_post_count; ?> messages</small>
                </div>
            </div>
            <div class="col-md-10">
                <div class="message-content h-100">
                    <?php echo format_message($message->message); ?>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<!-- Pagination -->
<?php
$pagination = pagination($total_messages, $messages_per_page, $current_page, "topic.php?id=$topic_id&page=%d");
echo $pagination;
?>

<!-- Notification que le forum est en lecture seule -->
<div class="alert alert-warning mt-4">
    <p class="mb-0">Ce forum est en mode lecture seule. Il n'est pas possible d'ajouter de nouvelles réponses.</p>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>