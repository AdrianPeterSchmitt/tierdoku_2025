<?php

if (!function_exists('view')) {
    /**
     * Helper function to render views
     *
     * @param string $template
     * @param array<string, mixed> $data
     * @return string
     */
    function view(string $template, array $data = []): string
    {
        extract($data);

        // Get full path to view
        $basePath = dirname(__DIR__) . '/resources/views/';
        $viewPath = $basePath . $template . '.php';

        if (!file_exists($viewPath)) {
            return "View not found: {$template}";
        }

        // Set base path for relative includes in views
        $GLOBALS['viewBasePath'] = $basePath;

        ob_start();
        require $viewPath;
        $output = ob_get_clean();

        if ($output === false) {
            return '';
        }

        return $output;
    }
}

if (!function_exists('redirect')) {
    /**
     * Helper function for redirects
     *
     * @param string $path
     * @return void
     */
    function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate CSRF token
     *
     * @return string
     */
    function csrf_token(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verify CSRF token
     *
     * @param string $token
     * @return bool
     */
    function csrf_verify(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('now')) {
    /**
     * Get current date/time
     *
     * @return \Carbon\Carbon|\Illuminate\Support\Carbon
     */
    function now()
    {
        return \Carbon\Carbon::now();
    }
}

if (!function_exists('app_timezone')) {
    /**
     * Get the configured application timezone
     *
     * @return string
     */
    function app_timezone(): string
    {
        return $_ENV['APP_TIMEZONE'] ?? 'Europe/Berlin';
    }
}
