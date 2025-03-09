<?php
// Configuration de la base de données
$db_host = 'localhost';      // Adresse du serveur MySQL
$db_name = 'hime7899_forumdb';  // Nom de la base de données
$db_user = 'hime7899_thomzy';           // Nom d'utilisateur MySQL
$db_pass = 'Shinichi034!';               // Mot de passe MySQL

// Établir la connexion à la base de données
try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    // Configurer PDO pour qu'il génère des exceptions en cas d'erreur
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configurer PDO pour qu'il retourne les résultats sous forme d'objets
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch(PDOException $e) {
    // En cas d'erreur, afficher un message et arrêter le script
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
