<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? convert_smileys(secure_output($page_title)) . ' - ' : ''; ?>Archives du Forum</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome pour l'icône smiley -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- CSS personnalisé -->
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="text-white mb-4">
        <div class="container py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h1 class="h3 mb-2 mb-md-0"><a href="index.php" class="text-white text-decoration-none">Archives du Forum</a></h1>
                
                <!-- Barre d'outils avec bouton smiley et recherche -->
                <div class="d-flex align-items-center">
                    <!-- Bouton smiley -->
                    <a href="smiley.php" class="btn btn-outline-light btn-sm me-2" title="Liste des smileys">
                        <i class="far fa-smile"></i>
                    </a>
                    
                    <!-- Formulaire de recherche -->
                    <form action="search.php" method="GET" class="d-flex">
                        <input type="text" name="term" class="form-control form-control-sm me-2" placeholder="Rechercher..." aria-label="Rechercher">
                        <button type="submit" class="btn btn-outline-light btn-sm">Rechercher</button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container mb-4">
        <?php if (isset($page_title)): ?>
            <h2 class="mb-3"><?php echo convert_smileys(secure_output($page_title)); ?></h2>
        <?php endif; ?>
        
        <?php if (isset($breadcrumb)): ?>
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                    <?php foreach ($breadcrumb as $url => $label): ?>
                        <?php if ($url): ?>
                            <li class="breadcrumb-item"><a href="<?php echo $url; ?>"><?php echo convert_smileys(secure_output($label)); ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo convert_smileys(secure_output($label)); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>