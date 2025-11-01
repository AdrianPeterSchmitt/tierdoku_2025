<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class HomePageTest extends TestCase
{
    public function testHomePageRendersSuccessfully(): void
    {
        // Simulate request
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';

        // Capture output
        ob_start();

        try {
            require __DIR__ . '/../../public/index.php';
            $output = ob_get_clean();

            // Assertions
            $this->assertStringContainsString('Dokumentation der anonymen Tiere', $output);
            $this->assertStringContainsString('Willkommen', $output);
            $this->assertStringContainsString('APP_ENV', $output);
        } catch (\Exception $e) {
            ob_end_clean();
            $this->fail('HomePage failed to render: ' . $e->getMessage());
        }
    }

    public function testHomePageContainsNavigation(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';

        ob_start();

        try {
            require __DIR__ . '/../../public/index.php';
            $output = ob_get_clean();

            $this->assertStringContainsString('Home', $output);
            $this->assertStringContainsString('Über uns', $output);
        } catch (\Exception $e) {
            ob_end_clean();
            $this->fail('Navigation not found: ' . $e->getMessage());
        }
    }
    protected function setUp(): void
    {
        parent::setUp();

        // Load environment
        if (file_exists(__DIR__ . '/../../.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
            $dotenv->load();
        }
    }
}
