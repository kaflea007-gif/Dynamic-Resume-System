<?php
session_start();

// clear session data
$_SESSION = [];

// delete the cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// end session
session_destroy();
header('Location: login.php');
exit;
