<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'ID du forum est spécifié
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$forum_id = (int)$_GET['id'];

// Récupérer les informations du forum (utilisation de requête directe pour éviter les problèmes)
$forum_id = (int)$forum_id; // Sécuriser l'ID du forum
$forum_query = $db->query("
    SELECT 
        f.id, 
        f.nom, 
        c.id AS category_id,
        c.nom AS category_name
    FROM 
        rf_forums f
    JOIN 
        rf_categories c ON f.idCat = c.id
    WHERE 
        f.id = $forum_id
");
$forum = $forum_query->fetch();

// Si le forum n'existe pas, rediriger vers la page d'accueil
if (!$forum) {
    header('Location: index.php');
    exit;
}

// Définir le titre de la page
$page_title = $forum->nom;

// Définir le fil d'Ariane
$breadcrumb = [
    "forum.php?id=" . $forum->category_id => $forum->category_name,
    "" => $forum->nom
];

// Pagination
$topics_per_page = 20;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $topics_per_page;

// Récupérer le nombre total de topics dans ce forum
$forum_id = (int)$forum_id; // Sécuriser l'ID du forum
$count_query = $db->query("SELECT COUNT(*) FROM rf_topics WHERE idForum = $forum_id AND visible = '1'");
$total_topics = $count_query->fetchColumn();

// Conversion des paramètres en entiers pour éviter les problèmes de syntaxe SQL
$forum_id = (int)$forum_id;
$offset = (int)$offset;
$topics_per_page = (int)$topics_per_page;

// Exécution de la requête avec les paramètres directement intégrés dans la requête SQL
$topics_query = $db->query("
    SELECT 
        t.id,
        t.titre,
        t.lastMsgTime,
        u.id AS user_id,
        u.pseudo AS user_name,
        t.nbMess - 1 AS reply_count,
        t.lastMsgTime AS last_post_date,
        last_u.id AS last_user_id,
        last_u.pseudo AS last_user_name
    FROM 
        rf_topics t
    JOIN 
        rf_membres u ON t.auteur = u.id
    LEFT JOIN 
        rf_messages m ON m.id = t.lastMsg
    LEFT JOIN 
        rf_membres last_u ON m.idPseudo = last_u.id
    WHERE 
        t.idForum = $forum_id 
        AND t.visible = '1'
    ORDER BY 
        t.epingle DESC, t.lastMsgTime DESC
    LIMIT $offset, $topics_per_page
");

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Liste des topics -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h3 class="card-title h5 mb-0">Sujets</h3>
        <span class="badge bg-light text-dark"><?php echo $total_topics; ?> sujets</span>
    </div>
    
    <?php if ($topics_query->rowCount() > 0): ?>
        <div class="list-group list-group-flush">
            <?php while ($topic = $topics_query->fetch()): ?>
                <a href="topic.php?id=<?php echo $topic->id; ?>" class="list-group-item list-group-item-action topic-card">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h4 class="h6 mb-1"><?php echo secure_output($topic->titre); ?></h4>
                            <p class="text-muted small mb-0">
                                Par <?php echo secure_output($topic->user_name); ?>
                            </p>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="small">
                                <?php echo $topic->reply_count; ?> réponses
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <small class="text-muted" data-bs-toggle="tooltip" title="Dernier message">
                                <?php echo format_date($topic->last_post_date); ?>
                                <?php if ($topic->last_user_name): ?>
                                    par <?php echo secure_output($topic->last_user_name); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card-body">
            <p class="text-muted mb-0">Aucun sujet dans ce forum.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php
$pagination = pagination($total_topics, $topics_per_page, $current_page, "forum.php?id=$forum_id&page=%d");
echo $pagination;
?>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>