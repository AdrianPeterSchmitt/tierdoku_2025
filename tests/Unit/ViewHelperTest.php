<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ViewHelperTest extends TestCase
{
    public function testViewFunctionExists(): void
    {
        $this->assertTrue(function_exists('view'));
    }

    public function testViewRendersBasicTemplate(): void
    {
        $result = view('home', ['title' => 'Test', 'env' => 'test']);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Test', $result);
    }

    public function testViewHandlesMissingTemplate(): void
    {
        $result = view('non-existent-view', []);

        $this->assertStringContainsString('View not found', $result);
    }
    protected function setUp(): void
    {
        parent::setUp();

        // Load helpers
        require __DIR__ . '/../../app/helpers.php';
    }
}
