<?php

/**
 * Application Entry Point
 */

// Load environment variables
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/helpers.php';

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use FastRoute\RouteCollector;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Load .env file
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Set timezone from environment variable (default: Europe/Berlin for Germany)
$timezone = $_ENV['APP_TIMEZONE'] ?? 'Europe/Berlin';
date_default_timezone_set($timezone);

// Setup Logger
$logger = new Logger('app');
$logFile = __DIR__ . '/../storage/logs/app.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}
$logger->pushHandler(new StreamHandler($logFile, $_ENV['LOG_LEVEL'] ?? 'debug'));

// Setup Illuminate Database
$capsule = new Capsule();

$driver = $_ENV['DB_CONNECTION'] ?? 'sqlite';

if ($driver === 'sqlite') {
    $capsule->addConnection([
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../' . ($_ENV['DB_DATABASE'] ?? 'database/database.sqlite'),
        'prefix' => '',
    ]);
} else {
    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_DATABASE'] ?? '',
        'username' => $_ENV['DB_USERNAME'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ]);
}

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Start session with security settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '0'); // Set to '1' when using HTTPS
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// Route dispatcher
$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $routes = require __DIR__ . '/../config/routes.php';

    foreach ($routes as $method => $routeGroup) {
        foreach ($routeGroup as $path => $handler) {
            $r->addRoute($method, $path, $handler);
        }
    }
});

// Fetch method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Dispatch route
$routeInfo = $dispatcher->dispatch($method, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo view('errors/404');
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo view('errors/405');
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        // Simple dependency injection container
        $container = require __DIR__ . '/../config/container.php';

        // Define protected routes that require authentication
        $protectedRoutes = ['/kremation', '/herkunft', '/standort', '/users', '/statistics', '/notifications'];
        $isProtectedRoute = in_array($uri, $protectedRoutes) || str_starts_with($uri, '/kremation/') || str_starts_with($uri, '/users/') || str_starts_with($uri, '/notifications/');
        
        // Check authentication for protected routes
        if ($isProtectedRoute) {
            $authService = $container->get(\App\Services\AuthService::class);
            $currentUser = $authService->currentUser();
            
            if (!$currentUser) {
                // Not authenticated, redirect to login
                redirect('/login');
                exit;
            }
            
            // Set user in request for controllers
            $_REQUEST['_user'] = $currentUser;
        }

        try {
            // Call controller method
            if (is_array($handler)) {
                [$class, $method] = $handler;

                // Resolve controller from container
                $controller = $container->get($class);
                echo $controller->$method($vars);
            } elseif (is_callable($handler)) {
                echo $handler($vars);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            $logger->error($e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'uri' => $uri ?? 'unknown',
                'vars' => $vars ?? []
            ]);

            // Always show detailed error for debugging (remove in production)
            echo '<pre style="background:#000;color:#0f0;padding:20px;font-family:monospace;">';
            echo '<strong style="color:#f00;">ERROR:</strong> ' . htmlspecialchars($e->getMessage()) . "\n\n";
            echo '<strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n";
            echo '<strong>URI:</strong> ' . htmlspecialchars($uri ?? 'unknown') . "\n";
            echo '<strong>Vars:</strong> ' . print_r($vars ?? [], true) . "\n\n";
            echo '<strong>Trace:</strong>\n' . htmlspecialchars($e->getTraceAsString());
            echo '</pre>';
        }
        break;
}
