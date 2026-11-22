
<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['avatar'])) {
        $error = "Please select an image.";
    } elseif ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $error = "Image upload failed. Please try again.";
    } elseif (!is_uploaded_file($_FILES['avatar']['tmp_name'])) {
        $error = "Invalid uploaded file.";
    } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
        $error = "Image must be smaller than 2 MB.";
    } else {

        $tmp = $_FILES['avatar']['tmp_name'];

        // Check actual MIME type.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);

        $allowed = [
            'image/jpeg',
            'image/png'
        ];

        if (!in_array($mime, $allowed, true)) {
            $error = "Only JPG and PNG images are allowed.";
        } else {

            $imageInfo = getimagesize($tmp);

            if ($imageInfo === false) {
                $error = "Invalid image file.";
            } else {

                // Load image using GD.
                switch ($mime) {
                    case 'image/jpeg':
                        $source = imagecreatefromjpeg($tmp);
                        break;
                    case 'image/png':
                        $source = imagecreatefrompng($tmp);
                        break;
                    case 'image/webp':
                        $source = imagecreatefromwebp($tmp);
                        break;
                }

                if (!$source) {
                    $error = "Could not process the image.";
                } else {

                    $width = imagesx($source);
                    $height = imagesy($source);

                    // Create a square 150 x 150 thumbnail.
                    $thumbnail = imagecreatetruecolor(150, 150);

                    $side = min($width, $height);
                    $sourceX = (int) (($width - $side) / 2);
                    $sourceY = (int) (($height - $side) / 2);

                    imagecopyresampled(
                        $thumbnail,
                        $source,
                        0, 0,
                        $sourceX, $sourceY,
                        150, 150,
                        $side, $side
                    );

                    $filename = 'avatar_' . $_SESSION['user_id']
                        . '_' . bin2hex(random_bytes(8)) . '.jpg';

                    $uploadPath = __DIR__ . '/../uploads/' . $filename;
                    $relativePath = 'uploads/' . $filename;

                    if (imagejpeg($thumbnail, $uploadPath, 85)) {

                        // Get previous avatar.
                        $stmt = $pdo->prepare(
                            "SELECT avatar_path FROM users WHERE id = :id"
                        );
                        $stmt->execute(['id' => $_SESSION['user_id']]);
                        $oldAvatar = $stmt->fetchColumn();

                        // Save new avatar path.
                        $stmt = $pdo->prepare(
                            "UPDATE users SET avatar_path = :path WHERE id = :id"
                        );

                        $stmt->execute([
                            'path' => $relativePath,
                            'id' => $_SESSION['user_id']
                        ]);

                        // Remove previous uploaded avatar.
                        if (
                            $oldAvatar &&
                            strpos($oldAvatar, 'uploads/') === 0 &&
                            is_file(__DIR__ . '/../' . $oldAvatar)
                        ) {
                            unlink(__DIR__ . '/../' . $oldAvatar);
                        }

                        $success = "Profile image uploaded successfully!";
                    } else {
                        $error = "Could not save thumbnail.";
                    }

                    imagedestroy($source);
                    imagedestroy($thumbnail);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Image</title>
</head>
<body>

    <h1>Upload Profile Image</h1>

    <?php if ($error !== ""): ?>
        <p style="color:red;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <p style="color:green;">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Select profile image:</label>
        <input type="file" name="avatar"
               accept="image/jpeg,image/png" required>

        <br><br>

        <button type="submit">Upload Image</button>

    </form>

    <br>
    <a href="admin.php">Back to Dashboard</a>

</body>
</html>
