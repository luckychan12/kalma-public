<?php
/**
 * User resource. Serves data about user entities, facilitates their creation, updation, etc.
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley
 * @category   Kalma
 * @package    Api
 * @subpackage Resource
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 * @version    0.1
 * @since      File available since Pre-Alpha
 */

namespace Kalma\Api\Resource;

use Kalma\Api\Business\UserManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../../vendor/autoload.php';

class User extends Resource
{
    /**
     * Attempt to create a user account. Return a success/failure bool with a message.
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param mixed ...$args
     * @return Response
     */
    public function signup(Request $req, Response $res, ?array $payload, ...$args) : Response
    {
        $am = UserManager::getInstance();
        $body = $req->getParsedBody();
        if ($body)
        {
            $creationResult = $am->createUser($body);
            $res->getBody()->write(json_encode($creationResult));

            if ($creationResult['success'])
            {
                return $res->withStatus(200);
            }
            else
            {
                return $res->withStatus(400);
            }
        }
        else
        {
            $res->getBody()->write(json_encode(array
            (
                'success' => false,
                'message' => 'Malformed request body.',
            )));
            return $res->withStatus(400);
        }
    }

    /**
     * Attempt to log a user in. If successful, return an auth JWT, else return an error message.
     * @param  Request    $req
     * @param  Response   $res
     * @param  array|null $payload Public endpoint, no JWT payload required
     * @param  mixed      ...$args Takes no URI params
     * @return Response
     */
    public function login(Request $req, Response $res, ?array $payload, ...$args) : Response
    {
        $body = $req->getParsedBody();
        if (isset($body['email_address']) && isset($body['password']) && isset($body['client_fingerprint']))
        {
            $um = UserManager::getInstance();
            $verificationResult = $um->verifyCredentials($body['email_address'], $body['password'], $body['client_fingerprint']);

            $verified = $verificationResult['success'];
            if (!$verified)
            {
                $status = $verificationResult['status'] ?? 500;
                unset($verificationResult['status']); // Don't send status in response body
                $res->getBody()->write(json_encode($verificationResult));
                return $res->withStatus($status);
            }

            $user_id = $verificationResult['user_id'];
            $client_fingerprint = $body['client_fingerprint'];
            $session = $um->createSession($user_id, $client_fingerprint);

            if (!$session['success'])
            {
                $res->getBody()->write(json_encode($session));
                return $res->withStatus(500);
            }

            $resBody = $session;
            $resBody['links'] = array
            (
                'account' => "/api/user/$user_id/account",
                'logout'  => "/api/user/$user_id/logout",
            );
            $res->getBody()->write(json_encode($resBody));
            return $res->withStatus(200);
        }

        $res->getBody()->write(json_encode(array
        (
            'success' => false,
            'message' => 'Malformed request body',
        )));
        return $res->withStatus(400);
    }

    /**
     * User REFRESH
     * Take a refresh token, validate it, and return a new access token and refresh token
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param mixed ...$args
     * @return Response
     */
    public function refresh(Request $req, Response $res, ?array $payload, ...$args) : Response
    {
        $body = $req->getParsedBody();
        if (isset($body['refresh_token']) && isset($body['client_fingerprint']))
        {
            $um = UserManager::getInstance();
            $result = $um->refreshSession($body['refresh_token'], $body['client_fingerprint']);
            $status = $result['status'];
            unset($result['status']);
            $res->getBody()->write(json_encode($result));
            return $res->withStatus($status);
        }

        $res->getBody()->write(json_encode(array
        (
            'success' => false,
            'message' => 'Malformed request body.'
        )));
        return $res->withStatus(400);
    }

    /**
     * User READ endpoint
     * Fetch account data for a specific user. Data may only be accessed by a logged in user requesting their own data.
     * @param Request $req
     * @param Response $res
     * @param array $payload
     * @param mixed ...$args Expects exactly one argument, the id of the user to be read
     * @return Response
     */
    public function read(Request $req, Response $res, ?array $payload, ...$args) : Response
    {
        if (count($args) != 1) {
            $res->getBody()->write(json_encode(array
            (
                'success' => false,
                'message' => 'Invalid URL parameters passed.',
            )));
            return $res->withStatus(400);
        }

        if (!isset($payload['sub']) || $args[0] != $payload['sub']) {
            $res->getBody()->write(json_encode(array
            (
                'success' => false,
                'message' => 'The logged in user does not have permission to access the requested user\'s data',
            )));
            return $res->withStatus(401);
        }

        $result = $this->database->fetch
        (
            'SELECT * FROM `user_account` WHERE `user_id` = :user_id',
            array('user_id' => $args[0])
        );

        if ($result['success'])
        {
            $rows = $result['data'];
            if (count($rows) > 0)
            {
                $account_data = $rows[0];
                $res->getBody()->write(json_encode(array
                (
                    'success' => true,
                    'user' => $account_data,
                    'links' => array
                    (
                        'logout' => "/api/user/$args[0]/logout",
                    ),
                )));
                return $res->withStatus(200);
            }
            else {
                $res->getBody()->write(json_encode(array
                (
                    'success' => false,
                    'message' => "Failed to read data for user with ID '$args[0]'. No such user exists.",
                )));
                return $res->withStatus(400);
            }
        }

        $res->getBody()->write(json_encode(array
        (
            'success' => false,
            'message' => "Failed to read data for user with ID '$args[0]'.",
        )));
        return $res->withStatus(400);
    }

    /**
     * User Sign out endpoint
     * Terminate the current session for a given user and client
     * @param Request $req
     * @param Response $res
     * @param array $payload
     * @param mixed ...$args Expects exactly one argument, the id of the user to be read
     * @return Response
     */
    public function logout(Request $req, Response $res, ?array $payload, ...$args) : Response
    {
        if (count($args) != 1) {
            $res->getBody()->write(json_encode(array
            (
                'success' => false,
                'message' => 'Invalid URL parameters passed.',
            )));
            return $res->withStatus(400);
        }

        $res->getBody()->write(json_encode(array
        (
            'success' => true,
            'message' => 'Successfully logged you out. Just kidding, I haven\'t finished implementing this yet.',
        )));
        return $res->withStatus(200);

    }

}