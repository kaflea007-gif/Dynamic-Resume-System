
<?php

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$error = "";
$success = "";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$isEditing = $id !== null && $id !== false && $id > 0;

$entry = [
    'entry_type' => '',
    'title' => '',
    'organization' => '',
    'start_date' => '',
    'end_date' => '',
    'description' => ''
];

if ($isEditing) {
    $stmt = $pdo->prepare(
        "SELECT * FROM resume_entries
         WHERE id = :id AND user_id = :user_id"
    );

    $stmt->execute([
        'id' => $id,
        'user_id' => $_SESSION['user_id']
    ]);

    $entry = $stmt->fetch();

    if (!$entry) {
        http_response_code(404);
        exit("Resume entry not found.");
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $entry_type = trim($_POST['entry_type'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $description = trim($_POST['description'] ?? '');

    $entry = [
        'entry_type' => $entry_type,
        'title' => $title,
        'organization' => $organization,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'description' => $description
    ];

    $validTypes = ['education', 'experience', 'project'];

    if (
        !in_array($entry_type, $validTypes, true) ||
        $title === '' ||
        $organization === '' ||
        $start_date === '' ||
        $description === ''
    ) {
        $error = "Please enter valid information in all required fields.";
    } elseif (
        strlen($title) > 120 ||
        strlen($organization) > 120
    ) {
        $error = "Title and organization must not exceed 120 characters.";
    } elseif (
        !DateTime::createFromFormat('!Y-m-d', $start_date) ||
        DateTime::createFromFormat('!Y-m-d', $start_date)->format('Y-m-d') !== $start_date
    ) {
        $error = "Please enter a valid start date.";
    } elseif (
        $end_date !== '' &&
        (
            !DateTime::createFromFormat('!Y-m-d', $end_date) ||
            DateTime::createFromFormat('!Y-m-d', $end_date)->format('Y-m-d') !== $end_date
        )
    ) {
        $error = "Please enter a valid end date.";
    } elseif ($end_date !== '' && $end_date < $start_date) {
        $error = "End date cannot be before start date.";
    } else {

        if ($isEditing) {
            $stmt = $pdo->prepare(
                "UPDATE resume_entries
                 SET entry_type = :entry_type,
                     title = :title,
                     organization = :organization,
                     start_date = :start_date,
                     end_date = :end_date,
                     description = :description
                 WHERE id = :id AND user_id = :user_id"
            );

            $stmt->execute([
                'entry_type' => $entry_type,
                'title' => $title,
                'organization' => $organization,
                'start_date' => $start_date,
                'end_date' => $end_date !== '' ? $end_date : null,
                'description' => $description,
                'id' => $id,
                'user_id' => $_SESSION['user_id']
            ]);

            $success = "Resume entry updated successfully!";

        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO resume_entries
                (user_id, entry_type, title, organization, start_date, end_date, description)
                VALUES
                (:user_id, :entry_type, :title, :organization, :start_date, :end_date, :description)"
            );

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'entry_type' => $entry_type,
                'title' => $title,
                'organization' => $organization,
                'start_date' => $start_date,
                'end_date' => $end_date !== '' ? $end_date : null,
                'description' => $description
            ]);

            $success = "Resume entry added successfully!";
        }
    }
}

$pageTitle = ($isEditing ? 'Edit' : 'Add') . ' Resume Entry';
require_once __DIR__ . '/../includes/header.php';

?>

<h1><?= $isEditing ? 'Edit' : 'Add' ?> Resume Entry</h1>

<?php if ($error !== ""): ?>
    <p class="error"><?= escape($error) ?></p>
<?php endif; ?>

<?php if ($success !== ""): ?>
    <p class="success"><?= escape($success) ?></p>
<?php endif; ?>

<form method="POST">

    <label>Category:</label>
    <select name="entry_type" required>
        <option value="">Select Category</option>

        <?php foreach (['education', 'experience', 'project'] as $type): ?>
            <option value="<?= escape($type) ?>"
                <?= $entry['entry_type'] === $type ? 'selected' : '' ?>>
                <?= escape(ucfirst($type)) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <label>Title:</label>
    <input type="text" name="title" maxlength="120"
           value="<?= escape($entry['title']) ?>" required>

    <br><br>

    <label>Organization:</label>
    <input type="text" name="organization" maxlength="120"
           value="<?= escape($entry['organization']) ?>" required>

    <br><br>

    <label>Start Date:</label>
    <input type="date" name="start_date"
           value="<?= escape($entry['start_date']) ?>" required>

    <br><br>

    <label>End Date:</label>
    <input type="date" name="end_date"
           value="<?= escape($entry['end_date'] ?? '') ?>">

    <br><br>

    <label>Description:</label>
    <textarea name="description" required><?= escape($entry['description']) ?></textarea>

    <br><br>

    <button type="submit">
        <?= $isEditing ? 'Update Entry' : 'Save Entry' ?>
    </button>

</form>

<br>

<a href="admin.php">Back to Dashboard</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
