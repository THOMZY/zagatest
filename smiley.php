<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';
require_once 'includes/functions.php';

// Définir le titre de la page
$page_title = "Liste des Smileys";

// Chemin du dossier des smileys
$smileys_dir = 'public/img/smileys';

// Fonction récursive pour trouver toutes les images
function findImages($dir) {
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Récursivement scanner les sous-répertoires
            $result = array_merge($result, findImages($path));
        } elseif (is_file($path) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['gif', 'png', 'jpg', 'jpeg'])) {
            // Ajouter l'image au résultat
            $result[] = [
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'path' => $path
            ];
        }
    }
    
    return $result;
}

// Trouver toutes les images
$smileys = findImages($smileys_dir);

// Trier par nom
usort($smileys, function($a, $b) {
    return strnatcasecmp($a['name'], $b['name']);
});

// Charger l'en-tête du site
include 'includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title h5 mb-0">Liste des Smileys Disponibles</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <p class="mb-0">Pour utiliser un smiley dans vos messages, entrez son code entre deux points. Exemple: <code>:nom_du_smiley:</code></p>
        </div>
        
        <div class="row">
            <?php foreach ($smileys as $smiley): ?>
                <div class="col-6 col-md-3 col-lg-2 mb-3">
                    <div class="smiley-box">
                        <img src="<?php echo htmlspecialchars($smiley['path']); ?>" alt="<?php echo htmlspecialchars($smiley['name']); ?>">
                        <div class="smiley-code">
                            :<?php echo htmlspecialchars($smiley['name']); ?>:
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.smiley-box {
    background-color: #2d2d2d;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.smiley-box img {
    max-width: 50px;
    max-height: 50px;
    display: block;
    margin-bottom: 8px;
}

.smiley-code {
    color: #90caf9;
    font-family: monospace;
    font-size: 0.85rem;
    word-break: break-all;
}
</style>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>