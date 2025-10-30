<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testTrueIsTrue(): void
    {
        $this->assertTrue(true);
    }

    public function testArrayHasKey(): void
    {
        $array = ['key' => 'value'];
        $this->assertArrayHasKey('key', $array);
    }
}
