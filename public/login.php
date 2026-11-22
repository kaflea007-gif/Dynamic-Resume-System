<?php
session_start();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

// Already authenticated users go straight to the dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, password FROM users WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['user_name'] = $user['full_name'];

            header('Location: admin.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Admin Login';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Admin Login</h1>

<?php if ($error !== ''): ?>
    <p class="error"><?= escape($error) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= escape($_POST['email'] ?? '') ?>" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Login</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
