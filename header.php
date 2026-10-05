<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Simple Blog') ?> | Simple Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">Simple Blog</a>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="index.php">News feed</a>
            <a href="post.php">New post</a>
            <form action="logout.php" method="post" class="inline-form">
                <?php csrf_field(); ?>
                <button type="submit" class="secondary">Log out</button>
            </form>
        <?php else: ?>
            <a href="login.php">Log in</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main>
