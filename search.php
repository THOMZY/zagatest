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
                (SELECT id FROM rf_messages WHERE idTopic = t.id AND message LIKE :search_term 
                 AND message NOT LIKE CONCAT('%href=%', :search_term_raw, '%')
                 AND message NOT LIKE CONCAT('%http%', :search_term_raw, '%')
                 AND message NOT LIKE CONCAT('%www.%', :search_term_raw, '%')
                 AND message NOT LIKE CONCAT('%url=%', :search_term_raw, '%')
                 AND visible = '1' LIMIT 1) AS message_id,
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
$pagination_url = 'search.php?term=' . urlencode($search_term) . '&type=' . $search_type;
if ($search_forum > 0) {
    $pagination_url .= '&forum=' . $search_forum;
}
$pagination_url .= $show_all ? "&all=1" : "&page=%d";

// Inclure l'en-tête
include 'includes/header.php';
?>

<style>
/* Style général pour les cartes de résultats */
.list-group-item.topic-card {
    background-color: #1e1e1e;
    border: none;
    padding: 8px 12px;
    transition: background-color 0.2s;
    text-decoration: none;
    color: #64b5f6;
}

.list-group-item.topic-card:hover {
    background-color: #2d2d2d;
    text-decoration: none;
    color: #90caf9;
}

/* Style pour le titre */
.topic-card .h6 {
    color: inherit;
    font-size: 14px;
    margin-bottom: 0.25rem;
    font-weight: normal;
}

/* Style pour les informations de base */
.text-muted {
    color: #888 !important;
}

.text-muted strong {
    color: #aaa !important;
}

.forum-name {
    color: #64b5f6;
}

.text-muted a {
    color: #64b5f6 !important;
}

.text-muted a:hover {
    color: #90caf9 !important;
    text-decoration: underline;
}

.small {
    font-size: 12px;
}

/* Style pour l'aperçu du message */
.message-preview {
    margin-top: 0;
    padding: 8px 12px;
    background-color: #2a2a2a;
    color: #bbb;
    font-size: 13px;
    line-height: 1.4;
    border-top: 1px solid #333;
}

.message-preview a {
    color: #64b5f6;
    text-decoration: none;
}

.message-preview a:hover {
    color: #90caf9;
    text-decoration: underline;
}

/* Style pour le terme recherché en surbrillance */
.search-highlight {
    background-color: rgba(25, 118, 210, 0.15);
    color: #90caf9;
    padding: 0 3px;
    border-radius: 2px;
}

/* Séparateur entre les résultats */
.list-group-flush .list-group-item + .list-group-item {
    border-top: 1px solid #333 !important;
}

/* Style pour le conteneur des résultats */
.list-group-flush {
    background-color: #1e1e1e;
    border-radius: 3px;
    overflow: hidden;
}

/* Style pour l'en-tête des résultats */
.card-header {
    background-color: #1a237e !important;
    border-bottom: none;
    padding: 10px 15px;
}

.card-header .h5 {
    font-size: 16px;
    font-weight: normal;
}

.card-header .badge {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
    font-weight: normal;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 3px;
}

/* Style pour la mise en page des colonnes */
.row.align-items-center {
    margin: 0 -12px;
}

.row.align-items-center > [class*="col-"] {
    padding: 0 12px;
}

/* Style pour les tooltips */
[data-bs-toggle="tooltip"] {
    cursor: help;
}
</style>

