<?php
/**
 * Handles server requests and calls the appropriate resource
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley
 * @category   Kalma
 * @package    Api
 * @subpackage Core
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 * @version    0.1
 * @since      File available since Pre-Alpha
 */

namespace Kalma\Api\Core;

require __DIR__ . '/../../vendor/autoload.php';

use FastRoute\Dispatcher;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;


class FrontController
{

    /**
     * Process a PSR7 Request. Finds the appropriate route and visits it.
     * @param Request $request
     */
    public function dispatchRequest(Request $request) : void
    {
        $router = new Router();
        $uri = $_SERVER['REQUEST_URI'];
        $route = $router->getRoute($request->getMethod(), $uri);

        // Create response
        $responseFactory = new Psr17Factory();
        $response = $responseFactory->createResponse(500); // Default to server error response (if not set by resource)

        switch($route['status'])
        {
            case Dispatcher::NOT_FOUND:
                // 404 Not Found
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(array
                (
                    'message' => $response->getReasonPhrase(),
                )));
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                // 405 Method Not Allowed
                $response = $response->withStatus(405);
                $response->getBody()->write(json_encode(array
                (
                    'message' => $response->getReasonPhrase(),
                    'allowed_methods' => $route['allowed_methods'],
                )));
                break;
            case Dispatcher::FOUND:
                // Route found
                // Pass request to resource to create the response
                $response = $this->visitRoute($request, $response, $route);
                break;
        }

        $this->emitResponse($response);
    }

    /**
     * Instantiate the Resource class for a given route and call the specified action method, if it exists
     * @param Request $request
     * @param Response $response
     * @param array   $route
     * @return Response
     */
    private function visitRoute(Request $request, Response $response, array $route) : Response
    {

        $authResult = Auth::authorize($request, $route['access_level']);
        if ($authResult['success'])
        {
            // Instantiate Resource
            $resourceClass = '\\Kalma\\Api\\Resource\\' . $route['resource'];
            $resource = new $resourceClass();

            // Call Resource action
            $params = $route['args'];
            array_unshift($params, $request, $response, $authResult['payload'] ?? NULL);
            $response = call_user_func_array(array($resource, $route['action']), $params);

            // Dispatch response to client
            return $response;
        }
        else {
            $response->getBody()->write(json_encode($authResult));
            return $response->withStatus(401);
        }
    }

    /**
     * Emit a PSR-7 Response communicating information provided by Resource actions
     * @param Response $response
     */
    private function emitResponse(Response $response) : void
    {
        // Throw error if headers have already been sent
        if (headers_sent())
        {
            throw new RuntimeException('Headers have already been sent. A new response cannot be emitted.');
        }

        // Write status line
        $response_status = sprintf
        (
            "HTTP/%s %s %s",
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($response_status, true);

        // Write headers
        foreach ($response->getHeaders() as $key => $value) {
            $header_line = sprintf
            (
                "%s: %s",
                $key,
                $response->getHeader($key)
            );
            header($header_line, false);
        }

        echo $response->getBody();
        exit(); // Allow no more execution
    }

}