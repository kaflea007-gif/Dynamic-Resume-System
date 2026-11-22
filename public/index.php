<?php
require_once __DIR__ . '/../includes/functions.php';
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
$pageTitle = $user['full_name'] . ' | Resume';
require_once __DIR__ . '/../includes/header.php';

$groups = [
    'experience' => 'Experience',
    'education' => 'Education',
    'project' => 'Projects'
];

?>

        <header class="profile">
            <?php if (!empty($user['avatar_path']) && file_exists(__DIR__ . '/../' . $user['avatar_path'])): ?>
                <img class="avatar" src="../<?= escape($user['avatar_path']) ?>" alt="Profile photo">
            <?php else: ?>
                <div class="avatar placeholder">Profile</div>
            <?php endif; ?>

            <h1><?= escape($user['full_name']) ?></h1>
            <h2><?= escape($user['headline']) ?></h2>
            <p><?= nl2br(escape($user['summary'])) ?></p>
            <p><?= escape($user['email']) ?></p>
        </header>

        <section class="skills">
            <h2>Technical Skills</h2>
            <ul>
                <li>C++</li>
                <li>PHP (Beginner)</li>
                <li>HTML</li>
                <li>CSS</li>
                <li>MySQL</li>
                <li>Git and GitHub</li>
                <li>Object-Oriented Programming</li>
            </ul>
        </section>


        <?php foreach ($groups as $type => $heading): ?>
            <section>
                <h2><?= escape($heading) ?></h2>

                <?php
                $found = false;
                foreach ($entries as $entry):
                    if ($entry['entry_type'] !== $type) continue;
                    $found = true;
                ?>
                    <article class="entry">
                        <h3><?= escape($entry['title']) ?></h3>
                        <h4><?= escape($entry['organization']) ?></h4>
                        <p class="date">
                            <?= escape($entry['start_date']) ?> —
                            <?= $entry['end_date'] ? escape($entry['end_date']) : 'Present' ?>
                        </p>
                        <p><?= nl2br(escape($entry['description'])) ?></p>
                    </article>
                <?php endforeach; ?>

                <?php if (!$found): ?>
                    <p class="empty">No <?= strtolower(escape($heading)) ?> added yet.</p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
