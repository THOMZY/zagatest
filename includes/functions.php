<?php
/**
 * Formater une date en format lisible
 * 
 * @param int|string $date Timestamp Unix ou date au format MySQL
 * @return string La date formatée
 */
function format_date($date) {
    // Si c'est un timestamp (nombre entier ou chaîne numérique)
    if (is_numeric($date)) {
        $timestamp = (int)$date;
    } else {
        // Sinon, considérer que c'est une date MySQL
        $timestamp = strtotime($date);
    }
    
    return date('d/m/Y à H:i', $timestamp);
}

/**
 * Sécuriser l'affichage des données (contre les attaques XSS)
 * 
 * @param string $data Les données à sécuriser
 * @return string Les données sécurisées
 */
function secure_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Convertir les codes smileys (:nom:) en images
 * 
 * @param string $text Le texte avec les codes smileys
 * @return string Le texte avec les images des smileys
 */
function convert_smileys($text) {
    // Regex pour trouver tous les smileys au format :nom:
    preg_match_all('/:([a-zA-Z0-9_-]+):/', $text, $matches);
    
    if (!empty($matches[1])) {
        // Extensions d'images à vérifier
        $extensions = ['gif', 'png', 'jpg', 'jpeg', 'webp'];
        $smileys_dir = 'public/img/smileys/';
        
        foreach ($matches[1] as $index => $smiley_name) {
            $smiley_code = $matches[0][$index]; // Le code complet :nom:
            $smiley_found = false;
            
            // Vérifier chaque extension possible
            foreach ($extensions as $ext) {
                $smiley_path = $smileys_dir . $smiley_name . '.' . $ext;
                
                if (file_exists($smiley_path)) {
                    // Remplacer le code par l'image du smiley
                    $replacement = '<img src="' . $smiley_path . '" alt="' . $smiley_name . '" class="smiley">';
                    $text = str_replace($smiley_code, $replacement, $text);
                    $smiley_found = true;
                    break; // Sortir de la boucle dès qu'une image est trouvée
                }
            }
            
            // Option: journaliser les smileys manquants (décommenter si besoin)
            /*
            if (!$smiley_found) {
                file_put_contents(
                    'smileys_missing.log', 
                    date('Y-m-d H:i:s') . " - Smiley manquant: $smiley_name\n", 
                    FILE_APPEND
                );
            }
            */
        }
    }
    
    return $text;
}

/**
 * Traite récursivement les balises cacher imbriquées
 * 
 * @param string $text Le texte contenant les balises cacher
 * @return string Le texte avec les balises cacher converties en HTML
 */
function process_spoilers($text) {
    static $spoiler_count = 0;
    
    // Fonction pour traiter une balise cacher
    $callback = function($matches) use (&$spoiler_count) {
        $content = $matches[1];
        $spoiler_id = 'spoiler-' . $spoiler_count++;
        
        // Traiter récursivement le contenu (pour les balises imbriquées)
        $content = process_spoilers($content);
        
        return '<div class="spoiler-container">
                  <button class="spoiler-button" onclick="toggleSpoiler(\'' . $spoiler_id . '\')">▶ Afficher le contenu caché</button>
                  <div id="' . $spoiler_id . '" class="spoiler-content hidden">
                    ' . $content . '
                  </div>
                </div>';
    };
    
    // Trouve la balise cacher la plus externe et la remplace
    $pattern = '/\[cacher\](.*?)\[\/cacher\]/is';
    $text = preg_replace_callback($pattern, $callback, $text);
    
    // Vérifie s'il reste encore des balises cacher (si c'est le cas, il y avait des imbrications)
    if (strpos($text, '[cacher]') !== false && strpos($text, '[/cacher]') !== false) {
        // Appel récursif pour traiter les balises restantes
        $text = process_spoilers($text);
    }
    
    return $text;
}

/**
 * Convertir les codes BBCode en HTML
 * 
 * @param string $text Le texte avec les codes BBCode
 * @return string Le texte avec le BBCode converti en HTML
 */
