<?php
/**
 * Parses URIs to assign the appropriate resource
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley (fergus.bentley@gmail.com)
 * @category   Kalma
 * @package    Api
 * @subpackage Core
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 * @version    0.1
 * @since      File available since Pre-Alpha
 */

namespace Kalma\Api\Core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

require __DIR__ . '/../../vendor/autoload.php';

class Router
{
    private Dispatcher $dispatcher;

    public function __construct()
    {
        // Define routes
        // Handlers follow the format:
        //     array( 'Resource' , 'action' [, ACCESS_LEVEL] )
        // Where: "Resource" is the name of a class extending Resource
        //        "action" is the name of a method belonging to the Resource, which will be called to get a response
        //        "ACCESS_LEVEL" is an optional integer indicating the access level required to access the resource
        $this->dispatcher = simpleDispatcher(function(RouteCollector $root)
        {
            $root->addGroup('/kalma/api', function(RouteCollector $group)
            {
                $group->addRoute('POST', '/user/signup', ['User', 'signup', Auth::ACCESS_PUBLIC]);
                $group->addRoute('POST', '/user/login', ['User', 'login', Auth::ACCESS_PUBLIC]);
                $group->addRoute('POST', '/user/{id:\d+}/logout', ['User', 'logout', Auth::ACCESS_USER]);
                $group->addRoute('GET', '/user/{id:\d+}/account', ['User', 'read', Auth::ACCESS_USER]);
            });
        });
    }

    /**
     * Select the appropriate route for a request URI
     * Returns an array with the following structure:
     * array (
     *     'uri' => 'modified/uri'              // The given URI, as modified to be passed to the dispatcher
     *     'status' => 0..2,                    // 0: NOT_FOUND, 1: METHOD_NOT_ALLOWED, 2: FOUND
     *     'resource' => 'ResourceClassName',   // (Optional) The name of the Resource class to call the action on
     *     'action' => 'actionMethodName',      // (Optional) The name of the public method to call in the Resource class
     *     'allowed_methods' => ['GET'...]      // (Optional) An array of all allowed HTTP methods for this route
     * )
     *
     * @param  string $method
     * @param  string $uri    The request URI to find a route for
     * @return array          A 2+ element array containing
     */
    public function getRoute(string $method, string $uri) : array
    {
        // Strip URI query string
        if (false !== $pos = strpos($uri, '?'))
        {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        // Fetch route
        $routeInfo = $this->dispatcher->dispatch($method, $uri);
        $route = array
        (
            'uri' => $uri,
            'status' => $routeInfo[0],
        );

        // Format route according to status
        if ($route['status'] == Dispatcher::METHOD_NOT_ALLOWED)
        {
            $route['allowed_methods'] = $routeInfo[1];
        }
        elseif ($route['status'] == Dispatcher::FOUND)
        {
            $handler = $routeInfo[1];
            $route['resource'] = $handler[0];
            $route['action'] = $handler[1];
            $route['args'] = $routeInfo[2];
            $route['access_level'] = $handler[2] ?? 0;
        }
        return $route;
    }

}