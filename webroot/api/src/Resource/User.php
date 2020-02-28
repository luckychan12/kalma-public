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
 */

namespace Kalma\Api\Resource;

use Kalma\Api\Business\UserManager;
use Kalma\Api\Response\Exception\ResponseException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Kalma\Api\Response\Response;

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
     * @throws ResponseException
     */
    public function signup(Request $req, Response $res, ?array $payload, ...$args) : Response
    {
        $am = UserManager::getInstance();
        $body = $req->getParsedBody();
        $creationResult = $am->createUser($body);
        $res->setBody($creationResult);
        return $res;
    }

    /**
     * Attempt to log a user in. If successful, return an auth JWT, else return an error message.
     * @param Request $req
     * @param Response $res
     * @param array|null $payload Public endpoint, no JWT payload required
     * @param mixed ...$args Takes no URI params
     * @return Response
     * @throws ResponseException
     */
    public function login(Request $req, Response $res, ?array $payload, ...$args) : Response
    {
        $body = $req->getParsedBody();
        if (!isset($body['email_address']) || !isset($body['password']) || !isset($body['client_fingerprint']))
        {
            throw new ResponseException(400, 1002, 'Oops! Something went wrong accessing this resource.', 'Invalid request attributes.');
        }

        $um = UserManager::getInstance();
        $user_id = $um->verifyCredentials($body['email_address'], $body['password']);
        $client_fingerprint = $body['client_fingerprint'];
        $session = $um->createSession($user_id, $client_fingerprint);

        $resBody = $session;
        $resBody['links'] = array
        (
            'account' => $this->api_root . "/user/$user_id/account",
            'logout'  => $this->api_root . "/user/logout",
        );
        $res->setBody($resBody);
        return $res;
    }

    /**
     * User REFRESH
     * Take a refresh token, validate it, and return a new access token and refresh token
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param mixed ...$args
     * @return Response
     * @throws ResponseException
     */
    public function refresh(Request $req, Response $res, ?array $payload, ...$args) : Response
    {
        $body = $req->getParsedBody();

        if (!isset($body['refresh_token']) || !isset($body['client_fingerprint']))
        {
            throw new ResponseException(400, 1002, 'Oops! Something went wrong accessing this resource.', 'Invalid request attributes.');
        }

        $um = UserManager::getInstance();
        $result = $um->refreshSession($body['refresh_token'], $body['client_fingerprint']);
        $res->setBody($result);
        return $res;
    }

    /**
     * User READ endpoint
     * Fetch account data for a specific user. Data may only be accessed by a logged in user requesting their own data.
     * @param Request $req
     * @param Response $res
     * @param array $payload
     * @param mixed ...$args Expects exactly one argument, the id of the user to be read
     * @return Response
     * @throws ResponseException
     */
    public function read(Request $req, Response $res, ?array $payload, ...$args) : Response
    {

        if (!isset($payload['sub']) || $args[0] != $payload['sub']) {
            throw new ResponseException(403, 2002, 'You do not have permission to access this resource.',
                    'The subject of the access token may not access the requested user\'s data.');
        }

        $rows = $this->database->fetch
        (
            'SELECT * FROM `user_account` WHERE `user_id` = :user_id',
            array('user_id' => $args[0])
        );

        if (count($rows) == 0)
        {
            throw new ResponseException(404, 1500, 'The requested user could not be found');
        }

        $account_data = $rows[0];

        $sessions = $this->database->fetch
        (
            'SELECT `client_fingerprint`, `created_time`, `expiry_time` FROM `session` WHERE `user_id` = :user_id;',
            array('user_id' => $args[0]),
        );

        if (count($sessions) > 0)
        {
            $account_data['sessions'] = $sessions;
        }

        $res->setBody(array(
            'user' => $account_data,
            'links' => array
            (
                'logout' => $this->api_root . '/user/logout',
            ),
        ));

        return $res;
    }

    /**
     * User Sign out endpoint
     * Terminate the current session for a given user and client
     * @param Request $req
     * @param Response $res
     * @param array $payload
     * @param mixed ...$args Expects exactly one argument, the id of the user to be read
     * @return Response
     * @throws ResponseException
     */
    public function logout(Request $req, Response $res, ?array $payload, ...$args) : Response
    {
        $body = $req->getParsedBody();
        if (!isset($body['client_fingerprint']))
        {
            throw new ResponseException(400, 1002, 'Oops! Something went wrong accessing this resource.', 'Invalid request attributes.');
        }

        $user_id = $payload['sub'];
        $client_fingerprint = $body['client_fingerprint'];

        $this->database->execute
        (
            'DELETE FROM `session` 
                 WHERE `session`.`user_id` = :user_id 
                 AND `session`.`client_fingerprint` = :client_fingerprint;',
            array('user_id' => $user_id, 'client_fingerprint' => $client_fingerprint),
        );

        $res->setBody(array(
            'message' => 'Successfully logged you out.',
        ));
        return $res;
    }

}