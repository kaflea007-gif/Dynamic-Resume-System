<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$userId = (int) $_SESSION['user_id'];

/* ---------- POST actions: avatar upload, add/delete skill ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!verifyCsrf()) {
        setFlash('error', 'Invalid request token. Please try again.');
    } elseif ($action === 'upload_avatar') {
        if (!isset($_FILES['avatar'])) {
            setFlash('error', 'Please select an image.');
        } else {
            [$ok, $message] = handleAvatarUpload($pdo, $userId, $_FILES['avatar']);
            setFlash($ok ? 'success' : 'error', $message);
        }
    } elseif ($action === 'add_skill') {
        $skill = trim($_POST['skill_name'] ?? '');
        if ($skill === '' || strLength($skill) > 80) {
            setFlash('error', 'Skill name is required (max 80 characters).');
        } else {
            $stmt = $pdo->prepare('INSERT INTO skills (user_id, skill_name) VALUES (:user_id, :skill_name)');
            $stmt->execute(['user_id' => $userId, 'skill_name' => $skill]);
            setFlash('success', 'Skill added.');
        }
    } elseif ($action === 'delete_skill') {
        $skillId = filter_input(INPUT_POST, 'skill_id', FILTER_VALIDATE_INT);
        if ($skillId === false || $skillId === null || $skillId <= 0) {
            setFlash('error', 'Invalid skill ID.');
        } else {
            $stmt = $pdo->prepare('DELETE FROM skills WHERE id = :id AND user_id = :user_id');
            $stmt->execute(['id' => $skillId, 'user_id' => $userId]);
            setFlash('success', 'Skill deleted.');
        }
    }

    // Post/Redirect/Get.
    header('Location: admin.php');
    exit;
}

/* ---------- Data for the dashboard ---------- */
$stmt = $pdo->prepare(
    'SELECT * FROM resume_entries WHERE user_id = :user_id ORDER BY start_date DESC'
);
$stmt->execute(['user_id' => $userId]);
$entries = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT id, skill_name FROM skills WHERE user_id = :user_id ORDER BY skill_name ASC');
$stmt->execute(['user_id' => $userId]);
$skills = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT avatar_path FROM users WHERE id = :id');
$stmt->execute(['id' => $userId]);
$avatarPath = $stmt->fetchColumn();

$flash     = popFlash();
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Admin Dashboard</h1>
<p>Welcome, <?= escape($_SESSION['user_name'] ?? '') ?>!</p>

<?php if ($flash): ?>
    <p class="<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= escape($flash['message']) ?></p>
<?php endif; ?>

<!-- Profile portrait upload -->
<section class="panel">
    <h2>Profile Portrait</h2>
    <?php if ($avatarPath && is_file(__DIR__ . '/../' . $avatarPath)): ?>
        <img class="avatar small" src="../<?= escape($avatarPath) ?>" alt="Current profile thumbnail">
    <?php else: ?>
        <p class="empty">No portrait uploaded yet.</p>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="upload_avatar">
        <label for="avatar">Upload JPG or PNG (max 2 MB). It is resized to a 150&times;150 thumbnail.</label>
        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png" required>
        <button type="submit">Upload Image</button>
    </form>
</section>

<!-- Resume entries -->
<section class="panel">
    <h2>Resume Entries</h2>
    <p><a class="btn-link" href="entry_form.php">+ Add Resume Entry</a></p>

    <?php if (count($entries) > 0): ?>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Category</th><th>Title</th><th>Organization</th>
                    <th>Start</th><th>End</th><th>Description</th><th>Actions</th>
                </tr>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= escape($entry['entry_type']) ?></td>
                        <td><?= escape($entry['title']) ?></td>
                        <td><?= escape($entry['organization']) ?></td>
                        <td><?= escape($entry['start_date']) ?></td>
                        <td><?= $entry['end_date'] ? escape($entry['end_date']) : 'Present' ?></td>
                        <td><?= escape($entry['description']) ?></td>
                        <td class="actions">
                            <a href="entry_form.php?id=<?= (int) $entry['id'] ?>">Edit</a>
                            <form method="POST" action="delete_entry.php"
                                  onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $entry['id'] ?>">
                                <button type="submit" class="danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">No resume entries found. Add your first entry!</p>
    <?php endif; ?>
</section>

<!-- Skills -->
<section class="panel">
    <h2>Technical Skills</h2>

    <?php if (count($skills) > 0): ?>
        <ul class="skill-admin-list">
            <?php foreach ($skills as $skill): ?>
                <li>
                    <?= escape($skill['skill_name']) ?>
                    <form method="POST" action=""
                          onsubmit="return confirm('Delete this skill?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_skill">
                        <input type="hidden" name="skill_id" value="<?= (int) $skill['id'] ?>">
                        <button type="submit" class="danger">&times;</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="empty">No skills added yet.</p>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_skill">
        <label for="skill_name">Add a skill</label>
        <input type="text" id="skill_name" name="skill_name" maxlength="80" required>
        <button type="submit">Add Skill</button>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
