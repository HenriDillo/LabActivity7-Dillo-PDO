<?php
require 'auth.php';
require_guest();
require 'db.php';

$email = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = post_text('email');
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || $password === '' || strlen($password) > 72) {
        $error = 'Enter a valid email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        }
        $error = 'Incorrect email or password.';
    }
}
$page_title = 'Log in';
require 'header.php';
?>
<section class="card auth-card">
    <h1>Welcome back</h1>
    <p class="muted">Log in to read posts and join the conversation.</p>
    <?php if (isset($_GET['registered'])): ?>
        <p class="success">Account created! You can now log in.</p>
    <?php endif; ?>
    <?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <?php csrf_field(); ?>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?= e($email) ?>" required maxlength="254" autocomplete="email">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required maxlength="72" autocomplete="current-password">
        <button type="submit">Log in</button>
    </form>
    <p>No account yet? <a href="register.php">Register</a></p>
</section>
<?php require 'footer.php'; ?>
