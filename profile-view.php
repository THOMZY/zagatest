<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si un nom d'utilisateur est spécifié
if (!isset($_GET['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_GET['username'];

// Récupérer les informations de l'utilisateur
try {
    $stmt = $db->prepare("
        SELECT 
            id, pseudo, time, lasttime, nbmess, 
            acces, presentation, signature, avatar, postheader
        FROM 
            rf_membres 
        WHERE 
            pseudo = :username
    ");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
    } else {
        // Rediriger si l'utilisateur n'existe pas
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données: " . $e->getMessage());
}

// Définir le titre de la page
$page_title = "Profil de " . secure_output($user->pseudo);

// Inclure l'en-tête
include 'includes/header.php';

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
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title h5 mb-0">Informations du membre</h3>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo get_avatar_path($user->avatar); ?>" alt="Avatar de <?php echo secure_output($user->pseudo); ?>" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                <h4 class="mb-0"><?php echo secure_output($user->pseudo); ?></h4>
                <p class="text-muted">Membre depuis <?php echo format_date($user->time); ?></p>
                <div class="d-flex justify-content-center">
                    <span class="badge bg-primary me-2">Messages: <?php echo $user->nbmess; ?></span>
                    <?php 
                    // Afficher le statut basé sur le niveau d'accès
                    if ($user->acces == 100): ?>
                        <span class="badge bg-danger">Administrateur</span>
                    <?php elseif ($user->acces > 11): ?>
                        <span class="badge bg-success">Modérateur</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Membre</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title h5 mb-0">Détails du profil</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Nom d'utilisateur:</div>
                    <div class="col-md-8"><?php echo secure_output($user->pseudo); ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Dernière connexion:</div>
                    <div class="col-md-8"><?php echo format_date($user->lasttime); ?></div>
                </div>
                
                <?php if (!empty($user->presentation)): ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Présentation:</div>
                    <div class="col-md-8"><?php echo format_message($user->presentation); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($user->signature)): ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Signature:</div>
                    <div class="col-md-8"><?php echo format_message($user->signature); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title h5 mb-0">Dernières activités</h3>
            </div>
            <div class="card-body">
                <?php
                // Récupérer les derniers messages de l'utilisateur
                try {
                    $messages_stmt = $db->prepare("
                        SELECT 
                            m.id, m.message, m.time, t.id as topic_id, t.titre as topic_title
                        FROM 
                            rf_messages m
                        JOIN 
                            rf_topics t ON m.idTopic = t.id
                        WHERE 
                            m.idPseudo = :user_id
                            AND m.visible = '1'
                        ORDER BY 
                            m.time DESC
                        LIMIT 5
                    ");
                    $messages_stmt->bindParam(':user_id', $user->id, PDO::PARAM_INT);
                    $messages_stmt->execute();
                    
                    if ($messages_stmt->rowCount() > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php while ($message = $messages_stmt->fetch()): ?>
                                <li class="list-group-item bg-dark">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <a href="topic.php?id=<?php echo $message->topic_id; ?>#message-<?php echo $message->id; ?>" class="text-decoration-none text-primary">
                                            <?php echo secure_output($message->topic_title); ?>
                                        </a>
                                        <small class="text-muted"><?php echo format_date($message->time); ?></small>
                                    </div>
                                    <a href="topic.php?id=<?php echo $message->topic_id; ?>#message-<?php echo $message->id; ?>" class="text-decoration-none">
                                        <div class="message-preview text-white">
                                            <?php 
                                            $preview = substr(strip_tags($message->message), 0, 150);
                                            echo $preview . (strlen($message->message) > 150 ? '...' : '');
                                            ?>
                                        </div>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">Aucun message récent.</p>
                    <?php endif;
                    
                } catch (PDOException $e) {
                    echo "<p class='text-danger'>Erreur lors de la récupération des messages: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?> 