<!-- Formulaire de recherche -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title h5 mb-0">Recherche avancée</h3>
    </div>
    <div class="card-body">
        <!-- Animation de chargement (cachée par défaut) -->
        <div id="loadingSpinner" style="display: none;" class="text-center my-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Recherche en cours...</span>
            </div>
            <p class="mt-3 text-white">Recherche en cours, veuillez patienter...</p>
            <p class="text-muted small">La recherche peut prendre quelques instants car la base de données est volumineuse.</p>
        </div>

        <!-- Formulaire de recherche -->
        <div id="searchFormContainer">
            <form method="get" action="search.php" class="row g-3" onsubmit="return showLoading()">
                <div class="col-md-6">
                    <label for="term" class="form-label text-white">Terme à rechercher</label>
                    <input type="text" class="form-control" id="term" name="term" value="<?php echo secure_output($search_term); ?>" required>
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label text-white">Type de recherche</label>
                    <select class="form-select" id="type" name="type">
                        <option value="author"<?php echo $search_type === 'author' ? ' selected' : ''; ?>>Auteur</option>
                        <option value="title"<?php echo $search_type === 'title' ? ' selected' : ''; ?>>Titre</option>
                        <option value="content"<?php echo $search_type === 'content' ? ' selected' : ''; ?>>Message</option>
                        
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="forum" class="form-label text-white">Forum</label>
                    <select class="form-select" id="forum" name="forum">
                        <option value="0">Tous les forums</option>
                        <?php echo build_forum_list($forums); ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="searchResults"<?php echo empty($search_term) ? ' style="display: none;"' : ''; ?>>
    <?php if (!empty($search_term)): ?>
        <!-- Pagination en haut de page -->
        <div class="top-pagination mb-4">
            <?php echo enhanced_pagination($total_results, $results_per_page, $current_page, $pagination_url, $show_all); ?>
        </div>

        <!-- Liste des résultats -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title h5 mb-0">Résultats de recherche</h3>
                <span class="badge bg-light text-dark"><?php echo $total_results; ?> résultats</span>
            </div>
            
            <?php if (!empty($results)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($results as $topic): ?>
                        <?php if ($search_type === 'content'): ?>
                            <div class="list-group-item topic-card">
                                <a href="topic.php?id=<?php echo $topic->id; ?>" class="row align-items-center text-decoration-none">
                                    <div class="col-md-7">
                                        <h4 class="h6 mb-1">
                                            <?php 
                                            $titre_securise = secure_output($topic->title);
                                            echo convert_smileys($titre_securise);
                                            ?>
                                        </h4>
                                        <p class="text-muted small mb-0">
                                            Par <strong><?php echo secure_output($topic->author); ?></strong>
                                            dans <span class="forum-name"><?php echo secure_output($topic->forum_name); ?></span>
                                        </p>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="small text-muted">
                                            <?php echo $topic->reply_count; ?> réponses
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <small class="text-muted" data-bs-toggle="tooltip" title="Dernier message">
                                            <?php echo format_date($topic->date); ?>
                                        </small>
                                    </div>
                                </a>
                                <?php if (isset($topic->message_preview)): ?>
                                    <div class="message-preview">
                                        <?php 
                                        $preview = secure_output(strip_tags($topic->message_preview));
                                        $pos = stripos($preview, $search_term);
                                        if ($pos !== false) {
                                            $start = max(0, $pos - 75);
                                            $length = 150;
                                            
                                            if ($start > 0) {
                                                $preview = substr($preview, $start);
                                                $firstSpace = strpos($preview, ' ');
                                                if ($firstSpace !== false) {
                                                    $preview = substr($preview, $firstSpace + 1);
                                                }
                                                $preview = '...' . $preview;
                                            }
                                            
                                            if (strlen($preview) > $length) {
                                                $preview = substr($preview, 0, $length);
                                                $lastSpace = strrpos($preview, ' ');
                                                if ($lastSpace !== false) {
                                                    $preview = substr($preview, 0, $lastSpace) . '...';
                                                } else {
                                                    $preview .= '...';
                                                }
                                            }
                                            
                                            $preview = preg_replace('/(' . preg_quote($search_term, '/') . ')/i', 
                                                '<span class="search-highlight">$1</span>', 
                                                $preview);
                                        }
                                        ?>
                                        <a href="topic.php?id=<?php echo $topic->id; ?>#message-<?php echo $topic->message_id; ?>">
                                            <?php echo $preview; ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <a href="topic.php?id=<?php echo $topic->id; ?>" class="list-group-item list-group-item-action topic-card">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <h4 class="h6 mb-1">
                                            <?php 
                                            $titre_securise = secure_output($topic->title);
                                            echo convert_smileys($titre_securise);
                                            ?>
                                        </h4>
                                        <p class="text-muted small mb-0">
                                            Par <strong><?php echo secure_output($topic->author); ?></strong>
                                            dans <span class="forum-name"><?php echo secure_output($topic->forum_name); ?></span>
                                        </p>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="small text-muted">
                                            <?php echo $topic->reply_count; ?> réponses
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <small class="text-muted" data-bs-toggle="tooltip" title="Dernier message">
                                            <?php echo format_date($topic->date); ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card-body">
                    <p class="text-muted mb-0">Aucun résultat trouvé pour votre recherche.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination en bas de page -->
        <div class="bottom-pagination mb-4">
            <?php echo enhanced_pagination($total_results, $results_per_page, $current_page, $pagination_url, $show_all); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Script pour l'animation de chargement -->
<script>
function showLoading() {
    document.getElementById('searchFormContainer').style.display = 'none';
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('loadingSpinner').style.display = 'block';
    return true;
}

// Cacher l'animation de chargement si la page est chargée avec des résultats
document.addEventListener('DOMContentLoaded', function() {
    var searchResults = document.getElementById('searchResults');
    var loadingSpinner = document.getElementById('loadingSpinner');
    
    if (searchResults && searchResults.querySelector('.card')) {
        loadingSpinner.style.display = 'none';
        searchResults.style.display = 'block';
    }
});
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>