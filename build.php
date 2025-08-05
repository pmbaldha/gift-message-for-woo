<?php
/**
 * Build script for Gift Message for WooCommerce plugin
 * Creates a WordPress.org compliant zip file
 */

// Colors for output
define('COLOR_RED', "\033[0;31m");
define('COLOR_GREEN', "\033[0;32m");
define('COLOR_YELLOW', "\033[1;33m");
define('COLOR_NC', "\033[0m");

// Plugin info
$plugin_slug = 'gift-message-for-woo';
$plugin_dir = __DIR__;
$build_dir = $plugin_dir . '/build';
$dist_dir = $plugin_dir . '/dist';

echo COLOR_GREEN . "Building {$plugin_slug} plugin..." . COLOR_NC . "\n";

// Clean up any existing build/dist directories
echo "Cleaning up old builds...\n";
if (is_dir($build_dir)) {
    deleteDirectory($build_dir);
}
if (is_dir($dist_dir)) {
    deleteDirectory($dist_dir);
}

// Create directories
mkdir($build_dir, 0755, true);
mkdir($dist_dir, 0755, true);

// Files and directories to exclude
$exclude = [
    '.git',
    '.github',
    '.gitignore',
    '.gitattributes',
    '.editorconfig',
    '.distignore',
    '.phpcs.xml',
    '.phpcs.xml.dist',
    'phpunit.xml',
    'phpunit.xml.dist',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'webpack.config.js',
    'Gruntfile.js',
    'gulpfile.js',
    '.eslintrc',
    '.eslintrc.js',
    '.eslintrc.json',
    '.stylelintrc',
    '.stylelintrc.json',
    'CLAUDE.md',
    'build.sh',
    'build.bat',
    'build.php',
    'build',
    'dist',
    'tests',
    'node_modules',
    'vendor/composer',
    '.idea',
    '.vscode',
    '.wordpress-org',
    'assets-wp-repo',
    '*.log',
    '*.lock',
    '.DS_Store',
    'Thumbs.db'
];

// Copy plugin files to build directory
echo "Copying plugin files...\n";
copyDirectory($plugin_dir, $build_dir . '/' . $plugin_slug, $exclude);

// Get version from main plugin file
$version = '1.0.0';
$plugin_file = $build_dir . '/' . $plugin_slug . '/' . $plugin_slug . '.php';
if (file_exists($plugin_file)) {
    $plugin_content = file_get_contents($plugin_file);
    if (preg_match('/Version:\s*([0-9.]+)/', $plugin_content, $matches)) {
        $version = $matches[1];
    }
}

// Create zip file
echo "Creating zip file...\n";
$zip_name = "{$plugin_slug}-{$version}.zip";
$zip_path = $dist_dir . '/' . $zip_name;

$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($build_dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($build_dir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    
    $zip->close();
    
    // Clean up build directory
    deleteDirectory($build_dir);
    
    // File size info
    $size = formatBytes(filesize($zip_path));
    echo COLOR_GREEN . "✓ Build complete!" . COLOR_NC . "\n";
    echo COLOR_GREEN . "Plugin zip created: " . COLOR_YELLOW . $zip_path . COLOR_NC . " ({$size})\n\n";
    
    // List contents for verification
    echo COLOR_YELLOW . "Zip contents:" . COLOR_NC . "\n";
    $zip = new ZipArchive();
    if ($zip->open($zip_path) === TRUE) {
        $count = min(20, $zip->numFiles);
        for ($i = 0; $i < $count; $i++) {
            echo "  " . $zip->getNameIndex($i) . "\n";
        }
        if ($zip->numFiles > 20) {
            echo "  ... and " . ($zip->numFiles - 20) . " more files\n";
        }
        $zip->close();
    }
} else {
    echo COLOR_RED . "✗ Build failed!" . COLOR_NC . "\n";
    exit(1);
}

/**
 * Recursively copy directory
 */
function copyDirectory($src, $dst, $exclude = []) {
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        
        // Check if file/directory should be excluded
        $skip = false;
        foreach ($exclude as $pattern) {
            if (fnmatch($pattern, $file) || $file == $pattern) {
                $skip = true;
                break;
            }
        }
        
        if ($skip) {
            continue;
        }
        
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        
        if (is_dir($srcPath)) {
            // Skip excluded directories
            if (!in_array($file, $exclude)) {
                copyDirectory($srcPath, $dstPath, $exclude);
            }
        } else {
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}

/**
 * Recursively delete directory
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}