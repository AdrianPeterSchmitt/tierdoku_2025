<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class AboutPageTest extends TestCase
{
    public function testAboutPageRendersSuccessfully(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/about';
        $_SERVER['HTTP_HOST'] = 'localhost';

        ob_start();

        try {
            require __DIR__ . '/../../public/index.php';
            $output = ob_get_clean();

            $this->assertStringContainsString('Über das Projekt', $output);
            $this->assertStringContainsString('PHP', $output);
            $this->assertStringContainsString('FastRoute', $output);
        } catch (\Exception $e) {
            ob_end_clean();
            $this->fail('AboutPage failed: ' . $e->getMessage());
        }
    }
    protected function setUp(): void
    {
        parent::setUp();

        if (file_exists(__DIR__ . '/../../.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
            $dotenv->load();
        }
    }
}

