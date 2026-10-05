<?php
require 'auth.php';
require_login();
require 'db.php';

$posts = $pdo->query('SELECT posts.*, users.name FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC, posts.id DESC')->fetchAll();
$comments = $pdo->query('SELECT comments.*, users.name FROM comments JOIN users ON comments.user_id = users.id ORDER BY comments.created_at ASC, comments.id ASC')->fetchAll();
// Group comments by post so each post displays only its own comments.
$comments_by_post = [];
foreach ($comments as $comment) {
    $comments_by_post[$comment['post_id']][] = $comment;
}
$page_title = 'News feed';
require 'header.php';
?>
<div class="feed-heading">
    <div><h1>News feed</h1><p class="muted">Hello, <?= e($_SESSION['user_name']) ?>. Here is what everyone is sharing.</p></div>
    <a class="button" href="post.php">Write a post</a>
</div>
<?php if (!$posts): ?>
    <section class="card"><h2>No posts yet</h2><p>Share something to start the conversation.</p></section>
<?php endif; ?>
<?php foreach ($posts as $post): ?>
    <article class="card" id="post-<?= e($post['id']) ?>">
        <h2><?= e($post['title']) ?></h2>
        <p class="meta">By <?= e($post['name']) ?> · <?= e($post['created_at']) ?>
            <?php if ($post['updated_at']): ?><span class="badge">Edited</span><?php endif; ?>
            <?php if ((int) $post['user_id'] === $_SESSION['user_id']): ?>
                · <a href="post.php?id=<?= e($post['id']) ?>">Edit post</a>
            <?php endif; ?>
        </p>
        <p class="content"><?= e($post['content']) ?></p>
        <section class="comments">
            <h3>Comments (<?= count($comments_by_post[$post['id']] ?? []) ?>)</h3>
            <?php foreach ($comments_by_post[$post['id']] ?? [] as $comment): ?>
                <div class="comment">
                    <p class="meta"><strong><?= e($comment['name']) ?></strong> · <?= e($comment['created_at']) ?>
                        <?php if ($comment['updated_at']): ?><span class="badge">Edited</span><?php endif; ?>
                        <?php if ((int) $comment['user_id'] === $_SESSION['user_id']): ?>
                            · <a href="comment.php?id=<?= e($comment['id']) ?>">Edit comment</a>
                        <?php endif; ?>
                    </p>
                    <p class="content"><?= e($comment['content']) ?></p>
                </div>
            <?php endforeach; ?>
            <form action="comment.php" method="post">
                <?php csrf_field(); ?>
                <input type="hidden" name="post_id" value="<?= e($post['id']) ?>">
                <label for="comment-<?= e($post['id']) ?>">Add a comment</label>
                <textarea id="comment-<?= e($post['id']) ?>" name="content" rows="2" required maxlength="1000"></textarea>
                <button type="submit">Comment</button>
            </form>
        </section>
    </article>
<?php endforeach; ?>
<script>
document.querySelectorAll('textarea').forEach(function (field) {
    field.addEventListener('input', function () {
        field.setCustomValidity(field.value.trim() ? '' : 'Enter a comment.');
    });
});
</script>
<?php require 'footer.php'; ?>
