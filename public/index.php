<?php
// Public resume page (read-only).
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

// profile
$stmt = $pdo->prepare('SELECT * FROM users ORDER BY id ASC LIMIT :limit');
$stmt->bindValue(':limit', 1, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();

if (!$user) {
    die('No resume profile found. Please import resume_db.sql first (see README.md).');
}

// entries grouped by type
$stmt = $pdo->prepare(
    'SELECT * FROM resume_entries WHERE user_id = :user_id ORDER BY start_date DESC, id DESC'
);
$stmt->execute(['user_id' => $user['id']]);

$byType = ['education' => [], 'project' => [], 'experience' => []];
foreach ($stmt->fetchAll() as $row) {
    $byType[$row['entry_type']][] = $row;
}

$stmt = $pdo->prepare('SELECT skill_name, skill_level FROM skills WHERE user_id = :user_id ORDER BY id ASC');
$stmt->execute(['user_id' => $user['id']]);
$skills = $stmt->fetchAll();

// heading and empty-state text per section
$sections = [
    'education'  => ['Education',       'No education records added yet.'],
    'project'    => ['Projects',        'No projects added yet.'],
    'experience' => ['Work Experience', 'No work experience added yet.'],
];

$pageTitle  = $user['full_name'] . ' | Resume';
$footerName = $user['full_name'];
require_once __DIR__ . '/../includes/header.php';
?>

<header class="profile">
    <?php if (!empty($user['avatar_path']) && is_file(__DIR__ . '/../' . $user['avatar_path'])): ?>
        <img class="avatar" src="../<?= escape($user['avatar_path']) ?>" width="150" height="150"
             alt="Profile photo of <?= escape($user['full_name']) ?>">
    <?php else: ?>
        <div class="avatar placeholder" aria-label="No profile photo uploaded">No photo</div>
    <?php endif; ?>

    <h1><?= escape($user['full_name']) ?></h1>
    <h2><?= escape($user['headline']) ?></h2>
</header>

<section>
    <h2>Professional Summary</h2>
    <p><?= nl2br(escape($user['summary'])) ?></p>
</section>

<?php foreach ($sections as $type => [$heading, $emptyText]): ?>
    <section>
        <h2><?= escape($heading) ?></h2>

        <?php if (count($byType[$type]) > 0): ?>
            <?php foreach ($byType[$type] as $entry): ?>
                <article class="entry">
                    <h3><?= escape($entry['title']) ?></h3>
                    <h4><?= escape($entry['organization']) ?></h4>

                    <?php if ($type !== 'project'): // project dates are kept in the database but not displayed ?>
                        <p class="date">
                            <?= escape(formatMonthYear($entry['start_date'])) ?> &ndash;
                            <?= $entry['end_date'] ? escape(formatMonthYear($entry['end_date'])) : 'Present' ?>
                        </p>
                    <?php endif; ?>

                    <p><?= nl2br(escape($entry['description'])) ?></p>

                    <?php if (!empty($entry['technologies'])): ?>
                        <p class="tech"><strong>Technologies:</strong> <?= escape($entry['technologies']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($entry['github_url'])): ?>
                        <p><a class="github-link" href="<?= escape($entry['github_url']) ?>"
                              target="_blank" rel="noopener noreferrer">View on GitHub &rarr;</a></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty"><?= escape($emptyText) ?></p>
        <?php endif; ?>
    </section>
<?php endforeach; ?>

<section>
    <h2>Technical Skills</h2>
    <?php if (count($skills) > 0): ?>
        <ul class="skills">
            <?php foreach ($skills as $skill): ?>
                <li>
                    <?= escape($skill['skill_name']) ?>
                    <span class="level level-<?= escape(strtolower($skill['skill_level'])) ?>"><?= escape($skill['skill_level']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="empty">No skills added yet.</p>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
