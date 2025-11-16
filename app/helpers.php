<?php

/**
 * Generate URL with optional base path from environment
 * Set BASE_PATH in .env for subdirectory deployments (e.g., /authboard_project)
 */
function url(string $path = ''): string {
    $basePath = rtrim(getenv('BASE_PATH') ?: '', '/');
    $path = '/' . ltrim($path, '/');
    return $basePath . $path;
}
