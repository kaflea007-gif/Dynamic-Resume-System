<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

// Profile owner (single-administrator portfolio).
$stmt = $pdo->query('SELECT * FROM users ORDER BY id ASC LIMIT 1');
$user = $stmt->fetch();

if (!$user) {
    die('No resume profile found. Please import resume_db.sql first.');
}

$stmt = $pdo->prepare(
    'SELECT * FROM resume_entries WHERE user_id = :user_id ORDER BY start_date DESC'
);
$stmt->execute(['user_id' => $user['id']]);
$entries = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT skill_name FROM skills WHERE user_id = :user_id ORDER BY skill_name ASC');
$stmt->execute(['user_id' => $user['id']]);
$skills = $stmt->fetchAll();

$groups = [
    'experience' => 'Experience',
    'education'  => 'Education',
    'project'    => 'Projects',
];

$pageTitle = $user['full_name'] . ' | Resume';
require_once __DIR__ . '/../includes/header.php';
?>

<header class="profile">
    <?php if (!empty($user['avatar_path']) && is_file(__DIR__ . '/../' . $user['avatar_path'])): ?>
        <img class="avatar" src="../<?= escape($user['avatar_path']) ?>" alt="Profile photo">
    <?php else: ?>
        <div class="avatar placeholder">Profile</div>
    <?php endif; ?>

    <h1><?= escape($user['full_name']) ?></h1>
    <h2><?= escape($user['headline']) ?></h2>
    <p><?= nl2br(escape($user['summary'])) ?></p>
    <p><?= escape($user['email']) ?></p>
</header>

<?php foreach ($groups as $type => $heading): ?>
    <section>
        <h2><?= escape($heading) ?></h2>

        <?php
        $found = false;
        foreach ($entries as $entry):
            if ($entry['entry_type'] !== $type) {
                continue;
            }
            $found = true;
        ?>
            <article class="entry">
                <h3><?= escape($entry['title']) ?></h3>
                <h4><?= escape($entry['organization']) ?></h4>
                <p class="date">
                    <?= escape($entry['start_date']) ?> &mdash;
                    <?= $entry['end_date'] ? escape($entry['end_date']) : 'Present' ?>
                </p>
                <p><?= nl2br(escape($entry['description'])) ?></p>
            </article>
        <?php endforeach; ?>

        <?php if (!$found): ?>
            <p class="empty">No <?= escape(strtolower($heading)) ?> added yet.</p>
        <?php endif; ?>
    </section>
<?php endforeach; ?>

<section>
    <h2>Technical Skills</h2>
    <?php if (count($skills) > 0): ?>
        <ul class="skills">
            <?php foreach ($skills as $skill): ?>
                <li><?= escape($skill['skill_name']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="empty">No skills added yet.</p>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
