<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . SITE_TITLE : SITE_TITLE ?></title>
    <meta name="description" content="<?= e($pageDescription ?? SITE_DESCRIPTION) ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($pageTitle ?? SITE_TITLE) ?>">
    <meta property="og:description" content="<?= e($pageDescription ?? SITE_DESCRIPTION) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= SITE_URL ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <?php if (file_exists(__DIR__ . '/../css/style.site.css')): ?>
    <link rel="stylesheet" href="/css/style.site.css">
    <?php endif; ?>
</head>
<body<?= isset($bodyClass) ? ' class="' . e($bodyClass) . '"' : '' ?>>
    <div class="header">
        <a href="/" id="logo"><?= SITE_TITLE ?></a>
        <nav class="nav">
            <a href="/">Home</a>
            <a href="/archives">Archive</a>
        </nav>
    </div>
    <main class="container">
        <div class="main">
