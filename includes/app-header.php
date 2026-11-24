<?php
require_login();
$user = current_user();
$activeNav = isset($activeNav) ? $activeNav : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/head.php'; ?>
</head>
<body class="app-body">
<a class="skip" href="#content">Skip to content</a>
<div class="shell">
    <aside class="rail">
        <a class="wordmark light" href="dashboard.php"><span class="mark" aria-hidden="true"></span>FixFlow</a>
        <p class="rail-uni">Thal University Bhakkar</p>
        <?php render_nav($user, $activeNav); ?>
        <?php render_user_block($user); ?>
    </aside>
    <div class="offcanvas offcanvas-start rail-mobile" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
        <div class="offcanvas-header">
            <span class="wordmark light" id="mobileNavLabel"><span class="mark" aria-hidden="true"></span>FixFlow</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?php render_nav($user, $activeNav); ?>
            <?php render_user_block($user); ?>
        </div>
    </div>
    <div class="main">
        <div class="main-content" id="content">
            <button class="ff-btn secondary nav-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav"><?= icon('menu') ?> Menu</button>
            <?php if (!empty($backHref)): ?>
                <p class="back"><a class="text-link" href="<?= e($backHref) ?>"><?= e($backLabel) ?></a></p>
            <?php endif; ?>
            <h1><?= e($pageTitle) ?></h1>
            <?php if (!empty($pageIntro)): ?>
                <p class="intro"><?= e($pageIntro) ?></p>
            <?php endif; ?>
            <?php render_flash(); ?>
