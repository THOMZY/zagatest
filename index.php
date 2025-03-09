<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';
require_once 'includes/functions.php';

// Définir le titre de la page
$page_title = "Accueil";

// Récupérer toutes les catégories avec leurs forums (version adaptée à votre structure)
$query = $db->query("
    SELECT 
        c.id AS category_id, 
        c.nom AS category_name,
        f.id AS forum_id,
        f.nom AS forum_name,
        f.nbTopics AS topic_count,
        f.nbMess AS message_count,
        f.lastMess AS last_post_date
    FROM 
        rf_categories c
    LEFT JOIN 
        rf_forums f ON f.idCat = c.id
    ORDER BY 
        c.id, f.id
");

// Organiser les résultats par catégorie
$categories = [];
while ($row = $query->fetch()) {
    if (!isset($categories[$row->category_id])) {
        $categories[$row->category_id] = [
            'id' => $row->category_id,
            'name' => $row->category_name,
            'forums' => []
        ];
    }
    
    // Ajouter le forum à la catégorie seulement s'il existe
    if ($row->forum_id) {
        $categories[$row->category_id]['forums'][] = [
            'id' => $row->forum_id,
            'name' => $row->forum_name,
            'description' => '', // Pas de description dans votre structure
            'topic_count' => $row->topic_count,
            'message_count' => $row->message_count,
            'last_post_date' => $row->last_post_date
        ];
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- Page d'accueil -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <h4 class="alert-heading">Bienvenue dans les archives du forum !</h4>
            <p>Ces archives sont en lecture seule. Vous pouvez parcourir tous les contenus existants, mais il n'est pas possible de créer de nouveaux messages.</p>
        </div>
    </div>
</div>

<?php foreach ($categories as $category): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title h5 mb-0"><?php echo secure_output($category['name']); ?></h3>
        </div>
        
        <?php if (count($category['forums']) > 0): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($category['forums'] as $forum): ?>
                    <a href="forum.php?id=<?php echo $forum['id']; ?>" class="list-group-item list-group-item-action forum-card">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h4 class="h6 mb-1"><?php echo secure_output($forum['name']); ?></h4>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="small">
                                    <div><?php echo $forum['topic_count']; ?> sujets</div>
                                    <div><?php echo $forum['message_count']; ?> messages</div>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <?php if ($forum['last_post_date']): ?>
                                    <small class="text-muted" data-bs-toggle="tooltip" title="Dernier message">
                                        <?php echo format_date($forum['last_post_date']); ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">Aucun message</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card-body">
                <p class="text-muted mb-0">Aucun forum dans cette catégorie.</p>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>