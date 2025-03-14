<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';

// Définir les variables
$message = '';
$status = '';
$username = '';
$new_password = '';

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Si on cherche un utilisateur
    if (isset($_POST['search_username'])) {
        $username = trim($_POST['username']);
        
        if (!empty($username)) {
            try {
                // Vérifier si l'utilisateur existe
                $stmt = $db->prepare("SELECT id, pseudo FROM rf_membres WHERE pseudo = :username");
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch();
                    $message = "Utilisateur trouvé: " . htmlspecialchars($user->pseudo);
                    $status = 'success';
                } else {
                    $message = "Aucun utilisateur trouvé avec ce nom.";
                    $status = 'danger';
                }
            } catch (PDOException $e) {
                $message = "Erreur de base de données: " . $e->getMessage();
                $status = 'danger';
            }
        } else {
            $message = "Veuillez entrer un nom d'utilisateur.";
            $status = 'warning';
        }
    }
    
    // Si on réinitialise le mot de passe
    if (isset($_POST['reset_password'])) {
        $username = trim($_POST['username']);
        $new_password = trim($_POST['new_password']);
        
        if (!empty($username) && !empty($new_password)) {
            try {
                // Vérifier d'abord si l'utilisateur existe
                $check_stmt = $db->prepare("SELECT id FROM rf_membres WHERE pseudo = :username");
                $check_stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    $user = $check_stmt->fetch();
                    
                    // Hasher le nouveau mot de passe avec MD5 (comme dans la base de données)
                    $hashed_password = md5($new_password);
                    
                    // Mettre à jour le mot de passe
                    $update_stmt = $db->prepare("UPDATE rf_membres SET pass = :password WHERE id = :id");
                    $update_stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                    $update_stmt->bindParam(':id', $user->id, PDO::PARAM_INT);
                    $update_stmt->execute();
                    
                    $message = "Le mot de passe a été réinitialisé avec succès!";
                    $status = 'success';
                } else {
                    $message = "Aucun utilisateur trouvé avec ce nom.";
                    $status = 'danger';
                }
            } catch (PDOException $e) {
                $message = "Erreur de base de données: " . $e->getMessage();
                $status = 'danger';
            }
        } else {
            $message = "Veuillez remplir tous les champs.";
            $status = 'warning';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="public/img/favicon.ico" type="image/x-icon">
    <title>Réinitialisation de mot de passe - Archives du Forum</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSS personnalisé -->
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="text-white mb-4">
        <div class="container py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h1 class="h3 mb-2 mb-md-0"><a href="index.php" class="text-white text-decoration-none">Archives du Forum</a></h1>
            </div>
        </div>
    </header>
    
    <main class="container mb-4">
        <h2 class="mb-3">Réinitialisation de mot de passe</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $status; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title h5 mb-0">Étape 1: Trouver votre compte</h3>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <button type="submit" name="search_username" class="btn btn-primary">Rechercher</button>
                </form>
            </div>
        </div>
        
        <?php if ($status === 'success' && !isset($_POST['reset_password'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title h5 mb-0">Étape 2: Créer un nouveau mot de passe</h3>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <button type="submit" name="reset_password" class="btn btn-primary">Réinitialiser le mot de passe</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($status === 'success' && isset($_POST['reset_password'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3 class="card-title h5 mb-0">Réinitialisation réussie</h3>
            </div>
            <div class="card-body">
                <p>Votre mot de passe a été réinitialisé avec succès.</p>
                <a href="login.php" class="btn btn-primary">Se connecter maintenant</a>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <footer class="bg-light py-3 mt-auto">
        <div class="container">
            <p class="text-center text-muted mb-0">Archives du Forum &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
