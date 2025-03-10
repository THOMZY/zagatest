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