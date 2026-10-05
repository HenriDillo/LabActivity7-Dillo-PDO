<?php
require 'auth.php';
require_guest();
require 'db.php';

$name = '';
$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = post_text('name');
    $email = post_text('email');
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) && is_string($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Enter a name with 1 to 100 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $errors[] = 'Enter a valid email address (maximum 254 characters).';
    }
    if (strlen($password) < 8 || strlen($password) > 72 || strpos($password, "\0") !== false) {
        $errors[] = 'Your password must be 8 to 72 bytes long (normal English characters use one byte each).';
    }
    if ($password !== $confirm) {
        $errors[] = 'The passwords do not match.';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'That email address is already registered.';
            } else {
                throw $e;
            }
        }
    }
}
$page_title = 'Register';
require 'header.php';
?>
<section class="card auth-card">
    <h1>Create an account</h1>
    <p class="muted">Join the conversation and share your first post.</p>
    <?php foreach ($errors as $error): ?>
        <p class="error" role="alert"><?= e($error) ?></p>
    <?php endforeach; ?>
    <form method="post">
        <?php csrf_field(); ?>
        <label for="name">Name</label>
        <input id="name" name="name" value="<?= e($name) ?>" required maxlength="100" pattern=".*\S.*" autocomplete="name">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?= e($email) ?>" required maxlength="254" autocomplete="email">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required minlength="8" maxlength="72" autocomplete="new-password">
        <small>Use at least 8 characters. Maximum 72 bytes.</small>
        <label for="confirm_password">Confirm password</label>
        <input id="confirm_password" name="confirm_password" type="password" required minlength="8" maxlength="72" autocomplete="new-password">
        <button type="submit">Register</button>
    </form>
    <p>Already registered? <a href="login.php">Log in</a></p>
</section>
<script>
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');
function validatePasswords() {
    password.setCustomValidity(new TextEncoder().encode(password.value).length > 72 ? 'Use a password of at most 72 bytes.' : '');
    confirmPassword.setCustomValidity(password.value === confirmPassword.value ? '' : 'The passwords do not match.');
}
password.addEventListener('input', validatePasswords);
confirmPassword.addEventListener('input', validatePasswords);
</script>
<?php require 'footer.php'; ?>
