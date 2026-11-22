<?php

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

// Fetch entries belonging to the logged-in user.
$stmt = $pdo->prepare(
    "SELECT * FROM resume_entries
     WHERE user_id = :user_id
     ORDER BY start_date DESC"
);

$stmt->execute([
    'user_id' => $_SESSION['user_id']
]);

$entries = $stmt->fetchAll();

// Shared page header.
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';

?>

<h1>Admin Dashboard</h1>

<p>
    Welcome,
    <?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?>!
</p>

<a href="entry_form.php">Add Resume Entry</a>
|
<a href="profile.php">Edit Profile Photo</a>
|
<a href="logout.php">Logout</a>

<h2>Your Resume Entries</h2>

<?php if (count($entries) > 0): ?>

    <table border="1" cellpadding="10">
        <tr>
            <th>Category</th>
            <th>Title</th>
            <th>Organization</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?= htmlspecialchars($entry['entry_type'], ENT_QUOTES, 'UTF-8') ?></td>

                <td><?= htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8') ?></td>

                <td><?= htmlspecialchars($entry['organization'], ENT_QUOTES, 'UTF-8') ?></td>

                <td><?= htmlspecialchars($entry['start_date'], ENT_QUOTES, 'UTF-8') ?></td>

                <td>
                    <?= $entry['end_date']
                        ? htmlspecialchars($entry['end_date'], ENT_QUOTES, 'UTF-8')
                        : 'Present' ?>
                </td>

                <td><?= htmlspecialchars($entry['description'], ENT_QUOTES, 'UTF-8') ?></td>

                <td>
                    <a href="entry_form.php?id=<?= (int) $entry['id'] ?>">Edit</a>

                    <form method="POST"
                          action="delete_entry.php"
                          onsubmit="return confirm('Are you sure you want to delete this entry?');"
                          style="display:inline;">

                        <input type="hidden" name="id" value="<?= (int) $entry['id'] ?>">

                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>

<?php else: ?>

    <p>No resume entries found. Add your first entry!</p>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
