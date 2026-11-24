<!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/head.php'; ?>
</head>
<body class="public">
<a class="skip" href="#content">Skip to content</a>
<header class="site-header">
    <div class="container-fluid px-3 px-lg-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
            <a class="wordmark" href="index.php"><span class="mark" aria-hidden="true"></span>FixFlow</a>
            <nav class="d-flex flex-wrap justify-content-end align-items-center gap-3" aria-label="Public">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a class="text-link" href="dashboard.php">Overview</a>
                <?php else: ?>
                    <a class="text-link" href="login.php">Sign in</a>
                    <a class="ff-btn" href="register.php">Create account</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
<main id="content">
