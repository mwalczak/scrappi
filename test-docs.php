#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

$kernel = new Kernel('dev', true);
$request = Request::create('/api/docs.json', 'GET');
$request->headers->set('Accept', 'application/json');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
$content = $response->getContent();
$data = json_decode($content, true);

if (isset($data['paths'])) {
    echo "\nAvailable paths:\n";
    foreach (array_keys($data['paths']) as $path) {
        echo "  - $path\n";
        if (strpos($path, 'health') !== false) {
            echo "    ✓ HEALTH ENDPOINT FOUND!\n";
            echo "    Methods: " . implode(', ', array_keys($data['paths'][$path])) . "\n";
            if (isset($data['paths'][$path]['get']['summary'])) {
                echo "    Summary: " . $data['paths'][$path]['get']['summary'] . "\n";
            }
        }
    }
} else {
    echo "No paths found in API documentation\n";
    echo "Response: " . substr($content, 0, 500) . "\n";
}

$kernel->terminate($request, $response);

