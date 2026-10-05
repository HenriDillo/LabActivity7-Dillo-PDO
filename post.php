<?php
require 'auth.php';
require_login();
require 'db.php';

$id = null;
$title = '';
$content = '';
$errors = [];
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        http_response_code(400);
        exit('Invalid post ID.');
    }
    // Check ownership on the server, not just by hiding the edit link.
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    $post = $stmt->fetch();
    if (!$post) {
        http_response_code(403);
        exit('Post not found or you do not own this post.');
    }
    $title = $post['title'];
    $content = $post['content'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $title = post_text('title');
    $content = post_text('content');
    if ($title === '' || mb_strlen($title) > 150) {
        $errors[] = 'Enter a title with 1 to 150 characters.';
    }
    if ($content === '' || mb_strlen($content) > 5000) {
        $errors[] = 'Enter post text with 1 to 5,000 characters.';
    }
    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
            $stmt->execute([$title, $content, $id, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)');
            $stmt->execute([$_SESSION['user_id'], $title, $content]);
            $id = $pdo->lastInsertId();
        }
        header('Location: index.php#post-' . $id);
        exit;
    }
}
$page_title = $id ? 'Edit post' : 'New post';
require 'header.php';
?>
<section class="card">
    <h1><?= e($page_title) ?></h1>
    <?php foreach ($errors as $error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endforeach; ?>
    <form method="post">
        <?php csrf_field(); ?>
        <label for="title">Title</label>
        <input id="title" name="title" value="<?= e($title) ?>" required maxlength="150" pattern=".*\S.*">
        <label for="content">Post text</label>
        <textarea id="content" name="content" rows="8" required maxlength="5000"><?= e($content) ?></textarea>
        <small>Text only. Maximum 5,000 characters.</small>
        <button type="submit"><?= $id ? 'Save changes' : 'Publish post' ?></button>
        <a class="cancel" href="index.php">Cancel</a>
    </form>
</section>
<script>
const content = document.getElementById('content');
content.addEventListener('input', function () {
    content.setCustomValidity(content.value.trim() ? '' : 'Enter some post text.');
});
</script>
<?php require 'footer.php'; ?>
