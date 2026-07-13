<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = '1|uVS1dn0iONmm35ylhK3p6l6pAbvCEo0T8GKajFRFe8aab764';
$url = 'https://digichatify.tribebella.com/api/v1/sync/pull?since=0';

try {
    echo "Pulling from server...\n";
    $response = Illuminate\Support\Facades\Http::withoutVerifying()
        ->withToken($token)
        ->get($url);

    if ($response->successful()) {
        $deltas = $response->json('data') ?? $response->json('deltas') ?? [];
        echo "Total deltas returned: " . count($deltas) . "\n";
        
        $counts = [];
        foreach ($deltas as $d) {
            $table = $d['table'] ?? 'unknown';
            $counts[$table] = ($counts[$table] ?? 0) + 1;
        }
        
        print_r($counts);
    } else {
        echo "Fail: " . $response->status() . " - " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
