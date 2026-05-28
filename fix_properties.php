<?php
$dir = new RecursiveDirectoryIterator('e:/hmsl/app/Livewire');
$ite = new RecursiveIteratorIterator($dir);
foreach ($ite as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        $content = preg_replace_callback('/public\s+function\s+get([A-Za-z0-9_]+)Property\s*\(\)/', function($matches) {
            $name = lcfirst($matches[1]);
            return "#[\Livewire\Attributes\Computed]\n    public function {$name}()";
        }, $content);

        // handle with return type like `public function getDoctorProperty(): ?Doctor`
        $content = preg_replace_callback('/public\s+function\s+get([A-Za-z0-9_]+)Property\s*\(\)\s*:\s*([A-Za-z0-9_?\\\\]+)/', function($matches) {
            $name = lcfirst($matches[1]);
            $returnType = $matches[2];
            return "#[\Livewire\Attributes\Computed]\n    public function {$name}(): {$returnType}";
        }, $content);
        
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated: " . $file->getPathname() . "\n";
        }
    }
}
echo "Done.\n";
