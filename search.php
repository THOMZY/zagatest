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
$results = [];
$total_results = 0;

// Si le terme de recherche vient du petit formulaire de l'en-tête, on utilise le type par défaut (titre)
if (isset($_GET['term']) && !isset($_GET['type'])) {
    $search_type = 'title';
}

// Pagination
$results_per_page = 20;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// Effectuer la recherche si un terme est fourni
if (!empty($search_term)) {
    // Sécuriser le terme de recherche pour les requêtes SQL
    $search_term_safe = '%' . $search_term . '%';
    
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
            GROUP BY 
                t.id
            ORDER BY 
                t.lastMsgTime DESC
            LIMIT :offset, :limit
        ");
        
        // Récupérer le nombre total de résultats pour la pagination
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
            GROUP BY 
                t.id
            ORDER BY 
                t.lastMsgTime DESC
            LIMIT :offset, :limit
        ");
        
        // Récupérer le nombre total de résultats pour la pagination
        $count_stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total
            FROM 
                rf_topics t
            WHERE 
                t.titre LIKE :search_term
                AND t.visible = '1'
        ");
    }
    
    // Exécuter la requête de comptage
    $count_stmt->bindParam(':search_term', $search_term_safe, PDO::PARAM_STR);
    $count_stmt->execute();
    $total_results = $count_stmt->fetchColumn();
    
    // Exécuter la requête principale
    $stmt->bindParam(':search_term', $search_term_safe, PDO::PARAM_STR);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $results_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title h5 mb-0">Recherche</h3>
    </div>
    <div class="card-body">
        <form action="search.php" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="term" class="form-control" placeholder="Terme de recherche" value="<?php echo secure_output($search_term); ?>" required>
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select">
                        <option value="title" <?php echo $search_type === 'title' ? 'selected' : ''; ?>>Rechercher dans les titres</option>
                        <option value="author" <?php echo $search_type === 'author' ? 'selected' : ''; ?>>Rechercher par auteur</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                </div>
            </div>
        </form>
        
        <?php if (!empty($search_term)): ?>
            <div class="alert alert-info">
                <?php if ($search_type === 'author'): ?>
                    Recherche des sujets créés par <strong><?php echo secure_output($search_term); ?></strong>
                <?php else: ?>
                    Recherche de "<strong><?php echo secure_output($search_term); ?></strong>" dans les titres
                <?php endif; ?>
                - <?php echo $total_results; ?> résultat(s) trouvé(s)
            </div>
            
            <?php if (count($results) > 0): ?>
                <div class="list-group">
                    <?php foreach ($results as $result): ?>
                        <a href="topic.php?id=<?php echo $result->id; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo secure_output($result->title); ?></h5>
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
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php 
                $pagination_url = "search.php?term=" . urlencode($search_term) . "&type=" . urlencode($search_type) . "&page=%d";
                echo pagination($total_results, $results_per_page, $current_page, $pagination_url); 
                ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    Aucun résultat trouvé pour votre recherche.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>