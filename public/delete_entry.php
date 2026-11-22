<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: admin.php");
    exit;
}

$stmt = $pdo->prepare(
    "DELETE FROM resume_entries
     WHERE id = :id AND user_id = :user_id"
);

$stmt->execute([
    'id' => $id,
    'user_id' => $_SESSION['user_id']
]);

header("Location: admin.php");
exit;
