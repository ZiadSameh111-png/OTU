<?php

/**
 * This script cleans up frontend-related files when converting to an API-only backend
 */

echo "Starting frontend cleanup process...\n";

// Directories to be removed
$directoriesToRemove = [
    'resources/views',
    'resources/js',
    'resources/css',
    'resources/sass',
    'public/js',
    'public/css',
    'public/build',
    'public/images',
    'public/fonts'
];

// Additional files that might need to be removed
$filesToRemove = [
    'public/mix-manifest.json',
    'public/favicon.ico',
    'resources/views.zip', // Backup the views first if needed
    'webpack.mix.js'
];

// Function to recursively remove a directory
function removeDirectory($dir) {
    if (!file_exists($dir)) {
        echo "Directory not found: $dir\n";
        return;
    }
    
    // Create a backup of views before removing
    if ($dir == 'resources/views') {
        echo "Creating backup of views directory...\n";
        $zip = new ZipArchive();
        $zipFile = 'resources/views.zip';
        
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen(realpath($dir)) + 1);
                    
                    $zip->addFile($filePath, $relativePath);
                    echo "Added to backup: " . $relativePath . "\n";
                }
            }
            
            $zip->close();
            echo "Backup created at $zipFile\n";
        } else {
            echo "Failed to create backup. Stopping.\n";
            return;
        }
    }
    
    // Now remove the directory
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    
    rmdir($dir);
    echo "Removed directory: $dir\n";
}

// Ask for confirmation
echo "This will remove all frontend-related files. Are you sure? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) != 'y') {
    echo "Operation cancelled.\n";
    exit;
}

// Process directories
foreach ($directoriesToRemove as $dir) {
    if (file_exists($dir)) {
        echo "Processing directory: $dir\n";
        removeDirectory($dir);
    } else {
        echo "Directory not found, skipping: $dir\n";
    }
}

// Process individual files
foreach ($filesToRemove as $file) {
    if (file_exists($file)) {
        echo "Removing file: $file\n";
        unlink($file);
    } else {
        echo "File not found, skipping: $file\n";
    }
}

// Create an empty views directory to prevent errors
if (!file_exists('resources/views')) {
    mkdir('resources/views', 0755, true);
    echo "Created empty views directory for compatibility\n";
}

// Create a simple welcome view for fallback
$welcomeContent = <<<EOT
<!DOCTYPE html>
<html>
<head>
    <title>API Backend</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; text-align: center; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>API Backend</h1>
        <p>This is an API-only backend application. Please use the API endpoints.</p>
    </div>
</body>
</html>
EOT;

file_put_contents('resources/views/welcome.blade.php', $welcomeContent);
echo "Created minimal welcome page\n";

echo "Frontend cleanup complete!\n";

// Additional instructions
echo "-------------------------------------------------------------\n";
echo "NEXT STEPS:\n";
echo "1. Remove view-related code from controllers\n";
echo "2. Update RouteServiceProvider to disable web routes\n";
echo "3. Update app configurations in config/app.php\n";
echo "4. Consider disabling CSRF for API routes\n";
echo "-------------------------------------------------------------\n"; 