<?php
/**
 * Fonctions liées à la sécurité
 */

/**
 * Sécuriser l'affichage des données (contre les attaques XSS)
 * 
 * @param string $data Les données à sécuriser
 * @return string Les données sécurisées
 */
function secure_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}