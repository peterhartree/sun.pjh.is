<?php
/**
 * Blog Template - Single Post View
 */

require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$result = findPostBySlug($slug);

if (!$result) {
    http_response_code(404);
    $pageTitle = 'Page not found';
    include __DIR__ . '/includes/header.php';
    echo '<h1>Page not found</h1>';
    echo '<p>Something has gone wrong. There is no page with this URL.</p>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$post = $result['post'];
$prevPost = $result['prev'];
$nextPost = $result['next'];

$pageTitle = $post['title'];
$pageDescription = SITE_DESCRIPTION;
$bodyClass = 'post-page';

include __DIR__ . '/includes/header.php';
?>

<article class="post-single">
    <div class="post-content">
        <?= $post['html'] ?>
    </div>

    <footer class="post-footer">
        <?= renderPostMeta($post) ?>
        <nav class="post-nav">
            <?php if ($prevPost): ?><a href="/<?= e($prevPost['slug']) ?>">prev</a><?php endif; ?>
            <?php if ($prevPost && $nextPost): ?> · <?php endif; ?>
            <?php if ($nextPost): ?><a href="/<?= e($nextPost['slug']) ?>">next</a><?php endif; ?>
        </nav>
    </footer>
</article>

<?php include __DIR__ . '/includes/footer.php'; ?>
