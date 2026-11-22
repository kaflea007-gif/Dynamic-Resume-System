<?php
/**
 * Shared helper utilities: session/auth helpers, output escaping,
 * CSRF protection, validation helpers and GD thumbnail generation.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- Authentication helpers ---------- */

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/* ---------- Output escaping ---------- */

function escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* ---------- Flash messages (post/redirect/get) ---------- */

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function popFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/* ---------- CSRF protection ---------- */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . escape(csrfToken()) . '">';
}

function verifyCsrf(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $sent);
}

/* ---------- Validation helpers ---------- */

/** Character length (multibyte-safe when mbstring is available). */
function strLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

/** True only for a real calendar date in YYYY-MM-DD format. */
function isValidDate(string $date): bool
{
    $dt = DateTime::createFromFormat('!Y-m-d', $date);
    return $dt !== false && $dt->format('Y-m-d') === $date;
}

/* ---------- Image processing (PHP GD) ---------- */

/**
 * Create a centered, square $size x $size JPEG thumbnail with GD.
 * Transparent PNG areas are filled with white.
 */
function createThumbnail(string $sourcePath, string $mime, string $destPath, int $size = 150): bool
{
    $source = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($sourcePath),
        'image/png'  => @imagecreatefrompng($sourcePath),
        default      => false,
    };

    if (!$source) {
        return false;
    }

    $width  = imagesx($source);
    $height = imagesy($source);
    $side   = min($width, $height);

    $thumb = imagecreatetruecolor($size, $size);
    imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255));

    imagecopyresampled(
        $thumb, $source,
        0, 0,
        (int) (($width - $side) / 2), (int) (($height - $side) / 2),
        $size, $size,
        $side, $side
    );

    return imagejpeg($thumb, $destPath, 85);
}

/**
 * Validate an uploaded avatar, build the 150x150 thumbnail, store its
 * relative path in the database and delete the previous avatar.
 *
 * @return array{0: bool, 1: string} [success, message]
 */
function handleAvatarUpload(PDO $pdo, int $userId, array $file): array
{
    $maxBytes = 2 * 1024 * 1024; // 2 MB

    // 1. Upload error and payload auditing.
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [false, ($file['error'] ?? 0) === UPLOAD_ERR_INI_SIZE || ($file['error'] ?? 0) === UPLOAD_ERR_FORM_SIZE
            ? 'Image must be smaller than 2 MB.'
            : 'Image upload failed. Please select a file and try again.'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return [false, 'Invalid uploaded file.'];
    }

    // 2. Size limit.
    if ($file['size'] > $maxBytes) {
        return [false, 'Image must be smaller than 2 MB.'];
    }

    // 3. Server-side MIME verification (never trust client headers/extension).
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
        return [false, 'Only JPG and PNG images are allowed.'];
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || $info[0] > 6000 || $info[1] > 6000) {
        return [false, 'Invalid image file or image dimensions too large.'];
    }

    // 4. Random filename inside uploads/ (original name is discarded).
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $filename     = 'avatar_' . $userId . '_' . bin2hex(random_bytes(8)) . '.jpg';
    $destination  = $uploadDir . $filename;
    $relativePath = 'uploads/' . $filename;

    // 5. GD thumbnail generation.
    if (!createThumbnail($file['tmp_name'], $mime, $destination, 150)) {
        return [false, 'Could not process the image.'];
    }

    // Persist the new path, then remove the old file.
    try {
        $stmt = $pdo->prepare('SELECT avatar_path FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $oldAvatar = $stmt->fetchColumn();

        $stmt = $pdo->prepare('UPDATE users SET avatar_path = :path WHERE id = :id');
        $stmt->execute(['path' => $relativePath, 'id' => $userId]);
    } catch (PDOException $e) {
        unlink($destination);
        return [false, 'Could not save the image. Please try again.'];
    }

    if ($oldAvatar && strpos($oldAvatar, 'uploads/avatar_') === 0) {
        $oldFile = $uploadDir . basename($oldAvatar);
        if (is_file($oldFile)) {
            unlink($oldFile);
        }
    }

    return [true, 'Profile image uploaded successfully!'];
}
