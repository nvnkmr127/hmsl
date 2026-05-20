<?php

$dir = realpath(__DIR__ . '/../tauri/src-tauri/bin/php-runtime');
if (!$dir) {
    die("Directory not found\n");
}

echo "Scanning $dir recursively...\n";

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$phpFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

echo "Found " . count($phpFiles) . " PHP files.\n";

$fixedCount = 0;

foreach ($phpFiles as $filePath) {
    $content = file_get_contents($filePath);
    $original = $content;

    // Fix 1: $var{offset} -> $var[offset]
    $count = 0;
    do {
        $content = preg_replace(
            '/\$([a-zA-Z0-9_\-\>\[\]\'\"]+)\s*\{\s*([^{}]+)\s*\}/',
            '$$1[$2]',
            $content,
            -1,
            $count
        );
    } while ($count > 0);

    // Fix 2: =& new -> = new
    $content = preg_replace('/=\s*&\s*new\s+/', '= new ', $content);

    // Fix 3: Event_Dispatcher getInstance & setNotificationClass static declaration
    if (basename($filePath) === 'Dispatcher.php') {
        $content = str_replace(
            'function &getInstance($name = \'__default\')',
            'public static function &getInstance($name = \'__default\')',
            $content
        );
        $content = str_replace(
            'function setNotificationClass($class)',
            'public static function setNotificationClass($class)',
            $content
        );
    }

    // Fix 4: PEAR_Registry getInstance/etc. static where appropriate (if needed, but let's check dispatcher first)

    if ($content !== $original) {
        file_put_contents($filePath, $content);
        $fixedCount++;
    }
}

echo "Processed files. Fixed $fixedCount files.\n";

// Let's run php -l on all files and see if any syntax errors remain
echo "Running lint check on all files...\n";
$errors = [];
foreach ($phpFiles as $filePath) {
    $output = [];
    $retval = 0;
    exec("c:\\xampp\\php\\php.exe -l " . escapeshellarg($filePath) . " 2>&1", $output, $retval);
    if ($retval !== 0) {
        $errors[] = [
            'file' => $filePath,
            'output' => implode("\n", $output)
        ];
    }
}

if (count($errors) > 0) {
    echo "Found " . count($errors) . " files with syntax errors:\n";
    foreach ($errors as $err) {
        echo "File: {$err['file']}\nError:\n{$err['output']}\n--------------------\n";
    }
} else {
    echo "All PHP files linted successfully! No syntax errors detected.\n";
}
