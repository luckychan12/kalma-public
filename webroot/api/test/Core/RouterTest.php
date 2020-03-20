<?php
declare(strict_types=1);

namespace Kalma\API\Test\Core;

require __DIR__ . '/../../vendor/autoload.php';


use FastRoute\Dispatcher;
use Kalma\Api\Core\Router;
use PHPUnit\Framework\TestCase;


final class TestRouter extends TestCase
{
    public function testCanBeInstantiated() : Router
    {
        $router = new Router();
        $this->assertInstanceOf(
            Router::class,
            $router
        );
        return $router;
    }

    /**
     * @depends testCanBeInstantiated
     * @param Router $router
     */
    public function testProvidesCorrectRouteForValidUri(Router $router) : void
    {
        $expected = array
        (
            'uri' => '/kalma/api/user/login',
            'status' => Dispatcher::FOUND,
            'resource' => 'User',
            'action' => 'login',
            'args' => array(),
            'access_level' => 0,
        );

        $this->assertEquals($expected, $router->getRoute('POST', '/kalma/api/user/login'));
    }

    /**
     * @depends testCanBeInstantiated
     * @param Router $router
     */
    public function testFindsNoRouteForInvalidUri(Router $router) : void
    {
        $expected = array
        (
            'uri' => '/kalma/api/user/DUMMY_ROUTE',
            'status' => Dispatcher::NOT_FOUND,
        );

        $this->assertEquals($expected, $router->getRoute('POST', '/kalma/api/user/DUMMY_ROUTE'));
    }

    /**
     * @depends testCanBeInstantiated
     * @param Router $router
     */
    public function testReturnsAllowedMethodsForInvalidMethod(Router $router) : void
    {
        $expected = array
        (
            'uri' => '/kalma/api/user/login',
            'status' => Dispatcher::METHOD_NOT_ALLOWED,
            'allowed_methods' => ['POST'],
        );

        $this->assertEquals($expected, $router->getRoute('GET', '/kalma/api/user/login'));
    }
}