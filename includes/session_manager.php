<?php
// Gestion des sessions pour tout le site
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour vérifier si l'utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fonction pour obtenir le nom d'utilisateur connecté
function get_logged_username() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}

// Fonction pour obtenir le niveau d'accès de l'utilisateur
function get_user_access_level() {
    return isset($_SESSION['acces']) ? $_SESSION['acces'] : 0;
}

// Fonction pour déconnecter l'utilisateur
function logout_user() {
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Détruire la session
    session_destroy();
    
    // Rediriger vers la page d'accueil
    header("Location: index.php");
    exit;
}
?>