<?php
// Fichier a utiliser une fois puis supprimer.
// Configuration de la base de données (utilise le fichier existant)
require_once 'config/database.php';

// Définir le domaine de l'ancien site
$old_domain = 'ton-nouveau-domaine.com';
// Définir le domaine du nouveau site (remplace par ton domaine)
$new_domain = 'thomzy.com';

// Nombre maximum de lignes à traiter en une fois (pour éviter les timeouts)
$batch_size = 1000;
$total_updated = 0;

// Journal pour suivre les modifications
$log_file = 'url_update_log_' . date('Y-m-d_H-i-s') . '.txt';
$log_handle = fopen($log_file, 'w');

// Fonction pour écrire dans le journal
function log_message($message) {
    global $log_handle;
    echo $message . "\n";
    fwrite($log_handle, $message . "\n");
}

// Fonction pour trouver la clé primaire d'une table
function get_primary_key($db, $table) {
    try {
        // Méthode 1: Chercher la clé primaire
        $query = $db->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['Column_name'])) {
            return $result['Column_name'];
        }
        
        // Méthode 2: Chercher une colonne nommée 'id' ou similaire
        $columns = $db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            if (strtolower($col['Field']) == 'id' || 
                strtolower($col['Field']) == 'idm' || 
                strtolower($col['Field']) == 'idp') {
                return $col['Field'];
            }
        }
        
        // Si on a une colonne unique, on peut l'utiliser
        foreach ($columns as $col) {
            if ($col['Key'] == 'UNI') {
                return $col['Field'];
            }
        }
        
        // Dernière tentative: utiliser la première colonne
        if (!empty($columns)) {
            return $columns[0]['Field'];
        }
        
        return null;
    } catch (Exception $e) {
        log_message("Erreur lors de la recherche de la clé primaire: " . $e->getMessage());
        return null;
    }
}

