<?php
/**
 * Fonctions liées à la pagination
 */

/**
 * Créer la pagination
 * 
 * @param int $total_items Nombre total d'éléments
 * @param int $items_per_page Nombre d'éléments par page
 * @param int $current_page Page actuelle
 * @param string $url_pattern URL de base pour les liens de pagination
 * @return string Le HTML de la pagination
 */
function pagination($total_items, $items_per_page, $current_page, $url_pattern) {
    $total_pages = ceil($total_items / $items_per_page);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Pagination"><ul class="pagination">';
    
    // Bouton "Précédent"
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page - 1) . '">Précédent</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Précédent</span></li>';
    }
    
    // Pages
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $start_page + 4);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a></li>';
        }
    }
    
    // Bouton "Suivant"
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page + 1) . '">Suivant</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Suivant</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Fonction pour créer la pagination améliorée avec bouton "Tout"
 * 
 * @param int $total_items Nombre total d'éléments
 * @param int $items_per_page Nombre d'éléments par page
 * @param int $current_page Page actuelle
 * @param string $url_pattern URL de base pour les liens de pagination
 * @param bool $show_all Si on affiche tous les éléments
 * @return string Le HTML de la pagination
 */
function enhanced_pagination($total_items, $items_per_page, $current_page, $url_pattern, $show_all = false) {
    $standard_per_page = 20; // Nombre d'items par page en mode normal
    $total_pages = ceil($total_items / ($show_all ? $standard_per_page : $items_per_page));
    
    if ($total_pages <= 1 && !$show_all) {
        return '';
    }
    
    $html = '<nav aria-label="Pagination"><ul class="pagination pagination-sm flex-wrap justify-content-center">';
    
    // Fonction sécurisée pour générer l'URL de pagination
    $getPageUrl = function($page) use ($url_pattern, $show_all) {
        // Si on est en mode "Tout" et qu'on veut générer une URL pour une page spécifique,
        // on doit s'assurer de retirer le paramètre "all=1"
        if ($show_all) {
            // Remplacer "all=1" par "page=X"
            return str_replace('all=1', 'page=' . $page, $url_pattern);
        } else {
            // Comportement normal
            return str_replace(['%%d', '%d'], $page, $url_pattern);
        }
    };
    
    // URL pour "Afficher tout"
    $all_url = str_replace(['page=%d', 'page=%%d'], 'all=1', $url_pattern);
    
    // Bouton "Précédent"
    if ($current_page > 1 && !$show_all) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $getPageUrl($current_page - 1) . '">Précédent</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Précédent</span></li>';
    }
    
    // Afficher un nombre limité de pages pour tenir sur une ligne
    // On limite à un maximum de 7 boutons de pages (3 avant, la page courante, 3 après)
    $max_page_buttons = 7;
    $half = floor($max_page_buttons / 2);
    
    $start_page = max(1, $current_page - $half);
    $end_page = min($total_pages, $start_page + $max_page_buttons - 1);
    
    // Ajuster si on est près de la fin
    if ($end_page - $start_page < $max_page_buttons - 1) {
        $start_page = max(1, $end_page - $max_page_buttons + 1);
    }
    
    // Afficher un ellipsis au début si nécessaire
    if ($start_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $getPageUrl(1) . '">1</a></li>';
        if ($start_page > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Afficher les pages
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page && !$show_all) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $getPageUrl($i) . '">' . $i . '</a></li>';
        }
    }
    
    // Afficher un ellipsis à la fin si nécessaire
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $getPageUrl($total_pages) . '">' . $total_pages . '</a></li>';
    }
    
    // Bouton "Suivant"
    if ($current_page < $total_pages && !$show_all) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $getPageUrl($current_page + 1) . '">Suivant</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Suivant</span></li>';
    }
    
    // Bouton "Tout"
    if ($show_all) {
        $html .= '<li class="page-item active"><span class="page-link">Tout</span></li>';
    } else {
        $html .= '<li class="page-item"><a class="page-link" href="' . $all_url . '">Tout</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}