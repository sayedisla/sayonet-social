<?php
namespace App\Core;

/**
 * Tiny router for simple PHP apps.
 *
 * - Safely handles missing method arrays (avoids PHP notices).
 * - Normalizes paths (removes query string, collapses multiple slashes).
 * - Supports GET/POST registration via ->get() / ->post().
 * - When dispatching: if callback returns a string, it is echoed.
 *   If callback already handled output/redirects, that's fine too.
 * - When a route isn't found, shows a helpful 404 and lists available routes.
 */
class Router {
    /** @var array<string,array<string,callable>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
        'PATCH' => [],
        'HEAD' => []
    ];

    /**
     * Register a GET route.
     */
    public function get(string $path, callable $callback): void {
        $path = $this->normalizePath($path);
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, callable $callback): void {
        $path = $this->normalizePath($path);
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Normalize a route or requested path:
     * - Ensure it starts with a single slash
     * - Remove trailing slash (except for root '/')
     * - Collapse duplicate slashes
     */
    private function normalizePath(string $path): string {
        if ($path === '') return '/';
        // remove query string if present
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        // collapse consecutive slashes
        $path = preg_replace('#/+#', '/', $path);
        // ensure leading slash
        if ($path[0] !== '/') $path = '/' . $path;
        // remove trailing slash unless root
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }
        return $path;
    }

    /**
     * Dispatch request to matching route.
     *
     * @param string $uri    The full request URI (e.g. $_SERVER['REQUEST_URI'])
     * @param string $method The HTTP method (e.g. $_SERVER['REQUEST_METHOD'])
     */
    public function dispatch(string $uri, string $method): void {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $this->normalizePath($path);

        // If the method array isn't present, treat as no routes for that method.
        $routesForMethod = $this->routes[$method] ?? [];

        $callback = $routesForMethod[$path] ?? null;

        if (!$callback) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1>";
            echo "<p>Requested: <strong>$method $path</strong></p>";
            echo "<details><summary>Available routes</summary><pre>";
            // Pretty-print available routes grouped by method
            foreach ($this->routes as $m => $routes) {
                echo "[$m]\n";
                foreach ($routes as $rp => $_cb) {
                    echo "  $rp\n";
                }
            }
            echo "</pre></details>";
            return;
        }

        // Call the callback. Callbacks may do header() redirects and/or echo output.
        // If a callback returns a non-null string, echo it.
        try {
            $result = call_user_func($callback);
            if (is_string($result)) {
                echo $result;
            }
            // if callback returned something else (null, int, array), we don't try to json_encode it automatically.
        } catch (\Throwable $e) {
            // Basic error handling to avoid fatal errors bubbling to the user
            http_response_code(500);
            error_log('Router dispatch error: ' . $e->getMessage());
            echo "<h1>500 Internal Server Error</h1>";
            echo "<p>There was an error while dispatching the route.</p>";
            if (defined('APP_DEBUG') && APP_DEBUG) {
                echo "<pre>" . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . "</pre>";
            }
        }
    }

    /**
     * Convenience redirect helper
     */
    public function redirect(string $path)
    {
        // if path looks relative, normalize; otherwise assume absolute URL fine
        $location = $this->normalizePath($path);
        header("Location: $location");
        exit;
    }
}
