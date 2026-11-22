<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

if (!verifyCsrf()) {
    setFlash('error', 'Invalid request token. Please try again.');
    header('Location: admin.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    setFlash('error', 'Invalid entry ID.');
    header('Location: admin.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM resume_entries WHERE id = :id AND user_id = :user_id');
$stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);

setFlash($stmt->rowCount() > 0 ? 'success' : 'error',
         $stmt->rowCount() > 0 ? 'Entry deleted.' : 'Entry not found.');
header('Location: admin.php');
exit;
