<?php
/**
 * Fonctions liées au traitement du BBCode
 */

/**
 * Fonction récursive pour traiter correctement les balises BBCode imbriquées
 * 
 * @param string $text Le texte avec les balises BBCode
 * @return string Le texte avec les balises converties en HTML
 */
function process_nested_bbcode($text) {
    static $spoiler_count = 0;
    
    // Augmenter les limites de regex pour PHP
    ini_set('pcre.backtrack_limit', 10000000);
    ini_set('pcre.recursion_limit', 10000);
    
    // Traiter d'abord les balises [img] pour éviter les conflits
    while (preg_match('/\[img\](.*?)\[\/img\]/is', $text, $matches)) {
        $url = $matches[1];
        $original = $matches[0];
        
        if (strpos($url, 'http') !== 0 && strpos($url, 'www.') === 0) {
            $url = 'http://' . $url;
        }
        
        $replacement = '<img src="' . $url . '" class="img-fluid" alt="Image">';
        $text = str_replace($original, $replacement, $text);
    }
    
    // Traiter les citations avec auteur de haut en bas
    $pattern_with_author = '/\[b\]Citation de ([^[]+)\[\/b\] : \[citer\]((?:[^\[]|\[(?!b\]Citation|\/?citer\])|(?R))*)\[\/citer\]/is';
    while (preg_match($pattern_with_author, $text, $matches)) {
        $author = $matches[1];
        $content = $matches[2];
        $original = $matches[0];
        
        // Traiter le contenu de la citation
        $processed_content = process_nested_bbcode($content);
        
        // Créer le remplacement
        $replacement = '<div class="quote-box with-author"><div class="quote-header"><i class="fas fa-quote-left"></i> Citation de <strong>' . htmlspecialchars($author) . '</strong></div><div class="quote-content">' . $processed_content . '</div></div>';
        
        // Remplacer la citation dans le texte
        $text = str_replace($original, $replacement, $text);
    }
    
    // Traiter les citations simples de haut en bas
    $pattern_simple = '/\[citer\]((?:[^\[]|\[(?!citer\])|(?R))*)\[\/citer\]/is';
    while (preg_match($pattern_simple, $text, $matches)) {
        $content = $matches[1];
        $original = $matches[0];
        
        // Vérifier si c'est une citation simple (pas une partie d'une citation avec auteur)
        if (strpos($original, '[b]Citation de') === false) {
            // Traiter le contenu de la citation
            $processed_content = process_nested_bbcode($content);
            
            // Créer le remplacement
            $replacement = '<div class="quote-box simple-quote"><div class="quote-content">' . $processed_content . '</div></div>';
            
            // Remplacer la citation dans le texte
            $text = str_replace($original, $replacement, $text);
        }
    }
    
    // Traiter les balises de formatage après les citations
    $formatting_patterns = [
        'center' => '/\[center\](.*?)\[\/center\]/is',
        'left' => '/\[left\](.*?)\[\/left\]/is',
        'right' => '/\[right\](.*?)\[\/right\]/is',
        'couleur' => '/\[couleur=([#a-zA-Z0-9]+)\](.*?)\[\/couleur\]/is',
        'color' => '/\[color=([#a-zA-Z0-9]+)\](.*?)\[\/color\]/is',
        'b' => '/\[b\](.*?)\[\/b\]/is',
        'i' => '/\[i\](.*?)\[\/i\]/is',
        'u' => '/\[u\](.*?)\[\/u\]/is',
        's' => '/\[s\](.*?)\[\/s\]/is',
        'size' => '/\[size=([1-7])\](.*?)\[\/size\]/is'
    ];
    
    foreach ($formatting_patterns as $tag => $pattern) {
        if (preg_match($pattern, $text)) {
            $text = preg_replace_callback($pattern, function($matches) use ($tag) {
                $content = process_nested_bbcode($matches[count($matches) - 1]);
                switch ($tag) {
                    case 'center':
                        return '<div style="text-align: center;">' . $content . '</div>';
                    case 'left':
                        return '<div style="text-align: left;">' . $content . '</div>';
                    case 'right':
                        return '<div style="text-align: right;">' . $content . '</div>';
                    case 'couleur':
                    case 'color':
                        return '<span style="color: ' . $matches[1] . ';">' . $content . '</span>';
                    case 'b':
                        return '<strong>' . $content . '</strong>';
                    case 'i':
                        return '<em>' . $content . '</em>';
                    case 'u':
                        return '<span style="text-decoration: underline;">' . $content . '</span>';
                    case 's':
                        return '<span style="text-decoration: line-through;">' . $content . '</span>';
                    case 'size':
                        return '<span style="font-size: ' . $matches[1] . 'em;">' . $content . '</span>';
                }
            }, $text);
        }
    }
    
    // Balise [youtube]
    $youtube_pattern = '/\[youtube\]([a-zA-Z0-9_-]{11})\[\/youtube\]/is';
    if (preg_match($youtube_pattern, $text)) {
        $text = preg_replace_callback($youtube_pattern, function($matches) {
            return '<div class="ratio ratio-16x9 mb-3"><iframe src="https://www.youtube.com/embed/' . $matches[1] . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
        }, $text);
    }
    
    // Balise [cacher] (spoiler)
    while (preg_match('/\[cacher\](.*?)\[\/cacher\]/is', $text, $matches)) {
        $original = $matches[0]; // Le texte complet [cacher]...[/cacher]
        // Traiter récursivement le contenu pour gérer les balises imbriquées à l'intérieur
        $content = process_nested_bbcode($matches[1]);
        $spoiler_id = 'spoiler-' . $spoiler_count++;
        
        $replacement = '<div class="spoiler-container">
                  <button class="spoiler-button" onclick="toggleSpoiler(\'' . $spoiler_id . '\')">▶ Afficher le contenu caché</button>
                  <div id="' . $spoiler_id . '" class="spoiler-content hidden">' . $content . '</div>
                </div>';
        
        // Remplacer directement le texte original
        $text = str_replace($original, $replacement, $text);
    }
    
    // Balise [code]
    $code_pattern = '/\[code\](.*?)\[\/code\]/is';
    if (preg_match($code_pattern, $text)) {
        $text = preg_replace_callback($code_pattern, function($matches) {
            // Ne pas traiter le contenu du code
            return '<pre><code>' . $matches[1] . '</code></pre>';
        }, $text);
    }
    
    // Balise [url] avec texte - nouvelle approche simplifiée
    while (preg_match('/\[url=(.*?)\](.*?)\[\/url\]/is', $text, $matches)) {
        $url = $matches[1];
        $link_text = $matches[2];
        $original = $matches[0]; // Le texte complet [url=...]...[/url]
        
        // Traiter récursivement le contenu
        $link_text = process_nested_bbcode($link_text);
        
        // S'assurer que l'URL a le bon format
        if (strpos($url, 'http') !== 0 && strpos($url, 'www.') === 0) {
            $url = 'http://' . $url;
        }
        
        $replacement = '<a href="' . $url . '" target="_blank">' . $link_text . '</a>';
        
        // Remplacer directement le texte original par le remplacement
        $text = str_replace($original, $replacement, $text);
    }

    // Balise [url] simple - nouvelle approche simplifiée
    while (preg_match('/\[url\](.*?)\[\/url\]/is', $text, $matches)) {
        $url = $matches[1];
        $original = $matches[0]; // Le texte complet [url]...[/url]
        
        // S'assurer que l'URL a le bon format
        if (strpos($url, 'http') !== 0 && strpos($url, 'www.') === 0) {
            $url = 'http://' . $url;
        }
        
        $replacement = '<a href="' . $url . '" target="_blank">' . $url . '</a>';
        
        // Remplacer directement
        $text = str_replace($original, $replacement, $text);
    }
    
    // Balise [email] avec texte
    $email_text_pattern = '/\[email=(.*?)\](.*?)\[\/email\]/is';
    if (preg_match($email_text_pattern, $text)) {
        $text = preg_replace_callback($email_text_pattern, function($matches) {
            $content = process_nested_bbcode($matches[2]); // Traiter récursivement
            return '<a href="mailto:' . $matches[1] . '">' . $content . '</a>';
        }, $text);
    }
    
    // Balise [email] simple
    $email_pattern = '/\[email\](.*?)\[\/email\]/is';
    if (preg_match($email_pattern, $text)) {
        $text = preg_replace_callback($email_pattern, function($matches) {
            return '<a href="mailto:' . $matches[1] . '">' . $matches[1] . '</a>';
        }, $text);
    }
    
    // Balise [list] avec éléments
    $list_pattern = '/\[list\](.*?)\[\/list\]/is';
    if (preg_match($list_pattern, $text)) {
        $text = preg_replace_callback($list_pattern, function($matches) {
            $content = $matches[1];
            // Traiter les éléments de liste
            $content = preg_replace_callback('/\[\*\](.*?)(?=\[\*\]|\[\/list\]|$)/is', function($item_matches) {
                $item_content = process_nested_bbcode($item_matches[1]); // Traiter récursivement
                return '<li>' . $item_content . '</li>';
            }, $content);
            return '<ul>' . $content . '</ul>';
        }, $text);
    }
    
    // Balise [list=1] avec éléments
    $ordered_list_pattern = '/\[list=1\](.*?)\[\/list\]/is';
    if (preg_match($ordered_list_pattern, $text)) {
        $text = preg_replace_callback($ordered_list_pattern, function($matches) {
            $content = $matches[1];
            // Traiter les éléments de liste
            $content = preg_replace_callback('/\[\*\](.*?)(?=\[\*\]|\[\/list\]|$)/is', function($item_matches) {
                $item_content = process_nested_bbcode($item_matches[1]); // Traiter récursivement
                return '<li>' . $item_content . '</li>';
            }, $content);
            return '<ol>' . $content . '</ol>';
        }, $text);
    }
    
    return $text;
}

