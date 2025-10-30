<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class RouterTest extends TestCase
{
    public function testRouteMatches(): void
    {
        $dispatcher = $this->createDispatcher();

        $routeInfo = $dispatcher->dispatch('GET', '/');

        $this->assertEquals(Dispatcher::FOUND, $routeInfo[0]);
        $this->assertEquals('home', $routeInfo[1]);
    }

    public function testRouteNotFound(): void
    {
        $dispatcher = $this->createDispatcher();

        $routeInfo = $dispatcher->dispatch('GET', '/non-existent');

        $this->assertEquals(Dispatcher::NOT_FOUND, $routeInfo[0]);
    }

    public function testAboutRouteMatches(): void
    {
        $dispatcher = $this->createDispatcher();

        $routeInfo = $dispatcher->dispatch('GET', '/about');

        $this->assertEquals(Dispatcher::FOUND, $routeInfo[0]);
        $this->assertEquals('about', $routeInfo[1]);
    }
    private function createDispatcher(): Dispatcher
    {
        return \FastRoute\simpleDispatcher(function (RouteCollector $r) {
            $r->addRoute('GET', '/', 'home');
            $r->addRoute('GET', '/about', 'about');
        });
    }
}

