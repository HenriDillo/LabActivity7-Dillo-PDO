<?php
require 'auth.php';
require_login();
require 'db.php';

$id = null;
$post_id = null;
$content = '';
$error = '';
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        http_response_code(400);
        exit('Invalid comment ID.');
    }
    $stmt = $pdo->prepare('SELECT * FROM comments WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    $comment = $stmt->fetch();
    if (!$comment) {
        http_response_code(403);
        exit('Comment not found or you do not own this comment.');
    }
    $post_id = $comment['post_id'];
    $content = $comment['content'];
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (!$id) {
        $post_id = filter_var($_POST['post_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $stmt = $pdo->prepare('SELECT id FROM posts WHERE id = ?');
        $stmt->execute([$post_id ?: 0]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            exit('The post does not exist.');
        }
    }
    $content = post_text('content');
    if ($content === '' || mb_strlen($content) > 1000) {
        $error = 'Enter a comment with 1 to 1,000 characters.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE comments SET content = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
            $stmt->execute([$content, $id, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
            $stmt->execute([$post_id, $_SESSION['user_id'], $content]);
        }
        header('Location: index.php#post-' . $post_id);
        exit;
    }
}
$page_title = $id ? 'Edit comment' : 'Add comment';
require 'header.php';
?>
<section class="card">
    <h1><?= e($page_title) ?></h1>
    <?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="post_id" value="<?= e($post_id) ?>">
        <label for="content">Comment text</label>
        <textarea id="content" name="content" rows="4" required maxlength="1000"><?= e($content) ?></textarea>
        <small>Text only. Maximum 1,000 characters.</small>
        <button type="submit"><?= $id ? 'Save changes' : 'Comment' ?></button>
        <a class="cancel" href="index.php#post-<?= e($post_id) ?>">Cancel</a>
    </form>
</section>
<script>
const content = document.getElementById('content');
content.addEventListener('input', function () {
    content.setCustomValidity(content.value.trim() ? '' : 'Enter a comment.');
});
</script>
<?php require 'footer.php'; ?>
