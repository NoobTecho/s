<?php
header('Content-Type: text/xml; charset=UTF-8');

$baseUrl = 'https://' . $_SERVER['HTTP_HOST'];
$baseDir = __DIR__;

$excludedDirs = ['wp-content', 'wp-includes', 'wp-admin'];
$excludedFiles = [
    '/sitemap.php', '/error.php',
    '/wp-config.php', '/wp-config-sample.php',
    '/wp-load.php', '/wp-cron.php', '/xmlrpc.php',
    '/wp-login.php', '/wp-settings.php', '/wp-trackback.php',
    '/wp-signup.php', '/wp-mail.php', '/wp-links-opml.php',
    '/wp-blog-header.php', '/wp-activate.php', '/readme.html'
];

function getUrls($dir, $baseUrl, $excludedDirs, $excludedFiles) {
    $urls = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isDir()) continue;

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['php', 'html', 'htm'])) continue;

        $relativePath = str_replace('\\', '/', str_replace($dir, '', $file->getPathname()));

        // Skip folder WP
        foreach ($excludedDirs as $excluded) {
            if (strpos($relativePath, '/' . $excluded . '/') !== false) {
                continue 2;
            }
        }

        // Skip file WP core
        if (in_array($relativePath, $excludedFiles)) continue;

        if (basename($relativePath) === 'index.php') {
            $relativePath = dirname($relativePath);
            if ($relativePath === '.' || $relativePath === '') $relativePath = '/';
        }

        if (substr($relativePath, 0, 1) !== '/') {
            $relativePath = '/' . $relativePath;
        }

        $url = htmlspecialchars($baseUrl . str_replace(' ', '%20', $relativePath));

        $urls[] = [
            'loc' => $url,
            'lastmod' => date('Y-m-d', $file->getMTime())
        ];
    }

    usort($urls, fn($a, $b) => strcmp($a['loc'], $b['loc']));
    return $urls;
}

$urls = getUrls($baseDir, $baseUrl, $excludedDirs, $excludedFiles);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
  <url>
    <loc><?= $u['loc'] ?></loc>
    <lastmod><?= $u['lastmod'] ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
<?php endforeach; ?>
</urlset>
