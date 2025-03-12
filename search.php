<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';
require_once 'includes/functions.php';

// Définir le titre de la page
$page_title = "Recherche";

// Définir le fil d'Ariane
$breadcrumb = [
    "" => "Recherche"
];

// Variables pour la recherche
$search_term = isset($_GET['term']) ? trim($_GET['term']) : '';
$search_type = isset($_GET['type']) ? $_GET['type'] : 'title'; // Par défaut, recherche dans les titres
$search_forum = isset($_GET['forum']) ? (int)$_GET['forum'] : 0; // 0 signifie "tous les forums"
$results = [];
$total_results = 0;

// Si le terme de recherche vient du petit formulaire de l'en-tête, on utilise le type par défaut (titre)
if (isset($_GET['term']) && !isset($_GET['type'])) {
    $search_type = 'title';
}

// Pagination
$results_per_page = 20;
$show_all = isset($_GET['all']) && $_GET['all'] == 1;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Effectuer la recherche si un terme est fourni
if (!empty($search_term)) {
    // Sécuriser le terme de recherche pour les requêtes SQL
    $search_term_safe = '%' . $search_term . '%';
    
    // Récupérer le nombre total de résultats pour la pagination
    if ($search_type === 'author') {
        // Recherche par auteur (pseudo)
        $count_stmt = $db->prepare("
            SELECT 
                COUNT(DISTINCT t.id) AS total
            FROM 
                rf_topics t
            JOIN 
                rf_membres u ON t.auteur = u.id
            WHERE 
                u.pseudo LIKE :search_term
                AND t.visible = '1'
                " . ($search_forum > 0 ? "AND t.idForum = :forum_id" : "") . "
        ");
    } elseif ($search_type === 'content') {
        // Recherche dans le contenu des messages
        // Exclure les URL contenant le terme recherché
        $count_stmt = $db->prepare("
            SELECT 
                COUNT(DISTINCT t.id) AS total
            FROM 
                rf_topics t
            JOIN 
                rf_messages m ON m.idTopic = t.id
            WHERE 
                m.message LIKE :search_term
                AND m.message NOT LIKE CONCAT('%href=%', :search_term_raw, '%')
                AND m.message NOT LIKE CONCAT('%http%', :search_term_raw, '%')
                AND m.message NOT LIKE CONCAT('%www.%', :search_term_raw, '%')
                AND m.message NOT LIKE CONCAT('%url=%', :search_term_raw, '%')
                AND t.visible = '1'
                AND m.visible = '1'
                " . ($search_forum > 0 ? "AND t.idForum = :forum_id" : "") . "
        ");
    } else {
        // Recherche par titre (par défaut)
        $count_stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total
            FROM 
                rf_topics t
            WHERE 
                t.titre LIKE :search_term
                AND t.visible = '1'
                " . ($search_forum > 0 ? "AND t.idForum = :forum_id" : "") . "
        ");
    }
    
    // Exécuter la requête de comptage
    $count_stmt->bindParam(':search_term', $search_term_safe, PDO::PARAM_STR);
    if ($search_type === 'content') {
        $search_term_raw = $search_term; // Terme sans les %
        $count_stmt->bindParam(':search_term_raw', $search_term_raw, PDO::PARAM_STR);
    }
    if ($search_forum > 0) {
        $count_stmt->bindParam(':forum_id', $search_forum, PDO::PARAM_INT);
    }
    $count_stmt->execute();
    $total_results = $count_stmt->fetchColumn();
    
    // Ajuster la pagination si "show all" est activé
    if ($show_all) {
        $offset = 0;
        $results_per_page = $total_results; // Tous les résultats
    } else {
        $offset = ($current_page - 1) * $results_per_page;
    }
    
    // Requête différente selon le type de recherche
    if ($search_type === 'author') {
        // Recherche par auteur (pseudo)
        $stmt = $db->prepare("
            SELECT 
                t.id,
                t.titre AS title,
                t.lastMsgTime AS date,
                u.pseudo AS author,
                f.nom AS forum_name,
                f.id AS forum_id,
                COUNT(m.id) - 1 AS reply_count
            FROM 
                rf_topics t
            JOIN 
                rf_membres u ON t.auteur = u.id
            JOIN 
                rf_forums f ON t.idForum = f.id
            LEFT JOIN 
                rf_messages m ON m.idTopic = t.id AND m.visible = '1'
            WHERE 
                u.pseudo LIKE :search_term
                AND t.visible = '1'
                " . ($search_forum > 0 ? "AND t.idForum = :forum_id" : "") . "
            GROUP BY 
                t.id
            ORDER BY 
                t.lastMsgTime DESC
            LIMIT :offset, :limit
        ");
    } elseif ($search_type === 'content') {
        // Recherche dans le contenu des messages
        // Exclure les URL contenant le terme recherché
        $stmt = $db->prepare("
            SELECT 
                t.id,
                t.titre AS title,
                t.lastMsgTime AS date,
                u.pseudo AS author,
                f.nom AS forum_name,
                f.id AS forum_id,
                (SELECT COUNT(*) - 1 FROM rf_messages WHERE idTopic = t.id AND visible = '1') AS reply_count,
                (SELECT message FROM rf_messages WHERE idTopic = t.id AND message LIKE :search_term 
                 AND message NOT LIKE CONCAT('%href=%', :search_term_raw, '%')
                 AND message NOT LIKE CONCAT('%http%', :search_term_raw, '%')
                 AND message NOT LIKE CONCAT('%www.%', :search_term_raw, '%')
                 AND message NOT LIKE CONCAT('%url=%', :search_term_raw, '%')
                 AND visible = '1' LIMIT 1) AS message_preview
            FROM 
                rf_topics t
            JOIN 
                rf_membres u ON t.auteur = u.id
            JOIN 
                rf_forums f ON t.idForum = f.id
            JOIN 
                rf_messages m ON m.idTopic = t.id
            WHERE 
                m.message LIKE :search_term
                AND m.message NOT LIKE CONCAT('%href=%', :search_term_raw, '%')
                AND m.message NOT LIKE CONCAT('%http%', :search_term_raw, '%')
                AND m.message NOT LIKE CONCAT('%www.%', :search_term_raw, '%')
                AND m.message NOT LIKE CONCAT('%url=%', :search_term_raw, '%')
                AND t.visible = '1'
                AND m.visible = '1'
                " . ($search_forum > 0 ? "AND t.idForum = :forum_id" : "") . "
            GROUP BY 
                t.id
            ORDER BY 
                t.lastMsgTime DESC
            LIMIT :offset, :limit
        ");
    } else {
        // Recherche par titre (par défaut)
        $stmt = $db->prepare("
            SELECT 
                t.id,
                t.titre AS title,
                t.lastMsgTime AS date,
                u.pseudo AS author,
                f.nom AS forum_name,
                f.id AS forum_id,
                COUNT(m.id) - 1 AS reply_count
            FROM 
                rf_topics t
            JOIN 
                rf_membres u ON t.auteur = u.id
            JOIN 
                rf_forums f ON t.idForum = f.id
            LEFT JOIN 
                rf_messages m ON m.idTopic = t.id AND m.visible = '1'
            WHERE 
                t.titre LIKE :search_term
                AND t.visible = '1'
                " . ($search_forum > 0 ? "AND t.idForum = :forum_id" : "") . "
            GROUP BY 
                t.id
            ORDER BY 
                t.lastMsgTime DESC
            LIMIT :offset, :limit
        ");
    }
    
    // Exécuter la requête principale
    $stmt->bindParam(':search_term', $search_term_safe, PDO::PARAM_STR);
    if ($search_type === 'content') {
        $search_term_raw = $search_term; // Terme sans les %
        $stmt->bindParam(':search_term_raw', $search_term_raw, PDO::PARAM_STR);
    }
    if ($search_forum > 0) {
        $stmt->bindParam(':forum_id', $search_forum, PDO::PARAM_INT);
    }
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $results_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();
}

// Récupérer la liste des forums pour le menu déroulant
$forums_query = $db->query("
    SELECT 
        id, 
        nom
    FROM 
        rf_forums 
    ORDER BY 
        id ASC
");
$forums = $forums_query->fetchAll();

// Fonction pour construire une liste simple des forums (non hiérarchique)
function build_forum_list($forums) {
    $html = '';
    foreach ($forums as $forum) {
        $html .= '<option value="' . $forum->id . '"';
        $html .= (isset($_GET['forum']) && $_GET['forum'] == $forum->id) ? ' selected' : '';
        $html .= '>' . secure_output($forum->nom) . '</option>';
    }
    return $html;
}

// Construire l'URL de pagination
$pagination_url = '';
if (!empty($search_term)) {
    $pagination_url = "search.php?term=" . urlencode($search_term) . "&type=" . urlencode($search_type);
    if ($search_forum > 0) {
        $pagination_url .= "&forum=" . $search_forum;
    }
    $pagination_url .= $show_all ? "&all=1" : "&page=%d";
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<style>
/* Styles pour les résultats de recherche dans les messages */
.message-search-result {
  background-color: #f8f9fa;
  border-radius: 0.25rem;
  overflow: hidden;
}

.message-search-result .topic-header {
  padding: 0.75rem 1.25rem;
  background-color: #f1f3f5;
}

.message-search-result .topic-header h5 {
  margin-bottom: 0.25rem;
}

.message-search-result .message-preview {
  padding: 0.75rem 0;
  line-height: 1.5;
}

/* Mise en évidence du terme recherché */
.message-search-result .bg-warning {
  padding: 0.1rem 0.2rem;
  border-radius: 0.2rem;
}
</style>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title h5 mb-0">Recherche</h3>
    </div>
    <div class="card-body">
        <!-- Animation de chargement (cachée par défaut) -->
        <div id="loadingSpinner" style="display: none;" class="text-center my-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Recherche en cours...</span>
            </div>
            <p class="mt-3">Recherche en cours, veuillez patienter...</p>
            <p class="text-muted small">La recherche peut prendre quelques instants si la base de données est volumineuse.</p>
        </div>

        <!-- Formulaire de recherche qui sera caché pendant le chargement -->
        <div id="searchFormContainer">
            <form action="search.php" method="GET" class="mb-4" id="searchForm" onsubmit="showLoading()">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="term" class="form-control" placeholder="Terme de recherche" value="<?php echo secure_output($search_term); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="author" <?php echo $search_type === 'author' ? 'selected' : ''; ?>>Rechercher par auteur</option>
                            <option value="title" <?php echo $search_type === 'title' ? 'selected' : ''; ?>>Rechercher dans les titres</option>
                            <option value="content" <?php echo $search_type === 'content' ? 'selected' : ''; ?>>Rechercher dans les messages</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="forum" class="form-select">
                            <option value="0">Tous les forums</option>
                            <?php echo build_forum_list($forums); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" id="searchButton">Rechercher</button>
                    </div>
                </div>
            </form>
        </div>

        <div id="searchResults">
          <?php if (!empty($search_term)): ?>
            <div class="alert alert-info">
                <?php if ($search_type === 'author'): ?>
                    Recherche des sujets créés par <strong><?php echo secure_output($search_term); ?></strong>
                <?php elseif ($search_type === 'content'): ?>
                    Recherche de "<strong><?php echo secure_output($search_term); ?></strong>" dans les messages
                <?php else: ?>
                    Recherche de "<strong><?php echo secure_output($search_term); ?></strong>" dans les titres
                <?php endif; ?>
                
                <?php if ($search_forum > 0): ?>
                    <?php
                    // Récupérer le nom du forum
                    $forum_name_query = $db->prepare("SELECT nom FROM rf_forums WHERE id = :forum_id");
                    $forum_name_query->bindParam(':forum_id', $search_forum, PDO::PARAM_INT);
                    $forum_name_query->execute();
                    $forum_name = $forum_name_query->fetchColumn();
                    ?>
                    dans le forum <strong><?php echo secure_output($forum_name); ?></strong>
                <?php endif; ?>
                - <?php echo $total_results; ?> résultat(s) trouvé(s)
            </div>
            
            <?php if (count($results) > 0): ?>
                <!-- Pagination en haut de page -->
                <?php if (!empty($pagination_url)): ?>
                <div class="top-pagination mb-4">
                    <?php echo enhanced_pagination($total_results, $results_per_page, $current_page, $pagination_url, $show_all); ?>
                </div>
                <?php endif; ?>
                
                <div class="list-group">
                    <?php foreach ($results as $result): ?>
                        <?php if ($search_type === 'content' && isset($result->message_preview)): ?>
                            <!-- Résultat de recherche dans les messages avec message_id -->
                            <div class="message-search-result mb-3">
                                <!-- Titre du topic comme en-tête -->
                                <div class="topic-header border-bottom pb-2 mb-2">
                                    <h5 class="mb-1">
                                        <a href="topic.php?id=<?php echo $result->id; ?>" class="text-decoration-none">
                                            <?php echo convert_smileys(secure_output($result->title)); ?>
                                        </a>
                                    </h5>
                                    <div class="d-flex justify-content-between text-muted small">
                                        <span>par <strong><?php echo secure_output($result->author); ?></strong> dans <a href="forum.php?id=<?php echo $result->forum_id; ?>"><?php echo secure_output($result->forum_name); ?></a></span>
                                        <span><?php echo format_date($result->date); ?></span>
                                    </div>
                                </div>
                                
                                <!-- Message avec lien direct -->
                                <?php 
                                // Récupérer l'ID du message qui contient le terme recherché
                                $message_id_query = $db->prepare("
                                    SELECT id 
                                    FROM rf_messages 
                                    WHERE idTopic = :topic_id 
                                    AND message LIKE :search_term
                                    AND message NOT LIKE CONCAT('%href=%', :search_term_raw, '%')
                                    AND message NOT LIKE CONCAT('%http%', :search_term_raw, '%')
                                    AND message NOT LIKE CONCAT('%www.%', :search_term_raw, '%')
                                    AND message NOT LIKE CONCAT('%url=%', :search_term_raw, '%')
                                    AND visible = '1'
                                    LIMIT 1
                                ");
                                $message_id_query->bindParam(':topic_id', $result->id, PDO::PARAM_INT);
                                $message_id_query->bindParam(':search_term', $search_term_safe, PDO::PARAM_STR);
                                $message_id_query->bindParam(':search_term_raw', $search_term, PDO::PARAM_STR);
                                $message_id_query->execute();
                                $message_id = $message_id_query->fetchColumn();
                                ?>
                                
                                <a href="topic.php?id=<?php echo $result->id; ?>#message-<?php echo $message_id; ?>" class="list-group-item list-group-item-action">
                                    <div class="message-preview">
                                        <?php 
                                        // Extraire un extrait du message contenant le terme recherché
                                        $preview = strip_tags($result->message_preview);
                                        $preview = preg_replace('/\[.*?\]/', '', $preview); // Enlever les tags BBCode
                                        
                                        // Trouver la position du terme recherché (sans les %)
                                        $search_term_clean = str_replace('%', '', $search_term_safe);
                                        $pos = stripos($preview, $search_term_clean);
                                        
                                        // Créer un extrait avec le contexte autour du terme
                                        if ($pos !== false) {
                                            $start = max(0, $pos - 75);
                                            $length = min(250, strlen($preview) - $start);
                                            $excerpt = substr($preview, $start, $length);
                                            
                                            // Ajouter des points de suspension si nécessaire
                                            if ($start > 0) {
                                                $excerpt = '...' . $excerpt;
                                            }
                                            if ($start + $length < strlen($preview)) {
                                                $excerpt .= '...';
                                            }
                                            
                                            // Mettre en évidence le terme recherché
                                            $excerpt = preg_replace('/(' . preg_quote($search_term_clean, '/') . ')/i', '<strong class="bg-warning">$1</strong>', $excerpt);
                                            
                                            echo $excerpt;
                                        } else {
                                            // Fallback si on ne trouve pas le terme (devrait être rare)
                                            echo substr(secure_output($preview), 0, 200) . '...';
                                        }
                                        ?>
                                    </div>
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Résultats de recherche standard (titre ou auteur) -->
                            <a href="topic.php?id=<?php echo $result->id; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo convert_smileys(secure_output($result->title)); ?></h5>
                                    <small class="text-muted"><?php echo format_date($result->date); ?></small>
                                </div>
                                <p class="mb-1">
                                    <small>
                                        par <strong><?php echo secure_output($result->author); ?></strong> 
                                        dans <a href="forum.php?id=<?php echo $result->forum_id; ?>"><?php echo secure_output($result->forum_name); ?></a>
                                        - <?php echo $result->reply_count; ?> réponse(s)
                                    </small>
                                </p>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination en bas de page -->
                <?php if (!empty($pagination_url)): ?>
                <div class="bottom-pagination mt-4">
                    <?php echo enhanced_pagination($total_results, $results_per_page, $current_page, $pagination_url, $show_all); ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    Aucun résultat trouvé pour votre recherche.
                </div>
            <?php endif; ?>
        <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>

<!-- Script pour l'animation de chargement -->
<script>
function showLoading() {
    // Cette fonction est appelée lors de la soumission du formulaire
    document.getElementById('searchFormContainer').style.display = 'none';
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('loadingSpinner').style.display = 'block';
    return true; // Permettre la soumission du formulaire
}

// S'assurer que l'animation de chargement est cachée si la page est chargée avec des résultats
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.alert-info')) {
        document.getElementById('loadingSpinner').style.display = 'none';
    }
});
</script>