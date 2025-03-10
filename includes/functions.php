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
        $original = $matches[0]; // Le texte complet [img]...[/img]
        
        // S'assurer que l'URL a le bon format (ajouter http:// si nécessaire)
        if (strpos($url, 'http') !== 0 && strpos($url, 'www.') === 0) {
            $url = 'http://' . $url;
        }
        
        // Remplacer directement le texte original
        $text = str_replace($original, $replacement, $text);
    }
	
    // Balise [youtube]
     $youtube_pattern = '/\[youtube\]([a-zA-Z0-9_-]{11})\[\/youtube\]/is';
     if (preg_match($youtube_pattern, $text)) {
         $text = preg_replace_callback($youtube_pattern, function($matches) {
             '<div class="ratio ratio-16x9 mb-3"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>', // YouTube embed
         }, $text);
     }
    
    // Traiter ensuite le format de citation personnalisé
    $custom_cite_pattern = '/\[b\]Citation de ([^[]+)\[\/b\] : \[citer\](.*?)\[\/citer\]/is';
    if (preg_match($custom_cite_pattern, $text)) {
        $text = preg_replace_callback($custom_cite_pattern, function($matches) {
            $content = process_nested_bbcode($matches[2]); // Traiter récursivement
            return '<div class="custom-citation"><div class="citation-author">Citation de <strong>' . $matches[1] . '</strong> :</div><div class="citation-content">' . $content . '</div></div>';
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
    
    // Balise [citer] (citation)
    while (preg_match('/\[citer\](.*?)\[\/citer\]/is', $text, $matches)) {
        $original = $matches[0]; // Le texte complet [citer]...[/citer]
        $content = process_nested_bbcode($matches[1]); // Traiter récursivement
        $replacement = '<div class="simple-citation">' . $content . '</div>';
    
        // Remplacer directement le texte original
        $text = str_replace($original, $replacement, $text);
    }
    
    // Balise [center]
    $center_pattern = '/\[center\](.*?)\[\/center\]/is';
    if (preg_match($center_pattern, $text)) {
        $text = preg_replace_callback($center_pattern, function($matches) {
            $content = process_nested_bbcode($matches[1]); // Traiter récursivement
            return '<div style="text-align: center;">' . $content . '</div>';
        }, $text);
    }
    
    // Balise [left]
    $left_pattern = '/\[left\](.*?)\[\/left\]/is';
    if (preg_match($left_pattern, $text)) {
        $text = preg_replace_callback($left_pattern, function($matches) {
            $content = process_nested_bbcode($matches[1]); // Traiter récursivement
            return '<div style="text-align: left;">' . $content . '</div>';
        }, $text);
    }
    
    // Balise [right]
    $right_pattern = '/\[right\](.*?)\[\/right\]/is';
    if (preg_match($right_pattern, $text)) {
        $text = preg_replace_callback($right_pattern, function($matches) {
            $content = process_nested_bbcode($matches[1]); // Traiter récursivement
            return '<div style="text-align: right;">' . $content . '</div>';
        }, $text);
    }
    
    // Balise [couleur]
    $color_pattern = '/\[couleur=([#a-zA-Z0-9]+)\](.*?)\[\/couleur\]/is';
    if (preg_match($color_pattern, $text)) {
        $text = preg_replace_callback($color_pattern, function($matches) {
            $content = process_nested_bbcode($matches[2]); // Traiter récursivement
            return '<span style="color: ' . $matches[1] . ';">' . $content . '</span>';
        }, $text);
    }
    
    // Balise [color] (version anglaise)
    $color_en_pattern = '/\[color=([#a-zA-Z0-9]+)\](.*?)\[\/color\]/is';
    if (preg_match($color_en_pattern, $text)) {
        $text = preg_replace_callback($color_en_pattern, function($matches) {
            $content = process_nested_bbcode($matches[2]); // Traiter récursivement
            return '<span style="color: ' . $matches[1] . ';">' . $content . '</span>';
        }, $text);
    }
    
    // Balise [b]
    $bold_pattern = '/\[b\](.*?)\[\/b\]/is';
    if (preg_match($bold_pattern, $text)) {
        $text = preg_replace_callback($bold_pattern, function($matches) {
            $content = process_nested_bbcode($matches[1]); // Traiter récursivement
            return '<strong>' . $content . '</strong>';
        }, $text);
    }
    
    // Balise [i]
    $italic_pattern = '/\[i\](.*?)\[\/i\]/is';
    if (preg_match($italic_pattern, $text)) {
        $text = preg_replace_callback($italic_pattern, function($matches) {
            $content = process_nested_bbcode($matches[1]); // Traiter récursivement
            return '<em>' . $content . '</em>';
        }, $text);
    }
    
    // Balise [u]
    $underline_pattern = '/\[u\](.*?)\[\/u\]/is';
    if (preg_match($underline_pattern, $text)) {
        $text = preg_replace_callback($underline_pattern, function($matches) {
            $content = process_nested_bbcode($matches[1]); // Traiter récursivement
            return '<span style="text-decoration: underline;">' . $content . '</span>';
        }, $text);
    }
    
    // Balise [s]
    $strike_pattern = '/\[s\](.*?)\[\/s\]/is';
    if (preg_match($strike_pattern, $text)) {
        $text = preg_replace_callback($strike_pattern, function($matches) {
            $content = process_nested_bbcode($matches[1]); // Traiter récursivement
            return '<span style="text-decoration: line-through;">' . $content . '</span>';
        }, $text);
    }
    
    // Balise [size]
    $size_pattern = '/\[size=([1-7])\](.*?)\[\/size\]/is';
    if (preg_match($size_pattern, $text)) {
        $text = preg_replace_callback($size_pattern, function($matches) {
            $content = process_nested_bbcode($matches[2]); // Traiter récursivement
            return '<span style="font-size: ' . $matches[1] . 'em;">' . $content . '</span>';
        }, $text);
    }
    
    // Balise [code]
    $code_pattern = '/\[code\](.*?)\[\/code\]/is';
    if (preg_match($code_pattern, $text)) {
        $text = preg_replace_callback($code_pattern, function($matches) {
            // Ne pas traiter le contenu du code
            return '<pre><code>' . $matches[1] . '</code></pre>';
        }, $text);
    }
    
    // Balise [quote] avec auteur
    $quote_author_pattern = '/\[quote=(.*?)\](.*?)\[\/quote\]/is';
    if (preg_match($quote_author_pattern, $text)) {
        $text = preg_replace_callback($quote_author_pattern, function($matches) {
            $content = process_nested_bbcode($matches[2]); // Traiter récursivement
            return '<blockquote class="blockquote"><p>' . $content . '</p><footer class="blockquote-footer">' . $matches[1] . '</footer></blockquote>';
        }, $text);
    }
    
    // Balise [quote] simple
    $quote_pattern = '/\[quote\](.*?)\[\/quote\]/is';
    if (preg_match($quote_pattern, $text)) {
        $text = preg_replace_callback($quote_pattern, function($matches) {
            $content = process_nested_bbcode($matches[1]); // Traiter récursivement
            return '<blockquote class="blockquote">' . $content . '</blockquote>';
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

function format_message($text) {
    // Sécuriser le texte d'abord mais en préservant les balises BBCode
    $text = str_replace(['<', '>'], ['&lt;', '&gt;'], $text);
    
    // Utiliser directement process_nested_bbcode au lieu de convert_bbcode
    $text = process_nested_bbcode($text);
    
    // Convertir les smileys
    $text = convert_smileys($text);
    
    // Convertir toutes les URLs (qui ne sont pas dans des balises)
    $text = convert_all_urls($text);    
    
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