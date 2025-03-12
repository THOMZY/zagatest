<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';
require_once 'includes/functions.php';

// Définir le titre de la page
$page_title = "Trombinoscope";

// Chemin du dossier des images du trombinoscope
$trombi_dir = 'upload/trombi';

// Fonction pour trouver toutes les images
function findImages($dir) {
    $result = [];
    
    // Vérifier si le répertoire existe
    if (!is_dir($dir)) {
        return $result;
    }
    
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_file($path) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['gif', 'png', 'jpg', 'jpeg'])) {
            // Ajouter l'image au résultat
            $result[] = [
                'path' => $path
            ];
        }
    }
    
    return $result;
}

// Trouver toutes les images
$trombi_images = findImages($trombi_dir);

// Charger l'en-tête du site
include 'includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title h5 mb-0">Trombinoscope</h3>
    </div>
    <div class="card-body">
        <?php if (empty($trombi_images)): ?>
            <div class="alert alert-info">
                Aucune image n'est disponible dans le trombinoscope pour le moment.
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($trombi_images as $image): ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <div class="trombi-box">
                            <img src="<?php echo htmlspecialchars($image['path']); ?>" alt="Photo de membre">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.trombi-box {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    padding: 5px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    overflow: hidden;
}

.trombi-box img {
    width: 100%;
    height: auto;
    object-fit: cover;
    border-radius: 4px;
    transition: transform 0.3s ease;
}

.trombi-box img:hover {
    transform: scale(1.05);
}
</style>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>