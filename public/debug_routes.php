<?php
// public/debug_routes.php
// Drop this file into public/ and open it in your browser to see runtime info.
// Remove this file after debugging.

echo "<h1>AuthBoard Debug — Routes & Environment</h1>";
echo "<p><strong>Accessed at:</strong> " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') . "</p>";
echo "<pre style='background:#fafafa;border:1px solid #eee;padding:12px;'>";

// Basic server vars
$keys = [
    'REQUEST_URI','REQUEST_METHOD','SCRIPT_NAME','SCRIPT_FILENAME',
    'PHP_SELF','DOCUMENT_ROOT','HTTP_HOST','SERVER_NAME','SERVER_PORT'
];
foreach ($keys as $k) {
    printf("%-20s : %s\n", $k, $_SERVER[$k] ?? '<unset>');
}

// Show effective form action path examples based on SCRIPT_NAME (so you can compare to what forms produce)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($scriptDir === '/' || $scriptDir === '\\') $scriptDir = '';
$basePath = preg_replace('#/index\.php$#', '', $scriptDir);
echo "\nComputed basePath: " . ($basePath === '' ? '<root>' : $basePath) . "\n";

// Show example endpoints (what the app registers)
$endpoints = [
    '/feed','/posts/store','/posts/like','/posts/comment','/posts/share','/posts/delete','/follow','/unfollow'
];
echo "\nRegistered endpoints (example variants):\n";
foreach ($endpoints as $e) {
    $plain = $e === '/' ? '/' : rtrim('/' . ltrim($e, '/'), '/');
    $prefixed = $basePath === '' ? $plain : rtrim($basePath, '/') . $plain;
    printf(" - %s\n", $plain);
    if ($prefixed !== $plain) printf("   -> %s\n", $prefixed);
}

// If you performed a form submit that 404s, copy the 'Requested: POST ...' from your 404 page here
echo "\n\nIf you see a 404 when submitting, paste the exact 'Requested: ...' line here so I can match it.\n";

echo "</pre>";
echo "<p>Now try: 1) open your feed page; 2) click Follow; 3) if a 404 appears copy the <strong>Requested</strong> line and paste here in chat.</p>";
