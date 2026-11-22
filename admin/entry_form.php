
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$error = "";
$success = "";

// Check whether this is an edit request.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$isEditing = $id !== null && $id !== false && $id > 0;

$entry = [
    'category' => '',
    'title' => '',
    'organization' => '',
    'start_date' => '',
    'end_date' => '',
    'description' => ''
];

// Load existing entry for editing.
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

    $category = trim($_POST['category'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $description = trim($_POST['description'] ?? '');

    if (
        $category === '' ||
        $title === '' ||
        $organization === '' ||
        $start_date === '' ||
        $description === ''
    ) {
        $error = "Please fill in all required fields.";
    } elseif ($end_date !== '' && $end_date < $start_date) {
        $error = "End date cannot be before start date.";
    } else {

        if ($isEditing) {
            $stmt = $pdo->prepare(
                "UPDATE resume_entries
                 SET category = :category,
                     title = :title,
                     organization = :organization,
                     start_date = :start_date,
                     end_date = :end_date,
                     description = :description
                 WHERE id = :id AND user_id = :user_id"
            );

            $stmt->execute([
                'category' => $category,
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
                (user_id, category, title, organization, start_date, end_date, description)
                VALUES
                (:user_id, :category, :title, :organization, :start_date, :end_date, :description)"
            );

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'category' => $category,
                'title' => $title,
                'organization' => $organization,
                'start_date' => $start_date,
                'end_date' => $end_date !== '' ? $end_date : null,
                'description' => $description
            ]);

            $success = "Resume entry added successfully!";
        }

        $entry = [
            'category' => $category,
            'title' => $title,
            'organization' => $organization,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'description' => $description
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditing ? 'Edit' : 'Add' ?> Resume Entry</title>
</head>
<body>

    <h1><?= $isEditing ? 'Edit' : 'Add' ?> Resume Entry</h1>

    <?php if ($error !== ""): ?>
        <p style="color: red;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <p style="color: green;">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Category:</label>
        <select name="category" required>
            <option value="">Select Category</option>
            <?php foreach (['Education', 'Experience', 'Project', 'Skill'] as $categoryOption): ?>
                <option value="<?= $categoryOption ?>"
                    <?= $entry['category'] === $categoryOption ? 'selected' : '' ?>>
                    <?= $categoryOption ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label>Title:</label>
        <input type="text" name="title"
            value="<?= htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8') ?>" required>

        <br><br>

        <label>Organization:</label>
        <input type="text" name="organization"
            value="<?= htmlspecialchars($entry['organization'], ENT_QUOTES, 'UTF-8') ?>" required>

        <br><br>

        <label>Start Date:</label>
        <input type="date" name="start_date"
            value="<?= htmlspecialchars($entry['start_date'], ENT_QUOTES, 'UTF-8') ?>" required>

        <br><br>

        <label>End Date:</label>
        <input type="date" name="end_date"
            value="<?= htmlspecialchars($entry['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <br><br>

        <label>Description:</label>
        <textarea name="description" required><?= htmlspecialchars($entry['description'], ENT_QUOTES, 'UTF-8') ?></textarea>

        <br><br>

        <button type="submit">
            <?= $isEditing ? 'Update Entry' : 'Save Entry' ?>
        </button>

    </form>

    <br>
    <a href="admin.php">Back to Dashboard</a>

</body>
</html>
