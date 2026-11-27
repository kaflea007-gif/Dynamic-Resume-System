<?php
// Admin dashboard.
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$userId      = (int) $_SESSION['user_id'];
$skillLevels = ['Beginner', 'Intermediate', 'Advanced'];

/* POST actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        // file bigger than post_max_size
        setFlash('error', 'The upload was too large. Images must be smaller than 2 MB.');
    } elseif (!verifyCsrf()) {
        setFlash('error', 'Invalid request token. Please try again.');
    } elseif ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $headline = trim($_POST['headline'] ?? '');
        $summary  = trim($_POST['summary'] ?? '');
        $email    = trim($_POST['email'] ?? '');

        if ($fullName === '' || strLength($fullName) > 100) {
            setFlash('error', 'Full name is required (max 100 characters).');
        } elseif ($headline === '' || strLength($headline) > 150) {
            setFlash('error', 'Headline is required (max 150 characters).');
        } elseif ($summary === '') {
            setFlash('error', 'Professional summary is required.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strLength($email) > 120) {
            setFlash('error', 'Please enter a valid login email (max 120 characters).');
        } else {
            try {
                $stmt = $pdo->prepare(
                    'UPDATE users SET full_name = :full_name, headline = :headline,
                            summary = :summary, email = :email WHERE id = :id'
                );
                $stmt->execute([
                    'full_name' => $fullName, 'headline' => $headline,
                    'summary' => $summary, 'email' => $email, 'id' => $userId,
                ]);
                $_SESSION['user_name'] = $fullName;
                setFlash('success', 'Profile updated.');
            } catch (PDOException $e) {
                // 23000 = duplicate email
                setFlash('error', $e->getCode() === '23000'
                    ? 'That email is already in use.'
                    : 'Could not save the profile. Please try again.');
            }
        }
    } elseif ($action === 'upload_avatar') {
        if (!isset($_FILES['avatar'])) {
            setFlash('error', 'Please select an image.');
        } else {
            [$ok, $message] = handleAvatarUpload($pdo, $userId, $_FILES['avatar']);
            setFlash($ok ? 'success' : 'error', $message);
        }
    } elseif ($action === 'add_skill' || $action === 'update_skill') {
        $skill = trim($_POST['skill_name'] ?? '');
        $level = $_POST['skill_level'] ?? '';

        if ($skill === '' || strLength($skill) > 80) {
            setFlash('error', 'Skill name is required (max 80 characters).');
        } elseif (!in_array($level, $skillLevels, true)) {
            setFlash('error', 'Please choose a valid skill level.');
        } elseif ($action === 'add_skill') {
            $stmt = $pdo->prepare(
                'INSERT INTO skills (user_id, skill_name, skill_level) VALUES (:user_id, :skill_name, :skill_level)'
            );
            $stmt->execute(['user_id' => $userId, 'skill_name' => $skill, 'skill_level' => $level]);
            setFlash('success', 'Skill added.');
        } else {
            $skillId = filter_input(INPUT_POST, 'skill_id', FILTER_VALIDATE_INT);
            if ($skillId === false || $skillId === null || $skillId <= 0) {
                setFlash('error', 'Invalid skill ID.');
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE skills SET skill_name = :skill_name, skill_level = :skill_level
                     WHERE id = :id AND user_id = :user_id'
                );
                $stmt->execute([
                    'skill_name' => $skill, 'skill_level' => $level,
                    'id' => $skillId, 'user_id' => $userId,
                ]);
                setFlash('success', 'Skill updated.');
            }
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

    // redirect after POST
    header('Location: admin.php');
    exit;
}

/* Page data */
$stmt = $pdo->prepare('SELECT full_name, headline, summary, email, avatar_path FROM users WHERE id = :id');
$stmt->execute(['id' => $userId]);
$profile = $stmt->fetch();

if (!$profile) { // account was removed while still logged in
    header('Location: logout.php');
    exit;
}
$avatarPath = $profile['avatar_path'];

$stmt = $pdo->prepare(
    'SELECT * FROM resume_entries WHERE user_id = :user_id ORDER BY start_date DESC, id DESC'
);
$stmt->execute(['user_id' => $userId]);
$entries = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT id, skill_name, skill_level FROM skills WHERE user_id = :user_id ORDER BY id ASC');
$stmt->execute(['user_id' => $userId]);
$skills = $stmt->fetchAll();

// skill being edited
$editSkill = null;
if (isset($_GET['edit_skill'])) {
    $editId = filter_input(INPUT_GET, 'edit_skill', FILTER_VALIDATE_INT);
    foreach ($skills as $s) {
        if ($editId !== false && $editId !== null && (int) $s['id'] === $editId) {
            $editSkill = $s;
        }
    }
}

