
<?php

// Start a session if one is not already active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check whether a user is logged in.
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

// Protect pages that require authentication.
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}
