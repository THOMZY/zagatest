<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers requis
require_once 'config/database.php';
require_once 'includes/functions.php';

// Définir le titre de la page
$page_title = "Test de mise en forme";

// Traiter le formulaire si soumis
$test_message = '';
$formatted_message = '';

if (isset($_POST['submit']) && !empty($_POST['test_message'])) {
    $test_message = $_POST['test_message'];
    $formatted_message = format_message($test_message);
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title h5 mb-0">Testeur de mise en forme</h3>
    </div>
    <div class="card-body">
        <p class="mb-3">Cette page vous permet de tester toutes les fonctions de mise en forme du texte disponibles sur le forum.</p>
        
        <!-- Boutons BBCode -->
        <div class="alert alert-info mb-4">
            <h4 class="alert-heading">Codes BBCode disponibles:</h4>
            <p class="mb-3">Cliquez sur un bouton pour insérer la balise à la position du curseur:</p>
            
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group btn-group-sm mb-2 me-2">
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[b]" data-end="[/b]" title="Gras">Gras</button>
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[i]" data-end="[/i]" title="Italique">Italique</button>
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[u]" data-end="[/u]" title="Souligné">Souligné</button>
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[s]" data-end="[/s]" title="Barré">Barré</button>
                    </div>
                    
                    <div class="btn-group btn-group-sm mb-2 me-2">
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[center]" data-end="[/center]" title="Centré">Centré</button>
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[left]" data-end="[/left]" title="Aligné à gauche">Gauche</button>
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[right]" data-end="[/right]" title="Aligné à droite">Droite</button>
                    </div>
                    
                    <div class="btn-group btn-group-sm mb-2 me-2">
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[size=3]" data-end="[/size]" title="Taille">Taille</button>
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[color=#FF0000]" data-end="[/color]" title="Couleur">Couleur</button>
                        <button type="button" class="btn btn-secondary bbcode-btn" data-start="[couleur=#FF0000]" data-end="[/couleur]" title="Couleur (FR)">Couleur FR</button>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group btn-group-sm mb-2 me-2">
                        <button type="button" class="btn btn-info bbcode-btn" data-start="[url=https://example.com]" data-end="[/url]" title="Lien">Lien</button>
                        <button type="button" class="btn btn-info bbcode-btn" data-start="[img]" data-end="[/img]" title="Image">Image</button>
                        <button type="button" class="btn btn-info bbcode-btn" data-start="[youtube]" data-end="[/youtube]" title="YouTube">YouTube</button>
                    </div>
                    
                    <div class="btn-group btn-group-sm mb-2 me-2">
                        <button type="button" class="btn btn-info bbcode-btn" data-start="[quote]" data-end="[/quote]" title="Citation">Citation</button>
                        <button type="button" class="btn btn-info bbcode-btn" data-start="[quote=Auteur]" data-end="[/quote]" title="Citation avec auteur">Citation avec auteur</button>
                        <button type="button" class="btn btn-info bbcode-btn" data-start="[citer]" data-end="[/citer]" title="Citation simple">Citation simple</button>
                        <button type="button" class="btn btn-info bbcode-btn" data-start="[b]Citation de Nom[/b] : [citer]" data-end="[/citer]" title="Citation personnalisée">Citation personnalisée</button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="btn-group btn-group-sm mb-2 me-2">
                        <button type="button" class="btn btn-warning bbcode-btn" data-start="[code]" data-end="[/code]" title="Code">Code</button>
                        <button type="button" class="btn btn-warning bbcode-btn" data-start="[cacher]" data-end="[/cacher]" title="Cacher (spoiler)">Cacher</button>
                        <button type="button" class="btn btn-warning bbcode-btn" data-start=":" data-end=":" title="Smiley">Smiley</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aperçu du message -->
        <?php if (!empty($formatted_message)): ?>
            <div class="message-card mb-4">
                <div class="message-outer-container">
                    <!-- Avatar factice -->
                    <div class="avatar-container">
                        <img src="public/img/avatars/default-avatar.png" alt="Avatar test" class="user-avatar-img">
                    </div>
                    
                    <!-- Header test -->
                    <div class="header-container">
                        <div class="header-content">
                            <div class="user-info-header">
                                <div class="username">Utilisateur Test</div>
                                <div class="user-details">
                                    Points: 100 | Messages: 100 | <?php echo format_date(time()); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenu du message -->
                    <div class="message-body">
                        <?php echo $formatted_message; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulaire de test -->
        <form method="post" action="">
            <div class="mb-3">
                <label for="test_message" class="form-label">Entrez votre texte avec des codes BBCode:</label>
                <textarea class="form-control" id="test_message" name="test_message" rows="10"><?php echo htmlspecialchars($test_message); ?></textarea>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Tester le formatage</button>
        </form>
    </div>
</div>

<?php
// JavaScript pour insérer les BBCodes à la position du curseur
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer les éléments
    const textarea = document.getElementById('test_message');
    const buttons = document.querySelectorAll('.bbcode-btn');
    
    // Ajouter un écouteur d'événement à chaque bouton
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Récupérer les balises de début et de fin
            const startTag = this.getAttribute('data-start');
            const endTag = this.getAttribute('data-end');
            
            // Insérer les balises à la position du curseur
            insertAtCursor(textarea, startTag, endTag);
        });
    });
    
    // Fonction pour insérer du texte à la position du curseur
    function insertAtCursor(textarea, startText, endText) {
        // Sauvegarder la position de défilement
        const scrollPos = textarea.scrollTop;
        
        // Récupérer les positions de début et de fin de la sélection
        const startPos = textarea.selectionStart;
        const endPos = textarea.selectionEnd;
        
        // Récupérer le texte sélectionné
        const selectedText = textarea.value.substring(startPos, endPos);
        
        // Nouveau texte à insérer
        const newText = startText + selectedText + endText;
        
        // Insérer le nouveau texte
        textarea.value = 
            textarea.value.substring(0, startPos) + 
            newText + 
            textarea.value.substring(endPos);
        
        // Repositionner le curseur après le texte inséré
        const newCursorPos = startPos + newText.length;
        textarea.setSelectionRange(newCursorPos, newCursorPos);
        
        // Restaurer la position de défilement
        textarea.scrollTop = scrollPos;
        
        // Donner le focus au textarea
        textarea.focus();
    }
});
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>