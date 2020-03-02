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
 */

namespace Kalma\Api\Core;

require __DIR__ . '/../../vendor/autoload.php';

use Kalma\Api\Business\Auth;
use Kalma\Api\Response\Exception\ResponseException;
use Kalma\Api\Response\JsonErrorResponse;
use Kalma\Api\Response\JsonResponse;
use Kalma\Api\Response\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Exception;


class FrontController
{

    /**
     * Process a PSR7 Request. Finds the appropriate route and visits it.
     * @param Request $request
     * @throws Exception
     */
    public function dispatchRequest(Request $request) : void
    {
        $router = new Router();
        $uri = $_SERVER['REQUEST_URI'];
        $method = $request->getMethod();

        // Strip API root
        $api_root = Config::get("api_root");
        $uri = substr($uri, strlen($api_root));

        // Strip URI query string
        if (false !== $pos = strpos($uri, '?'))
        {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        try
        {
            $route = $router->getRoute($method, $uri);
            $response = new JsonResponse($uri);
            $request = $request->withParsedBody(json_decode(file_get_contents('php://input'), true));
            $response = $this->visitRoute($request, $response, $route);
            $response->setStatus(200);
        }
        catch (ResponseException $re) {
            $response = new JsonErrorResponse($uri, $re);
        }
        catch (Exception $e)
        {
            Logger::log(Logger::ERROR, $e->getMessage());
            $re = new ResponseException(500, 4000, 'Oops! Something went wrong processing your request.');
            $response = new JsonErrorResponse($uri, $re);
        }

        $this->emitResponse($response);
    }

    /**
     * Instantiate the Resource class for a given route and call the specified action method, if it exists
     * @param Request $request
     * @param Response $response
     * @param array $route
     * @return Response
     * @throws ResponseException
     */
    private function visitRoute(Request $request, Response $response, array $route) : Response
    {
        $authPayload = Auth::authorize($request, $route['access_level']);

        // Instantiate Resource
        $resourceClass = '\\Kalma\\Api\\Resource\\' . $route['resource'];
        $resource = new $resourceClass();

        // Call Resource action
        $params = $route['args'];
        array_unshift($params, $request, $response, $authPayload);
        $response = call_user_func_array(array($resource, $route['action']), $params);

        // Dispatch response to client
        return $response;
    }

    /**
     * Emit a PSR-7 Response communicating information provided by Resource actions
     * @param Response $res
     */
    private function emitResponse(Response $res) : void
    {
        $response = $res->getResponse();

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
        foreach ($response->getHeaders() as $key => $value)
        {
            $header_line = sprintf
            (
                "%s: %s",
                $key,
                implode(", ", $response->getHeader($key))
            );
            header($header_line);
        }

        echo $response->getBody();
        exit(); // Allow no more execution
    }

}