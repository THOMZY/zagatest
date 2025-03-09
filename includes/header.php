<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? secure_output($page_title) . ' - ' : ''; ?>Archives du Forum</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSS personnalisé -->
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="bg-dark text-white mb-4">
        <div class="container py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><a href="index.php" class="text-white text-decoration-none">Archives du Forum</a></h1>
                <p class="mb-0">Forum en lecture seule</p>
            </div>
        </div>
    </header>
    
    <main class="container mb-4">
        <?php if (isset($page_title)): ?>
            <h2 class="mb-3"><?php echo secure_output($page_title); ?></h2>
        <?php endif; ?>
        
        <?php if (isset($breadcrumb)): ?>
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                    <?php foreach ($breadcrumb as $url => $label): ?>
                        <?php if ($url): ?>
                            <li class="breadcrumb-item"><a href="<?php echo $url; ?>"><?php echo secure_output($label); ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo secure_output($label); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