function convert_bbcode($text) {
    // Traitement spécial pour le format de citation personnalisé [b]Citation de USER[/b] : [citer]texte[/citer]
    $pattern_custom_cite = '/\[b\]Citation de ([^[]+)\[\/b\] : \[citer\](.*?)\[\/citer\]/is';
    $replacement_custom_cite = '<div class="custom-citation"><div class="citation-author">Citation de <strong>$1</strong> :</div><div class="citation-content">$2</div></div>';
    $text = preg_replace($pattern_custom_cite, $replacement_custom_cite, $text);
    
    // Traitement récursif des balises cacher (spoilers) imbriquées
    $text = process_spoilers($text);
    
    // BBCode de formatage de texte
    $bbcode_patterns = [
        '/\[b\](.*?)\[\/b\]/is',                  // Gras
        '/\[i\](.*?)\[\/i\]/is',                  // Italique
        '/\[u\](.*?)\[\/u\]/is',                  // Souligné
        '/\[s\](.*?)\[\/s\]/is',                  // Barré
        '/\[size=([1-7])\](.*?)\[\/size\]/is',    // Taille de texte
        '/\[couleur=([#a-zA-Z0-9]+)\](.*?)\[\/couleur\]/is', // Couleur
        '/\[center\](.*?)\[\/center\]/is',        // Centré
        '/\[left\](.*?)\[\/left\]/is',            // Aligné à gauche
        '/\[right\](.*?)\[\/right\]/is',          // Aligné à droite
        '/\[quote\](.*?)\[\/quote\]/is',          // Citation simple
        '/\[quote=(.*?)\](.*?)\[\/quote\]/is',    // Citation avec auteur
        '/\[code\](.*?)\[\/code\]/is',            // Code
        '/\[url\](.*?)\[\/url\]/is',              // Lien URL simple
        '/\[url=(.*?)\](.*?)\[\/url\]/is',        // Lien URL avec texte
        '/\[img\](.*?)\[\/img\]/is',              // Image
        '/\[list\](.*?)\[\/list\]/is',            // Liste non ordonnée
        '/\[list=1\](.*?)\[\/list\]/is',          // Liste numérotée
        '/\[\*\](.*?)(?=\[\*\]|\[\/list\])/is',   // Élément de liste
        '/\[email\](.*?)\[\/email\]/is',          // Email
        '/\[email=(.*?)\](.*?)\[\/email\]/is',    // Email avec texte
        '/\[youtube\]([a-zA-Z0-9_-]{11})\[\/youtube\]/is', // Vidéo YouTube (ID de 11 caractères)
        '/\[citer\](.*?)\[\/citer\]/is',          // Citation simple sans auteur (pour les cas restants)
    ];
    
    $html_replacements = [
        '<strong>$1</strong>',                    // Gras
        '<em>$1</em>',                            // Italique
        '<span style="text-decoration: underline;">$1</span>', // Souligné
        '<span style="text-decoration: line-through;">$1</span>', // Barré
        '<span style="font-size: $1em;">$2</span>', // Taille de texte
        '<span style="color: $1;">$2</span>',     // Couleur
        '<div style="text-align: center;">$1</div>', // Centré
        '<div style="text-align: left;">$1</div>', // Aligné à gauche
        '<div style="text-align: right;">$1</div>', // Aligné à droite
        '<blockquote class="blockquote">$1</blockquote>', // Citation simple
        '<blockquote class="blockquote"><p>$2</p><footer class="blockquote-footer">$1</footer></blockquote>', // Citation avec auteur
        '<pre><code>$1</code></pre>',             // Code
        '<a href="$1" target="_blank">$1</a>',    // Lien URL simple
        '<a href="$1" target="_blank">$2</a>',    // Lien URL avec texte
        '<img src="$1" class="img-fluid" alt="Image" />', // Image
        '<ul>$1</ul>',                            // Liste non ordonnée
        '<ol>$1</ol>',                            // Liste numérotée
        '<li>$1</li>',                            // Élément de liste
        '<a href="mailto:$1">$1</a>',             // Email
        '<a href="mailto:$1">$2</a>',             // Email avec texte
        '<div class="ratio ratio-16x9 mb-3"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>', // YouTube embed
        '<div class="simple-citation">$1</div>',  // Citation simple sans auteur
    ];
    
    // Appliquer les remplacements
    $text = preg_replace($bbcode_patterns, $html_replacements, $text);
    
    // Gestion particulière pour les sauts de ligne à l'intérieur des listes
    $text = str_replace("\n[*]", "[*]", $text);
    
    return $text;
}

/**
 * Formater le texte d'un message pour l'affichage
 * (conversion des sauts de ligne, smileys, etc.)
 * 
 * @param string $text Le texte à formater
 * @return string Le texte formaté
 */
function format_message($text) {
    // Sécuriser le texte
    $text = secure_output($text);
    
    // Convertir le BBCode en HTML
    $text = convert_bbcode($text);
    
    // Convertir les smileys avant de traiter les sauts de ligne
    $text = convert_smileys($text);
    
    // Convertir les sauts de ligne en balises <br>
    $text = nl2br($text);
    
    return $text;
}

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