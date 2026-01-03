<?php
/**
 * Blog Template - Core Functions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Parsedown.php';

/**
 * Parse YAML-style frontmatter from markdown content
 */
function parseFrontmatter($content) {
    $lines = explode("\n", $content);
    $frontmatter = [];
    $bodyStart = 0;

    foreach ($lines as $i => $line) {
        $line = trim($line);
        if (empty($line)) {
            $bodyStart = $i + 1;
            break;
        }

        if (preg_match('/^(\w+):\s*(.*)$/', $line, $matches)) {
            $key = strtolower($matches[1]);
            $value = trim($matches[2]);
            $frontmatter[$key] = $value;
        }
    }

    $body = implode("\n", array_slice($lines, $bodyStart));

    return [
        'frontmatter' => $frontmatter,
        'body' => $body
    ];
}

/**
 * Parse tags from frontmatter
 */
function parseTags($tagString) {
    if (empty($tagString)) {
        return [];
    }

    $tags = array_map('trim', explode(',', $tagString));
    return array_filter($tags);
}

/**
 * Create URL-safe slug from tag name
 */
function tagSlug($tag) {
    $slug = mb_strtolower($tag, 'UTF-8');
    $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $slug);
    $slug = preg_replace('/[\s_]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Transliterate accented characters to ASCII
 */
function removeAccents($string) {
    $accents = [
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'ae',
        'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
        'î'=>'i', 'ï'=>'i', 'ð'=>'d', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o',
        'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u',
        'ý'=>'y', 'ÿ'=>'y', 'þ'=>'th', 'ß'=>'ss',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'AE',
        'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I',
        'Î'=>'I', 'Ï'=>'I', 'Ð'=>'D', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U',
        'Ý'=>'Y', 'Þ'=>'TH', 'œ'=>'oe', 'Œ'=>'OE', 'š'=>'s', 'Š'=>'S',
        'ž'=>'z', 'Ž'=>'Z', 'ƒ'=>'f'
    ];
    return strtr($string, $accents);
}

/**
 * Create URL-safe slug from post title
 */
function postSlug($title) {
    $slug = mb_strtolower($title, 'UTF-8');
    // Transliterate accented characters to ASCII
    $slug = removeAccents($slug);
    // Replace em-dash and other dashes with hyphen
    $slug = str_replace(['—', '–', '―'], '-', $slug);
    // Remove special characters except letters, numbers, spaces, hyphens
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    // Replace spaces with hyphens
    $slug = preg_replace('/[\s]+/', '-', $slug);
    // Remove consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Parse date from various formats
 * Supports: YYYY-MM-DD, YYYY-MM-DD HHMM, YYYY-MM-DD HH:MM
 */
function parseDate($dateString) {
    $dateString = trim($dateString);

    // Try YYYY-MM-DD HH:MM format
    if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d{2}):?(\d{2})$/', $dateString, $matches)) {
        return strtotime($matches[1] . ' ' . $matches[2] . ':' . $matches[3]);
    }

    // Try YYYY-MM-DD HHMM format (no colon)
    if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d{4})$/', $dateString, $matches)) {
        $time = $matches[2];
        $hours = substr($time, 0, 2);
        $mins = substr($time, 2, 2);
        return strtotime($matches[1] . ' ' . $hours . ':' . $mins);
    }

    // Try just YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
        return strtotime($dateString);
    }

    return strtotime($dateString);
}

/**
 * Format date for display
 */
function formatDate($timestamp) {
    return date(DATE_FORMAT, $timestamp);
}

/**
 * Load and parse a single post file
 */
function loadPost($filepath) {
    if (!file_exists($filepath)) {
        return null;
    }

    $filename = basename($filepath);

    // Skip drafts (files starting with underscore)
    if (strpos($filename, '_') === 0) {
        return null;
    }

    $content = file_get_contents($filepath);
    $parsed = parseFrontmatter($content);

    // Parse markdown body
    $parsedown = new Parsedown();
    $parsedown->setSafeMode(false);
    $html = $parsedown->text($parsed['body']);

    // Apply site-specific HTML processing (defined in functions.site.php)
    $html = processPostHtml($html);

    // Extract title from first h1 and decode HTML entities
    $title = '';
    if (preg_match('/<h1>([^<]+)<\/h1>/', $html, $matches)) {
        $title = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Parse date
    $dateString = $parsed['frontmatter']['date'] ?? '';
    $timestamp = parseDate($dateString);

    // Parse tags
    $tagString = $parsed['frontmatter']['tags'] ?? '';
    $tags = parseTags($tagString);

    // Generate slug
    $slug = postSlug($title);

    return [
        'title' => $title,
        'slug' => $slug,
        'html' => $html,
        'date' => $timestamp,
        'dateFormatted' => formatDate($timestamp),
        'tags' => $tags,
        'filepath' => $filepath
    ];
}

/**
 * Load all posts, sorted by date descending
 */
function loadAllPosts($includeDrafts = false) {
    $posts = [];
    $files = glob(POSTS_DIR . '/*.md');

    foreach ($files as $file) {
        $filename = basename($file);

        // Skip drafts unless explicitly included
        if (!$includeDrafts && strpos($filename, '_') === 0) {
            continue;
        }

        $post = loadPost($file);
        if ($post) {
            $posts[] = $post;
        }
    }

    // Sort by date descending
    usort($posts, function($a, $b) {
        return $b['date'] - $a['date'];
    });

    return $posts;
}

/**
 * Get paginated posts
 */
function getPaginatedPosts($page = 1, $perPage = POSTS_PER_PAGE) {
    $allPosts = loadAllPosts();
    $total = count($allPosts);
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));

    $offset = ($page - 1) * $perPage;
    $posts = array_slice($allPosts, $offset, $perPage);

    return [
        'posts' => $posts,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'total' => $total,
        'hasNext' => $page < $totalPages,
        'hasPrev' => $page > 1
    ];
}

