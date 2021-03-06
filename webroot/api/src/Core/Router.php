<?php
/**
 * Parses URIs to assign the appropriate Endpoint
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley (fergus.bentley@gmail.com)
 * @category   Kalma
 * @package    Api
 * @subpackage Core
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 */

namespace Kalma\Api\Core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Kalma\Api\Business\Auth;
use Kalma\Api\Response\Exception\ResponseException;

require __DIR__ . '/../../vendor/autoload.php';

class Router
{
    private Dispatcher $dispatcher;

    public function __construct()
    {
        // Define routes
        // Handlers follow the format:
        //     array( 'Endpoint' , 'action' [, ACCESS_LEVEL] )
        // Where: "Endpoint" is the name of a class extending Endpoint
        //        "action" is the name of a method belonging to the Endpoint, which will be called to get a response
        //        "ACCESS_LEVEL" is an optional integer indicating the access level required to access the endpoint
        $this->dispatcher = simpleDispatcher(function(RouteCollector $root)
        {
            // Operations relating to users & data associated with users
            // /api/user/...
            $root->addGroup('/user', function(RouteCollector $user) {

                // User account & session management operations
                $user->addRoute('POST', '/signup',          ['User', 'signup',  Auth::ACCESS_PUBLIC]);
                $user->addRoute('POST', '/confirm',         ['User', 'confirm', Auth::ACCESS_PUBLIC]);
                $user->addRoute('POST', '/login',           ['User', 'login',   Auth::ACCESS_PUBLIC]);
                $user->addRoute('POST', '/refresh',         ['User', 'refresh', Auth::ACCESS_PUBLIC]);
                $user->addRoute('POST', '/logout',          ['User', 'logout',  Auth::ACCESS_USER]);

                // Operations relating to a specific user, referenced by ID
                // /api/user/{id}/...
                $user->addGroup('/{id:\d+}', function(RouteCollector $account) {

                    // User account endpoint
                    $account->addRoute('GET', '/account',         ['User', 'read',     Auth::ACCESS_USER]);
                    $account->addRoute('PUT', '/account/targets', ['User', 'setTargets', Auth::ACCESS_USER]);

                    // Sleep data CRUD operations
                    $account->addRoute('GET',    '/sleep',  ['SleepPeriod', 'read',   Auth::ACCESS_USER]);
                    $account->addRoute('POST',   '/sleep',  ['SleepPeriod', 'create', Auth::ACCESS_USER]);
                    $account->addRoute('PUT',    '/sleep',  ['SleepPeriod', 'update', Auth::ACCESS_USER]);
                    $account->addRoute('DELETE', '/sleep',  ['SleepPeriod', 'delete', Auth::ACCESS_USER]);

                    // Calm data CRUD operations
                    $account->addRoute('GET',    '/calm',   ['CalmPeriod', 'read',   Auth::ACCESS_USER]);
                    $account->addRoute('POST',   '/calm',   ['CalmPeriod', 'create', Auth::ACCESS_USER]);
                    $account->addRoute('PUT',    '/calm',   ['CalmPeriod', 'update', Auth::ACCESS_USER]);
                    $account->addRoute('DELETE', '/calm',   ['CalmPeriod', 'delete', Auth::ACCESS_USER]);

                    // Daily steps CRUD operations
                    $account->addRoute('GET',    '/steps',   ['StepsDaily', 'read',   Auth::ACCESS_USER]);
                    $account->addRoute('POST',   '/steps',   ['StepsDaily', 'create', Auth::ACCESS_USER]);
                    $account->addRoute('PUT',    '/steps',   ['StepsDaily', 'update', Auth::ACCESS_USER]);
                    $account->addRoute('DELETE', '/steps',   ['StepsDaily', 'delete', Auth::ACCESS_USER]);

                    // Weight logging CRUD operations
                    $account->addRoute('GET',    '/weight',   ['WeightLog', 'read',   Auth::ACCESS_USER]);
                    $account->addRoute('POST',   '/weight',   ['WeightLog', 'create', Auth::ACCESS_USER]);
                    $account->addRoute('PUT',    '/weight',   ['WeightLog', 'update', Auth::ACCESS_USER]);
                    $account->addRoute('DELETE', '/weight',   ['WeightLog', 'delete', Auth::ACCESS_USER]);

                    // Height logging CRUD operations
                    $account->addRoute('GET',    '/height',   ['HeightLog', 'read',   Auth::ACCESS_USER]);
                    $account->addRoute('POST',   '/height',   ['HeightLog', 'create', Auth::ACCESS_USER]);
                    $account->addRoute('PUT',    '/height',   ['HeightLog', 'update', Auth::ACCESS_USER]);
                    $account->addRoute('DELETE', '/height',   ['HeightLog', 'delete', Auth::ACCESS_USER]);
                });

            });

        });
    }

    /**
     * Select the appropriate route for a request URI
     * Returns an array with the following structure:
     * array (
     *     'uri' => 'modified/uri'              // The given URI, as modified to be passed to the dispatcher
     *     'status' => 0..2,                    // 0: NOT_FOUND, 1: METHOD_NOT_ALLOWED, 2: FOUND
     *     'endpoint' => 'EndpointClassName',   // (Optional) The name of the Endpoint class to call the action on
     *     'action' => 'actionMethodName',      // (Optional) The name of the public method to call in the Endpoint class
     *     'allowed_methods' => ['GET'...]      // (Optional) An array of all allowed HTTP methods for this route
     * )
     *
     * @param string $method
     * @param string $uri    The request URI to find a route for
     * @return array         A 2+ element array containing
     * @throws ResponseException
     */
    public function getRoute(string $method, string $uri) : array
    {
        // Fetch route
        $routeInfo = $this->dispatcher->dispatch($method, $uri);
        $status = $routeInfo[0];

        // Format route according to status
        switch($status)
        {
            case Dispatcher::NOT_FOUND:
                throw new ResponseException(404, 1404, 'We couldn\'t find the resource you requested.', 'Not found.');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new ResponseException(405, 1405, 'We couldn\'t access the resource you requested.',
                        'Method not allowed. Allowed Methods: ' . json_encode($routeInfo[1]));
                break;
            case Dispatcher::FOUND:
            default:
                $handler = $routeInfo[1];
                return array(
                    'endpoint' => $handler[0],
                    'action' => $handler[1],
                    'args' => $routeInfo[2],
                    'access_level' => $handler[2] ?? Auth::ACCESS_PUBLIC,
                );
                break;
        }
    }

}