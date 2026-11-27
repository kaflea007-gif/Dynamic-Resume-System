<?php
$pageTitle = $pageTitle ?? 'Dynamic Resume System';
$loggedIn  = function_exists('isLoggedIn') && isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">Dynamic Resume</a>
    <nav>
        <a href="index.php">Public CV</a>
        <?php if ($loggedIn): ?>
            <a href="admin.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Admin Login</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
