<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$errors     = [];
$validTypes = ['education', 'experience', 'project'];

// Edit mode: validated integer ID from the query string.
$isEditing = false;
$id        = null;

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id <= 0) {
        setFlash('error', 'Invalid entry ID.');
        header('Location: admin.php');
        exit;
    }
    $isEditing = true;
}

$entry = [
    'entry_type'   => '',
    'title'        => '',
    'organization' => '',
    'start_date'   => '',
    'end_date'     => '',
    'description'  => '',
];

if ($isEditing) {
    $stmt = $pdo->prepare('SELECT * FROM resume_entries WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);
    $found = $stmt->fetch();

    if (!$found) {
        setFlash('error', 'Resume entry not found.');
        header('Location: admin.php');
        exit;
    }
    $entry = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entry = [
        'entry_type'   => trim($_POST['entry_type'] ?? ''),
        'title'        => trim($_POST['title'] ?? ''),
        'organization' => trim($_POST['organization'] ?? ''),
        'start_date'   => trim($_POST['start_date'] ?? ''),
        'end_date'     => trim($_POST['end_date'] ?? ''),
        'description'  => trim($_POST['description'] ?? ''),
    ];

    if (!verifyCsrf()) {
        $errors['form'] = 'Invalid request token. Please reload the page and try again.';
    }
    if (!in_array($entry['entry_type'], $validTypes, true)) {
        $errors['entry_type'] = 'Please choose a valid category.';
    }
    if ($entry['title'] === '' || strLength($entry['title']) > 120) {
        $errors['title'] = 'Title is required (max 120 characters).';
    }
    if ($entry['organization'] === '' || strLength($entry['organization']) > 120) {
        $errors['organization'] = 'Organization is required (max 120 characters).';
    }
    if (!isValidDate($entry['start_date'])) {
        $errors['start_date'] = 'Please enter a valid start date.';
    }
    if ($entry['end_date'] !== '') {
        if (!isValidDate($entry['end_date'])) {
            $errors['end_date'] = 'Please enter a valid end date.';
        } elseif (!isset($errors['start_date']) && $entry['end_date'] < $entry['start_date']) {
            $errors['end_date'] = 'End date cannot be before start date.';
        }
    }
    if ($entry['description'] === '') {
        $errors['description'] = 'Description is required.';
    }

    if (!$errors) {
        $params = [
            'entry_type'   => $entry['entry_type'],
            'title'        => $entry['title'],
            'organization' => $entry['organization'],
            'start_date'   => $entry['start_date'],
            'end_date'     => $entry['end_date'] !== '' ? $entry['end_date'] : null,
            'description'  => $entry['description'],
            'user_id'      => $_SESSION['user_id'],
        ];

        if ($isEditing) {
            $params['id'] = $id;
            $stmt = $pdo->prepare(
                'UPDATE resume_entries
                 SET entry_type = :entry_type, title = :title, organization = :organization,
                     start_date = :start_date, end_date = :end_date, description = :description
                 WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute($params);
            setFlash('success', 'Resume entry updated successfully!');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO resume_entries
                    (user_id, entry_type, title, organization, start_date, end_date, description)
                 VALUES
                    (:user_id, :entry_type, :title, :organization, :start_date, :end_date, :description)'
            );
            $stmt->execute($params);
            setFlash('success', 'Resume entry added successfully!');
        }

        // Post/Redirect/Get: avoids duplicate inserts on refresh.
        header('Location: admin.php');
        exit;
    }
}

$pageTitle = ($isEditing ? 'Edit' : 'Add') . ' Resume Entry';
require_once __DIR__ . '/../includes/header.php';
?>

<h1><?= $isEditing ? 'Edit' : 'Add' ?> Resume Entry</h1>

<?php if (isset($errors['form'])): ?>
    <p class="error"><?= escape($errors['form']) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <?= csrfField() ?>

    <label for="entry_type">Category</label>
    <select id="entry_type" name="entry_type" required>
        <option value="">Select Category</option>
        <?php foreach ($validTypes as $type): ?>
            <option value="<?= escape($type) ?>" <?= $entry['entry_type'] === $type ? 'selected' : '' ?>>
                <?= escape(ucfirst($type)) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['entry_type'])): ?><p class="field-error"><?= escape($errors['entry_type']) ?></p><?php endif; ?>

    <label for="title">Title</label>
    <input type="text" id="title" name="title" maxlength="120" value="<?= escape($entry['title']) ?>" required>
    <?php if (isset($errors['title'])): ?><p class="field-error"><?= escape($errors['title']) ?></p><?php endif; ?>

    <label for="organization">Organization</label>
    <input type="text" id="organization" name="organization" maxlength="120" value="<?= escape($entry['organization']) ?>" required>
    <?php if (isset($errors['organization'])): ?><p class="field-error"><?= escape($errors['organization']) ?></p><?php endif; ?>

    <label for="start_date">Start Date</label>
    <input type="date" id="start_date" name="start_date" value="<?= escape($entry['start_date']) ?>" required>
    <?php if (isset($errors['start_date'])): ?><p class="field-error"><?= escape($errors['start_date']) ?></p><?php endif; ?>

    <label for="end_date">End Date <small>(leave empty for "Present")</small></label>
    <input type="date" id="end_date" name="end_date" value="<?= escape($entry['end_date'] ?? '') ?>">
    <?php if (isset($errors['end_date'])): ?><p class="field-error"><?= escape($errors['end_date']) ?></p><?php endif; ?>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="5" required><?= escape($entry['description']) ?></textarea>
    <?php if (isset($errors['description'])): ?><p class="field-error"><?= escape($errors['description']) ?></p><?php endif; ?>

    <button type="submit"><?= $isEditing ? 'Update Entry' : 'Save Entry' ?></button>
    <a class="btn-link" href="admin.php">Cancel</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
