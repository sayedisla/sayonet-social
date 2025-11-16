<?php
declare(strict_types=1);

// Simple PSR-4-ish autoloader for the App\ namespace (no Composer required)
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    // FIXED: point to the lowercase 'app' directory (your project uses app/)
    $baseDir = __DIR__ . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return; // not our namespace
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// tiny .env loader (reads .env into getenv and $_ENV)
// adjust path if your .env is somewhere else
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$key, $val] = array_map('trim', explode('=', $line, 2) + [1 => null]);
        if ($key && $val !== null) {
            putenv("$key=$val");
            $_ENV[$key] = $val;
        }
    }
}

// ----- APP BOOT -----
use App\Core\Router;
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PostController;

// start session
Session::start();

// compute APP_BASE from SCRIPT_NAME so routes and view helpers can use same base
// Example: if script is "/metro_wb_lab-main/index.php" -> APP_BASE = "/metro_wb_lab-main"
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$appBase = rtrim(dirname($scriptName), '/\\');
if ($appBase === '/' || $appBase === '\\') $appBase = '';
// expose constant for views
if (!defined('APP_BASE')) define('APP_BASE', $appBase);

/**
 * Helper to register both root and base-prefixed routes.
 * This avoids 404 when forms generate paths with/without the app base.
 */
function registerRouteVariants(Router $router, string $method, string $path, callable $callback): void {
    // normalize path start
    $p = '/' . ltrim($path, '/');
    // register plain variant (root)
    if (strtoupper($method) === 'GET') $router->get($p, $callback);
    else $router->post($p, $callback);

    // if APP_BASE is set, register base-prefixed variant too
    if (defined('APP_BASE') && APP_BASE !== '') {
        $prefixed = rtrim(APP_BASE, '/') . ($p === '/' ? '' : $p);
        if (strtoupper($method) === 'GET') $router->get($prefixed, $callback);
        else $router->post($prefixed, $callback);

        // also include trailing-slash variants to be extra safe
        if ($p !== '/') {
            $pslash = $p . '/';
            if (strtoupper($method) === 'GET') $router->get($pslash, $callback);
            else $router->post($pslash, $callback);
            $prefixedSlash = $prefixed . '/';
            if (strtoupper($method) === 'GET') $router->get($prefixedSlash, $callback);
            else $router->post($prefixedSlash, $callback);
        }
    }
}

$router = new Router();
$auth = new AuthController();
$dash = new DashboardController();
$post = new PostController();

// AUTH routes
registerRouteVariants($router, 'GET', '/', fn() => $auth->showLogin());
registerRouteVariants($router, 'GET', '/login', fn() => $auth->showLogin());
registerRouteVariants($router, 'GET', '/register', fn() => $auth->showRegister());
registerRouteVariants($router, 'POST', '/register', fn() => $auth->register());
registerRouteVariants($router, 'POST', '/login', fn() => $auth->login());
registerRouteVariants($router, 'GET', '/logout', fn() => $auth->logout());

// DASHBOARD / MAIL
registerRouteVariants($router, 'GET', '/dashboard', fn() => $dash->index());
registerRouteVariants($router, 'GET', '/test-mail', fn() => $dash->testMail());

// SOCIAL FEATURES (posts)
registerRouteVariants($router, 'GET', '/feed', fn() => $post->index());
registerRouteVariants($router, 'POST', '/posts/store', fn() => $post->store());
registerRouteVariants($router, 'POST', '/posts/like', fn() => $post->like());
registerRouteVariants($router, 'POST', '/posts/comment', fn() => $post->comment());
registerRouteVariants($router, 'POST', '/posts/share', fn() => $post->share());

// MISSING routes added: delete / follow / unfollow
registerRouteVariants($router, 'POST', '/posts/delete', fn() => $post->delete());
registerRouteVariants($router, 'POST', '/follow', fn() => $post->follow());
registerRouteVariants($router, 'POST', '/unfollow', fn() => $post->unfollow());

// DISPATCH
$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
