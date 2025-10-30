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
