<?php
/**
 * Fonctions liées au formatage du contenu
 */

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
 * Convertir les URLs brutes en liens cliquables
 * 
 * @param string $text Le texte contenant des URLs
 * @return string Le texte avec les URLs converties en liens
 */
function convert_urls($text) {
    // Regex pour trouver les URLs, en excluant celles qui font déjà partie d'un lien ou d'une balise img
    $pattern = '~(?<!href=[\'"])(?<!src=[\'"])(https?://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(?:/[^\s<]*)?|www\.[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(?:/[^\s<]*)?)~i';
    
    // Remplacer les URLs par des liens ou des images
    return preg_replace_callback($pattern, function($matches) {
        $url = $matches[0];
        
        // Ajouter http:// si l'URL ne commence pas par http
        if (strpos($url, 'http') !== 0) {
            $full_url = 'http://' . $url;
        } else {
            $full_url = $url;
        }
        
        // Vérifier si c'est une URL d'image
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $path_info = pathinfo(strtolower($url));
        
        // Si c'est une image, retourner une balise img
        if (isset($path_info['extension']) && in_array($path_info['extension'], $image_extensions)) {
            return '<img src="' . $full_url . '" class="img-fluid" alt="Image">';
        }
        
        // Sinon, retourner un lien
        return '<a href="' . $full_url . '" target="_blank">' . $url . '</a>';
    }, $text);
}

/**
 * Convertir tous types d'URLs en liens ou images
 * 
 * @param string $text Le texte contenant des URLs
 * @return string Le texte avec les URLs converties
 */
function convert_all_urls($text) {
    // Regex pour trouver les URLs, en excluant celles qui font déjà partie d'une balise a ou img
    $pattern = '~(?<!href=[\'"])(?<!src=[\'"])(https?://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(?:/[^\s<]*)?|www\.[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(?:/[^\s<]*)?)~i';
    
    // Remplacer les URLs par des liens ou des images
    return preg_replace_callback($pattern, function($matches) {
        $url = $matches[0];
        
        // Ajouter http:// si l'URL ne commence pas par http
        if (strpos($url, 'http') !== 0) {
            $full_url = 'http://' . $url;
        } else {
            $full_url = $url;
        }
        
        // Vérifier si c'est une URL d'image
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $path_info = pathinfo(strtolower($url));
        
        // Si c'est une image, retourner une balise img
        if (isset($path_info['extension']) && in_array($path_info['extension'], $image_extensions)) {
            return '<img src="' . $full_url . '" class="img-fluid" alt="Image">';
        }
        
        // Sinon, retourner un lien
        return '<a href="' . $full_url . '" target="_blank">' . $url . '</a>';
    }, $text);
}

/**
 * Formater un message pour l'affichage (combine sécurité, BBCode, smileys, etc.)
 * 
 * @param string $text Le texte brut
 * @return string Le texte formaté pour l'affichage
 */
function format_message($text) {
    // Sécuriser le texte d'abord mais en préservant les balises BBCode
    $text = str_replace(['<', '>'], ['&lt;', '&gt;'], $text);
    
    // Convertir le BBCode en HTML
    $text = process_nested_bbcode($text);
    
    // Convertir les smileys
    $text = convert_smileys($text);
    
    // Convertir toutes les URLs (qui ne sont pas dans des balises)
    $text = convert_all_urls($text);
    
    // Convertir les sauts de ligne en balises <br>
    $text = nl2br($text);
    
    return $text;
}