// Fonction pour mettre à jour une colonne dans une table
function update_urls_in_column($db, $table, $column, $old_domain, $new_domain, $batch_size) {
    $updates = 0;
    
    try {
        // Vérifier si la table a une colonne ID ou équivalent
        $id_column = get_primary_key($db, $table);
        
        // Si aucune colonne ID ou équivalent, on ne peut pas traiter cette table
        if (!$id_column) {
            log_message("ATTENTION: Impossible de trouver une clé primaire dans $table, tentative de mise à jour sans ID");
            
            // On va essayer de mettre à jour sans utiliser de clé primaire
            $count_query = $db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` LIKE :pattern");
            $pattern = '%' . $old_domain . '%';
            $count_query->bindParam(':pattern', $pattern);
            $count_query->execute();
            $total_rows = $count_query->fetchColumn();
            
            if ($total_rows == 0) {
                log_message("Aucune occurrence trouvée dans $table.$column");
                return 0;
            }
            
            log_message("$total_rows lignes à traiter dans $table.$column");
            
            // Tentative de mise à jour directe
            $update_query = $db->prepare("UPDATE `$table` SET `$column` = REPLACE(`$column`, :old_domain, :new_domain) WHERE `$column` LIKE :pattern");
            $update_query->bindParam(':old_domain', $old_domain);
            $update_query->bindParam(':new_domain', $new_domain);
            $update_query->bindParam(':pattern', $pattern);
            $update_query->execute();
            
            $rows_updated = $update_query->rowCount();
            log_message("$rows_updated lignes mises à jour dans $table.$column (mise à jour globale)");
            return $rows_updated;
        }
        
        // Compter le nombre total de lignes contenant l'ancien domaine
        $count_query = $db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` LIKE :pattern");
        $pattern = '%' . $old_domain . '%';
        $count_query->bindParam(':pattern', $pattern);
        $count_query->execute();
        $total_rows = $count_query->fetchColumn();
        
        if ($total_rows == 0) {
            log_message("Aucune occurrence trouvée dans $table.$column");
            return 0;
        }
        
        log_message("$total_rows lignes à traiter dans $table.$column");
        
        // Debug: Afficher quelques échantillons pour voir comment les URLs apparaissent
        $sample_query = $db->prepare("SELECT `$id_column`, `$column` FROM `$table` WHERE `$column` LIKE :pattern LIMIT 3");
        $sample_query->bindParam(':pattern', $pattern);
        $sample_query->execute();
        
        log_message("=== ÉCHANTILLONS DE DONNÉES POUR DÉBUG ===");
        while ($row = $sample_query->fetch(PDO::FETCH_ASSOC)) {
            log_message("ID: " . $row[$id_column]);
            log_message("Contenu: " . substr($row[$column], 0, 200) . (strlen($row[$column]) > 200 ? '...' : ''));
        }
        log_message("=======================================");
        
        // Traiter par lots
        $offset = 0;
        while ($offset < $total_rows) {
            // Récupérer l'ID et la valeur de la colonne pour ce lot
            $select_query = $db->prepare("SELECT `$id_column`, `$column` FROM `$table` WHERE `$column` LIKE :pattern LIMIT :offset, :limit");
            $select_query->bindParam(':pattern', $pattern);
            $select_query->bindParam(':offset', $offset, PDO::PARAM_INT);
            $select_query->bindParam(':limit', $batch_size, PDO::PARAM_INT);
            $select_query->execute();
            
            $batch_updates = 0;
            
            // Traiter chaque ligne
            while ($row = $select_query->fetch(PDO::FETCH_ASSOC)) {
                $original_value = $row[$column];
                $new_value = $original_value;
                
                // Remplacer toutes les occurrences possibles du domaine
                
                // 1. URLs complètes (http://domain.com ou https://domain.com)
                $new_value = str_replace(
                    ['http://' . $old_domain, 'https://' . $old_domain],
                    'http://' . $new_domain,
                    $new_value
                );
                
                // 2. Format //domain.com
                $new_value = str_replace(
                    '//' . $old_domain,
                    '//' . $new_domain,
                    $new_value
                );
                
                // 3. Subdomains (forum.domain.com, www.domain.com, etc.)
                $pattern_subdomain = '/([a-z0-9\-]+)\.' . preg_quote($old_domain, '/') . '/i';
                $new_value = preg_replace(
                    $pattern_subdomain,
                    '$1.' . $new_domain,
                    $new_value
                );
                
                // 4. Domaine simple sans protocole, s'il n'est pas déjà remplacé
                $new_value = str_replace(
                    $old_domain,
                    $new_domain,
                    $new_value
                );
                
                // Si modifié, mettre à jour
                if ($new_value !== $original_value) {
                    $update_query = $db->prepare("UPDATE `$table` SET `$column` = :new_value WHERE `$id_column` = :id");
                    $update_query->bindParam(':new_value', $new_value);
                    $update_query->bindParam(':id', $row[$id_column]);
                    $update_query->execute();
                    
                    $batch_updates++;
                    $updates++;
                    
                    // Log les 5 premières modifications pour vérification
                    if ($updates <= 5) {
                        log_message("ID #{$row[$id_column]} mis à jour dans $table.$column");
                        log_message("  Avant: " . substr($original_value, 0, 100) . (strlen($original_value) > 100 ? '...' : ''));
                        log_message("  Après: " . substr($new_value, 0, 100) . (strlen($new_value) > 100 ? '...' : ''));
                    }
                }
            }
            
            log_message("$batch_updates lignes mises à jour dans ce lot ($table.$column)");
            $offset += $batch_size;
        }
        
        log_message("Total de $updates lignes mises à jour dans $table.$column");
        return $updates;
        
    } catch (PDOException $e) {
        log_message("Erreur avec $table.$column: " . $e->getMessage());
        return 0;
    }
}

// Démarrer le processus
log_message("Début de la mise à jour des URLs de $old_domain vers $new_domain");

try {
    // 1. Récupérer toutes les tables de la base de données
    $tables_query = $db->query("SHOW TABLES");
    $tables = $tables_query->fetchAll(PDO::FETCH_COLUMN);
    
    log_message("Nombre de tables à traiter: " . count($tables));
    
    foreach ($tables as $table) {
        log_message("\nTraitement de la table: $table");
        
        // 2. Récupérer toutes les colonnes de cette table
        $columns_query = $db->query("DESCRIBE `$table`");
        $columns = $columns_query->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            $column_name = $column['Field'];
            $column_type = $column['Type'];
            
            // Ne traiter que les colonnes de type texte
            if (preg_match('/(varchar|text|longtext)/i', $column_type)) {
                log_message("Vérification de la colonne: $column_name ($column_type)");
                $updates = update_urls_in_column($db, $table, $column_name, $old_domain, $new_domain, $batch_size);
                $total_updated += $updates;
            }
        }
    }
    
    log_message("\n===== RÉSUMÉ =====");
    log_message("Mise à jour terminée. Nombre total de champs mis à jour: $total_updated");
    log_message("Journal détaillé sauvegardé dans: $log_file");
    
} catch (PDOException $e) {
    log_message("Erreur: " . $e->getMessage());
}

// Fermer le journal
fclose($log_handle);
?>