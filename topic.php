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
    "forum.php?id=" . $topic->forum_id => $topic->forum_name
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

// Fonction pour créer la pagination améliorée avec bouton "Tout"
function enhanced_pagination($total_items, $items_per_page, $current_page, $url_pattern, $show_all = false) {
    $standard_per_page = 20; // Nombre d'items par page en mode normal
    $total_pages = ceil($total_items / ($show_all ? $standard_per_page : $items_per_page));
    
    if ($total_pages <= 1 && !$show_all) {
        return '';
    }
    
    $html = '<nav aria-label="Pagination"><ul class="pagination pagination-sm flex-wrap justify-content-center">';
    
    // Fonction sécurisée pour générer l'URL de pagination
    $getPageUrl = function($page) use ($url_pattern, $show_all) {
        // Si on est en mode "Tout" et qu'on veut générer une URL pour une page spécifique,
        // on doit s'assurer de retirer le paramètre "all=1"
        if ($show_all) {
            // Remplacer "all=1" par "page=X"
            return str_replace('all=1', 'page=' . $page, $url_pattern);
        } else {
            // Comportement normal
            return str_replace(['%%d', '%d'], $page, $url_pattern);
        }
    };
    
    // URL pour "Afficher tout"
    $all_url = str_replace(['page=%d', 'page=%%d'], 'all=1', $url_pattern);
    
    // Bouton "Précédent"
    if ($current_page > 1 && !$show_all) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $getPageUrl($current_page - 1) . '">Précédent</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Précédent</span></li>';
    }
    
    // Afficher toutes les pages
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page && !$show_all) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $getPageUrl($i) . '">' . $i . '</a></li>';
        }
    }
    
    // Bouton "Suivant"
    if ($current_page < $total_pages && !$show_all) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $getPageUrl($current_page + 1) . '">Suivant</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Suivant</span></li>';
    }
    
    // Bouton "Tout"
    if ($show_all) {
        $html .= '<li class="page-item active"><span class="page-link">Tout</span></li>';
    } else {
        $html .= '<li class="page-item"><a class="page-link" href="' . $all_url . '">Tout</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo convert_smileys(secure_output($topic->titre)); ?> - Archives du Forum</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSS personnalisé -->
    <link href="public/css/style.css" rel="stylesheet">
    
    <!-- CSS spécifique pour les topics -->
    <link href="public/css/topic-styles.css" rel="stylesheet">
</head>
<body>
    <header class="text-white mb-4">
        <div class="container py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h1 class="h3 mb-2 mb-md-0"><a href="index.php" class="text-white text-decoration-none">Archives du Forum</a></h1>
                
                <!-- Formulaire de recherche -->
                <div class="d-flex">
                    <form action="search.php" method="GET" class="d-flex">
                        <input type="text" name="term" class="form-control form-control-sm me-2" placeholder="Rechercher..." aria-label="Rechercher">
                        <button type="submit" class="btn btn-outline-light btn-sm">Rechercher</button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container mb-4">
        <!-- Fil d'Ariane simplifié -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <?php foreach ($breadcrumb as $url => $label): ?>
                    <li class="breadcrumb-item"><a href="<?php echo $url; ?>"><?php echo convert_smileys(secure_output($label)); ?></a></li>
                <?php endforeach; ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo convert_smileys(secure_output($topic->titre)); ?></li>
            </ol>
        </nav>
        
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
            <?php 
            // Toujours utiliser le même format d'URL, qu'on soit en mode "Tout" ou non
            $pagination_url = "topic.php?id=$topic_id";
            $pagination_url .= $show_all ? "&all=1" : "&page=%d";
            echo enhanced_pagination($total_messages, $messages_per_page, $current_page, $pagination_url, $show_all); 
            ?>
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
                    <div class="message-body">
                        <?php echo format_message($message->message); ?>
                    </div>
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
    </main>
    
    <footer class="bg-light py-3 mt-auto">
        <div class="container">
            <p class="text-center text-muted mb-0">Archives du Forum &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personnalisé -->
    <script src="public/js/script.js"></script>
</body>
</html>