<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';
require_once 'includes/functions.php';

// Définir le titre de la page
$page_title = "Connexion Multi-Méthodes";

// Variables pour stocker les erreurs et les succès
$error_message = "";
$success_message = "";
$debug_info = "";

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Validation de base
    if (empty($username) || empty($password)) {
        $error_message = "Veuillez entrer votre nom d'utilisateur et votre mot de passe.";
    } else {
        // Sécuriser les entrées pour éviter les injections SQL
        $username = htmlspecialchars($username);
        
        // Générer différents hashs pour le mot de passe
        $hashes = [
            'md5' => md5($password),
            'sha1' => sha1($password),
            'sha1_trunc' => substr(sha1($password), 0, 32),
            'md5_username_pass' => md5($username . $password),
            'md5_pass_username' => md5($password . $username),
            'md5_with_salt' => md5($password . 'salt'),
            'md5_with_salt_before' => md5('salt' . $password),
            'md5_with_forum_salt' => md5($password . 'forum'),
            'md5_with_forum_salt_before' => md5('forum' . $password),
            'md5_with_rf_salt' => md5($password . 'rf_'),
            'md5_with_rf_salt_before' => md5('rf_' . $password),
            'double_md5' => md5(md5($password))
        ];
        
        // Ajouter le mode débogage pour voir les hashes générés
        if (isset($_POST['debug']) && $_POST['debug'] == 1) {
            $debug_info = "<h4>Hashes générés:</h4><ul>";
            foreach ($hashes as $type => $hash) {
                $debug_info .= "<li><strong>{$type}:</strong> {$hash}</li>";
            }
            $debug_info .= "</ul>";
        }
        
        try {
            // Nous allons d'abord récupérer le hash du mot de passe de l'utilisateur
            $stmt = $db->prepare("SELECT id, pseudo, pass, acces FROM rf_membres WHERE pseudo = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                $stored_hash = $user->pass;
                
                // Vérifier si l'un des hashes générés correspond au hash stocké
                $match_found = false;
                $method_used = "";
                
                foreach ($hashes as $method => $hash) {
                    if ($hash === $stored_hash) {
                        $match_found = true;
                        $method_used = $method;
                        break;
                    }
                }
                
                if ($match_found) {
                    // Démarrer la session si ce n'est pas déjà fait
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    // Enregistrer les informations de l'utilisateur dans la session
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['username'] = $user->pseudo;
                    $_SESSION['acces'] = $user->acces;
                    
                    // Message de succès
                    $success_message = "Connexion réussie avec la méthode: " . $method_used . " ! Redirection...";
                    
                    // Mettre à jour l'heure de la dernière connexion
                    $update_stmt = $db->prepare("
                        UPDATE rf_membres 
                        SET lasttime = :lasttime, lastip = :lastip 
                        WHERE id = :id
                    ");
                    
                    $currentTime = time();
                    $ip = ip2long($_SERVER['REMOTE_ADDR']);
                    
                    $update_stmt->bindParam(':lasttime', $currentTime, PDO::PARAM_INT);
                    $update_stmt->bindParam(':lastip', $ip, PDO::PARAM_INT);
                    $update_stmt->bindParam(':id', $user->id, PDO::PARAM_INT);
                    $update_stmt->execute();
                    
                    // Redirection vers la page d'accueil après 5 secondes
                    header("refresh:5;url=index.php");
                } else {
                    if (isset($_POST['debug']) && $_POST['debug'] == 1) {
                        $error_message = "Erreur d'authentification. Hash stocké dans la BDD: " . $stored_hash;
                    } else {
                        $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
                    }
                }
            } else {
                $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
            }
            
        } catch (PDOException $e) {
            $error_message = "Erreur de connexion à la base de données: " . $e->getMessage();
        }
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title h5 mb-0">Connexion avec Détection Multiple de Hachage</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($debug_info)): ?>
            <div class="alert alert-info">
                <?php echo $debug_info; ?>
            </div>
        <?php endif; ?>
        
        <p class="text-muted mb-4">Cette page essaie plusieurs méthodes de hachage pour vous authentifier. Utile si vous ne savez pas quelle méthode est utilisée.</p>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="debug" name="debug" value="1" <?php echo (isset($_POST['debug']) && $_POST['debug'] == 1) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="debug">Mode debug (afficher les hashs)</label>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary">Se connecter</button>
                <a href="reset_password.php" class="text-decoration-none">Mot de passe oublié?</a>
            </div>
        </form>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>