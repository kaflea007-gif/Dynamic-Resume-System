<?php
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC LIMIT 1");
$user = $stmt->fetch();

if (!$user) {
    die("No resume profile found. Please create an admin account first.");
}

$stmt = $pdo->prepare(
    "SELECT * FROM resume_entries
     WHERE user_id = :user_id
     ORDER BY start_date DESC"
);
$stmt->execute(['user_id' => $user['id']]);
$entries = $stmt->fetchAll();

$groups = [
    'experience' => 'Experience',
    'education' => 'Education',
    'project' => 'Projects'
];

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($user['full_name']) ?> | Resume</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="container">
        <header class="profile">
            <?php if (!empty($user['avatar_path']) && file_exists(__DIR__ . '/../' . $user['avatar_path'])): ?>
                <img class="avatar" src="../<?= e($user['avatar_path']) ?>" alt="Profile photo">
            <?php else: ?>
                <div class="avatar placeholder">Profile</div>
            <?php endif; ?>

            <h1><?= e($user['full_name']) ?></h1>
            <h2><?= e($user['headline']) ?></h2>
            <p><?= nl2br(e($user['summary'])) ?></p>
            <p><?= e($user['email']) ?></p>
        </header>

        <?php foreach ($groups as $type => $heading): ?>
            <section>
                <h2><?= e($heading) ?></h2>

                <?php
                $found = false;
                foreach ($entries as $entry):
                    if ($entry['entry_type'] !== $type) continue;
                    $found = true;
                ?>
                    <article class="entry">
                        <h3><?= e($entry['title']) ?></h3>
                        <h4><?= e($entry['organization']) ?></h4>
                        <p class="date">
                            <?= e($entry['start_date']) ?> —
                            <?= $entry['end_date'] ? e($entry['end_date']) : 'Present' ?>
                        </p>
                        <p><?= nl2br(e($entry['description'])) ?></p>
                    </article>
                <?php endforeach; ?>

                <?php if (!$found): ?>
                    <p class="empty">No <?= strtolower(e($heading)) ?> added yet.</p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </main>
</body>
</html>