/**
 * Fonction principale pour convertir le BBCode en HTML
 * 
 * @param string $text Le texte avec les codes BBCode
 * @return string Le texte avec le BBCode converti en HTML
 */
function convert_bbcode($text) {
    // Traiter d'abord les balises [url] pour éviter les interférences
    
    // Balise [url] avec texte personnalisé
    $text = preg_replace_callback(
        '/\[url=(.*?)\](.*?)\[\/url\]/is',
        function($matches) {
            $url = $matches[1];
            $link_text = $matches[2];
            
            // S'assurer que l'URL a le bon format
            if (strpos($url, 'http') !== 0 && strpos($url, 'www.') === 0) {
                $url = 'http://' . $url;
            }
            
            return '<a href="' . $url . '" target="_blank">' . $link_text . '</a>';
        },
        $text
    );
    
    // Balise [url] simple
    $text = preg_replace_callback(
        '/\[url\](.*?)\[\/url\]/is',
        function($matches) {
            $url = $matches[1];
            
            // S'assurer que l'URL a le bon format
            if (strpos($url, 'http') !== 0 && strpos($url, 'www.') === 0) {
                $url = 'http://' . $url;
            }
            
            return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
        },
        $text
    );
    
    // Traiter les balises [img] avant le reste du BBCode
    $text = preg_replace_callback(
        '/\[img\](.*?)\[\/img\]/is',
        function($matches) {
            $url = $matches[1];
            if (strpos($url, 'http') !== 0 && strpos($url, 'www.') === 0) {
                $url = 'http://' . $url;
            }
            return '<span class="img-frame">' . $url . '</span>';
        },
        $text
    );
    
    // Utiliser la fonction récursive pour le reste des balises
    return process_nested_bbcode($text);
}