$flash     = popFlash();
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Admin Dashboard</h1>
<p>Welcome, <?= escape($_SESSION['user_name'] ?? '') ?>! <a href="index.php">View public resume</a></p>

<?php if ($flash): ?>
    <p class="<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= escape($flash['message']) ?></p>
<?php endif; ?>

<!-- Personal profile -->
<section class="panel">
    <h2>Personal Profile</h2>
    <form method="POST" action="">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="update_profile">

        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" maxlength="100" value="<?= escape($profile['full_name']) ?>" required>

        <label for="headline">Headline (title)</label>
        <input type="text" id="headline" name="headline" maxlength="150" value="<?= escape($profile['headline']) ?>" required>

        <label for="summary">Professional summary</label>
        <textarea id="summary" name="summary" rows="7" required><?= escape($profile['summary']) ?></textarea>

        <label for="email">Login email <small>(used to sign in; not shown on the public CV)</small></label>
        <input type="email" id="email" name="email" maxlength="120" value="<?= escape($profile['email']) ?>" required>

        <button type="submit">Save Profile</button>
    </form>
</section>

<!-- Profile portrait upload -->
<section class="panel">
    <h2>Profile Portrait</h2>
    <?php if ($avatarPath && is_file(__DIR__ . '/../' . $avatarPath)): ?>
        <img class="avatar small" src="../<?= escape($avatarPath) ?>" width="90" height="90" alt="Current profile thumbnail">
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
    <h2>Resume Entries (Education, Experience, Projects)</h2>
    <p><a class="btn-link" href="entry_form.php">+ Add Resume Entry</a></p>

    <?php if (count($entries) > 0): ?>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Category</th><th>Title</th><th>Organization</th>
                    <th>Start</th><th>End</th><th>Description</th><th>GitHub</th><th>Actions</th>
                </tr>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= escape($entry['entry_type']) ?></td>
                        <td><?= escape($entry['title']) ?></td>
                        <td><?= escape($entry['organization']) ?></td>
                        <td><?= escape($entry['start_date']) ?></td>
                        <td><?= $entry['end_date'] ? escape($entry['end_date']) : 'Present' ?></td>
                        <td><?= escape(function_exists('mb_strimwidth')
                                ? mb_strimwidth($entry['description'], 0, 110, '...', 'UTF-8')
                                : $entry['description']) ?></td>
                        <td>
                            <?php if (!empty($entry['github_url'])): ?>
                                <a href="<?= escape($entry['github_url']) ?>" target="_blank" rel="noopener noreferrer">Link</a>
                            <?php else: ?>&mdash;<?php endif; ?>
                        </td>
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
<section class="panel" id="skills">
    <h2>Technical Skills</h2>

    <?php if (count($skills) > 0): ?>
        <div class="table-wrap">
            <table>
                <tr><th>Skill</th><th>Level</th><th>Actions</th></tr>
                <?php foreach ($skills as $skill): ?>
                    <tr>
                        <td><?= escape($skill['skill_name']) ?></td>
                        <td><?= escape($skill['skill_level']) ?></td>
                        <td class="actions">
                            <a href="admin.php?edit_skill=<?= (int) $skill['id'] ?>#skills">Edit</a>
                            <form method="POST" action=""
                                  onsubmit="return confirm('Delete this skill?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_skill">
                                <input type="hidden" name="skill_id" value="<?= (int) $skill['id'] ?>">
                                <button type="submit" class="danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">No skills added yet.</p>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $editSkill ? 'update_skill' : 'add_skill' ?>">
        <?php if ($editSkill): ?>
            <input type="hidden" name="skill_id" value="<?= (int) $editSkill['id'] ?>">
        <?php endif; ?>

        <h3><?= $editSkill ? 'Edit skill' : 'Add a skill' ?></h3>
        <div class="inline-form">
            <div>
                <label for="skill_name">Skill name</label>
                <input type="text" id="skill_name" name="skill_name" maxlength="80"
                       value="<?= escape($editSkill['skill_name'] ?? '') ?>" required>
            </div>
            <div>
                <label for="skill_level">Level</label>
                <select id="skill_level" name="skill_level" required>
                    <?php foreach ($skillLevels as $level): ?>
                        <option value="<?= escape($level) ?>"
                            <?= ($editSkill['skill_level'] ?? 'Beginner') === $level ? 'selected' : '' ?>>
                            <?= escape($level) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit"><?= $editSkill ? 'Update Skill' : 'Add Skill' ?></button>
            <?php if ($editSkill): ?>
                <a class="btn-link btn-secondary" href="admin.php#skills">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