/**
 * Find post by slug
 */
function findPostBySlug($slug) {
    $posts = loadAllPosts();

    foreach ($posts as $index => $post) {
        if ($post['slug'] === $slug) {
            return [
                'post' => $post,
                'prev' => $posts[$index + 1] ?? null,
                'next' => $posts[$index - 1] ?? null
            ];
        }
    }

    return null;
}

/**
 * Get posts by tag
 */
function getPostsByTag($tagSlug) {
    $posts = loadAllPosts();
    $filtered = [];
    $tagName = '';

    foreach ($posts as $post) {
        foreach ($post['tags'] as $tag) {
            if (tagSlug($tag) === $tagSlug) {
                $filtered[] = $post;
                if (empty($tagName)) {
                    $tagName = $tag;
                }
                break;
            }
        }
    }

    return [
        'posts' => $filtered,
        'tagName' => $tagName
    ];
}

/**
 * Get all unique tags with counts
 */
function getAllTags() {
    $posts = loadAllPosts();
    $tags = [];

    foreach ($posts as $post) {
        foreach ($post['tags'] as $tag) {
            $slug = tagSlug($tag);
            if (!isset($tags[$slug])) {
                $tags[$slug] = [
                    'name' => $tag,
                    'slug' => $slug,
                    'count' => 0
                ];
            }
            $tags[$slug]['count']++;
        }
    }

    // Sort by count descending
    uasort($tags, function($a, $b) {
        return $b['count'] - $a['count'];
    });

    return $tags;
}

/**
 * Load a static page
 */
function loadPage($name) {
    $filepath = PAGES_DIR . '/' . $name . '.md';

    if (!file_exists($filepath)) {
        return null;
    }

    $content = file_get_contents($filepath);
    $parsedown = new Parsedown();
    $parsedown->setSafeMode(false);

    return $parsedown->text($content);
}

/**
 * HTML escape helper
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Render post meta (date + tags)
 */
function renderPostMeta($post) {
    $html = '<p class="post-meta small">';
    $html .= e($post['dateFormatted']);
    foreach ($post['tags'] as $tag) {
        $html .= '<a href="/tagged/' . e(tagSlug($tag)) . '" class="tag">' . e($tag) . '</a>';
    }
    $html .= '</p>';
    return $html;
}

// =============================================================================
// Embed converters
// =============================================================================

/**
 * Convert YouTube URLs to embeds in HTML content
 * Handles both raw URLs and URLs wrapped in anchor tags by Parsedown
 */
function convertYouTubeEmbeds($html) {
    // Match youtu.be URLs wrapped in anchor tags within paragraphs
    $html = preg_replace(
        '/<p>\s*<a[^>]*href=["\']https?:\/\/youtu\.be\/([a-zA-Z0-9_-]+)["\'][^>]*>[^<]*<\/a>\s*<\/p>/i',
        '<div class="video-embed"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>',
        $html
    );

    // Match youtube.com/watch URLs wrapped in anchor tags within paragraphs
    $html = preg_replace(
        '/<p>\s*<a[^>]*href=["\']https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)[^"\']*["\'][^>]*>[^<]*<\/a>\s*<\/p>/i',
        '<div class="video-embed"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>',
        $html
    );

    // Match raw youtu.be URLs in paragraphs
    $html = preg_replace(
        '/<p>\s*https?:\/\/youtu\.be\/([a-zA-Z0-9_-]+)\s*<\/p>/i',
        '<div class="video-embed"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>',
        $html
    );

    // Match raw youtube.com/watch URLs in paragraphs
    $html = preg_replace(
        '/<p>\s*https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)[^<]*<\/p>/i',
        '<div class="video-embed"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>',
        $html
    );

    return $html;
}

/**
 * Convert Twitter/X URLs to official Twitter embeds
 * Handles: twitter.com, x.com status URLs
 */
function convertTwitterEmbeds($html) {
    // Pattern to match Twitter/X URLs (both raw and anchor-wrapped)
    // Matches: https://twitter.com/user/status/123 or https://x.com/user/status/123
    $pattern = '/<p>\s*(?:<a[^>]*href=")?https?:\/\/(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]+)\/status\/(\d+)[^"<]*(?:"[^>]*>[^<]*<\/a>)?\s*<\/p>/i';

    $html = preg_replace_callback($pattern, function($matches) {
        $username = $matches[1];
        $tweetId = $matches[2];
        $tweetUrl = 'https://twitter.com/' . htmlspecialchars($username) . '/status/' . htmlspecialchars($tweetId);
        // Use Twitter's official blockquote format that widgets.js will transform
        return '<blockquote class="twitter-tweet" data-dnt="true"><a href="' . $tweetUrl . '"></a></blockquote>';
    }, $html);

    return $html;
}

// =============================================================================
// Site-specific extensions
// =============================================================================

// Include site-specific functions if they exist
if (file_exists(__DIR__ . '/functions.site.php')) {
    require_once __DIR__ . '/functions.site.php';
}

// Default post HTML processor (can be overridden in functions.site.php)
// Applies YouTube and Twitter embed conversion by default
if (!function_exists('processPostHtml')) {
    function processPostHtml($html) {
        $html = convertYouTubeEmbeds($html);
        $html = convertTwitterEmbeds($html);
        return $html;
    }
}
