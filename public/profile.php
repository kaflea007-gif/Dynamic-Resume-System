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

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);

        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            $error = "Only JPG and PNG images are allowed.";
        } else {

            $imageInfo = getimagesize($tmp);

            if ($imageInfo === false) {
                $error = "Invalid image file.";
            } else {

                $source = ($mime === 'image/jpeg')
                    ? imagecreatefromjpeg($tmp)
                    : imagecreatefrompng($tmp);

                if (!$source) {
                    $error = "Could not process the image.";
                } else {

                    $width = imagesx($source);
                    $height = imagesy($source);

                    $thumbnail = imagecreatetruecolor(150, 150);

                    // White background for transparent PNG images.
                    $white = imagecolorallocate($thumbnail, 255, 255, 255);
                    imagefill($thumbnail, 0, 0, $white);

                    $side = min($width, $height);
                    $sourceX = (int)(($width - $side) / 2);
                    $sourceY = (int)(($height - $side) / 2);

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

                        $stmt = $pdo->prepare(
                            "SELECT avatar_path FROM users WHERE id = :id"
                        );

                        $stmt->execute([
                            'id' => $_SESSION['user_id']
                        ]);

                        $oldAvatar = $stmt->fetchColumn();

                        $stmt = $pdo->prepare(
                            "UPDATE users SET avatar_path = :path WHERE id = :id"
                        );

                        $stmt->execute([
                            'path' => $relativePath,
                            'id' => $_SESSION['user_id']
                        ]);

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

$pageTitle = 'Profile Image';
require_once __DIR__ . '/../includes/header.php';

?>

<h1>Upload Profile Image</h1>

<?php if ($error !== ""): ?>
    <p class="error"><?= escape($error) ?></p>
<?php endif; ?>

<?php if ($success !== ""): ?>
    <p class="success"><?= escape($success) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Select profile image:</label>

    <input type="file"
           name="avatar"
           accept="image/jpeg,image/png"
           required>

    <br><br>

    <button type="submit">Upload Image</button>

</form>

<br>

<a href="admin.php">Back to Dashboard</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
