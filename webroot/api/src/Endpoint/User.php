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

namespace Kalma\Api\Endpoint;

use DateTime;
use Kalma\Api\Business\UserManager;
use Kalma\Api\Core\Config;
use Kalma\Api\Response\Exception\InvalidBodyAttributesException;
use Kalma\Api\Response\Exception\ResponseException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Kalma\Api\Response\Response;

require __DIR__ . '/../../vendor/autoload.php';

class User extends Endpoint
{
    /**
     * Return an associative array of links available to a given user
     * @param int $user_id
     * @param array $omit
     * @return array
     */
    public static function getLinks(int $user_id, ...$omit) : array
    {
        $api = Config::get('api_root');
        $links = array(
            'logout' => "$api/user/logout",
            'refresh' => "$api/user/refresh",
            'account' => "$api/user/$user_id/account",
            'targets' => "$api/user/$user_id/account/targets",
            'sleep' => "$api/user/$user_id/sleep",
            'calm' => "$api/user/$user_id/calm",
            'steps' => "$api/user/$user_id/steps",
            'weight' => "$api/user/$user_id/weight",
            'height' => "$api/user/$user_id/height",
        );

        foreach ($omit as $key) {
            if (isset($links[$key])) {
                unset($links[$key]);
            }
        }

        return $links;
    }

    /**
     * Attempt to create a user account. Return a success/failure bool with a message.
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function signup(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        $am = UserManager::getInstance();
        $body = $req->getParsedBody();
        $confirmation_url = $am->createUser($body);
        $res->setBody(array(
            'message' => 'We successfully signed you up. Check your inbox for an email with instructions on how to activate your account.',
            'confirmation_url' => $confirmation_url,
        ));
        return $res;
    }

    /**
     * Verify a confirmation token and, if successful, activate the associated user account.
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function confirm(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        $body = $req->getParsedBody();
        if (!isset($body['confirmation_token'])) {
            throw new InvalidBodyAttributesException("The request body is missing the required 'confirmation_token' attribute.");
        }

        $um = UserManager::getInstance();
        $um->confirmAccount($body['confirmation_token']);
        $res->setBody(array(
            'message' => 'Your account has been activated! You may now log in using the email address and password you signed up with.',
        ));
        return $res;
    }

    /**
     * Attempt to log a user in. If successful, return an auth JWT, else return an error message.
     * @param Request $req
     * @param Response $res
     * @param array|null $payload Public endpoint, no JWT payload required
     * @param array $args Takes no URI params
     * @return Response
     * @throws ResponseException
     */
    public function login(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        $body = $req->getParsedBody();
        foreach(['email_address', 'password', 'client_fingerprint'] as $attribute) {
            if (!isset($body[$attribute])) {
                throw new InvalidBodyAttributesException("The request body is missing the required attribute '$attribute'.");
            }
        }

        $um = UserManager::getInstance();
        $user_id = $um->verifyCredentials($body['email_address'], $body['password']);
        $client_fingerprint = $body['client_fingerprint'];
        $session = $um->createSession($user_id, $client_fingerprint);

        $resBody = $session;
        $resBody['links'] = static::getLinks($user_id);
        $res->setBody($resBody);
        return $res;
    }

    /**
     * User REFRESH
     * Take a refresh token, validate it, and return a new access token and refresh token
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function refresh(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        $body = $req->getParsedBody();

        foreach(['refresh_token', 'client_fingerprint'] as $attribute) {
            if (!isset($body[$attribute])) {
                throw new InvalidBodyAttributesException("The request body is missing the required attribute '$attribute'.");
            }
        }

        $um = UserManager::getInstance();
        [$session, $user_id] = $um->refreshSession($body['refresh_token'], $body['client_fingerprint']);
        $resBody = $session;
        $resBody['links'] = static::getLinks($user_id, 'refresh');
        $res->setBody($resBody);
        return $res;
    }

    /**
     * User READ endpoint
     * Fetch account data for a specific user. Data may only be accessed by a logged in user requesting their own data.
     * @param Request $req
     * @param Response $res
     * @param array $payload
     * @param array $args Expects exactly one argument, the id of the user to be read
     * @return Response
     * @throws ResponseException
     */
    public function read(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        if (!isset($payload['sub']) || intval($args['id']) !== $payload['sub']) {
            throw new ResponseException(403, 2002, 'You do not have permission to access this resource.',
                    'The subject of the access token may not access the requested user\'s data.');
        }

        $rows = $this->database->fetch(
            'SELECT * FROM `user_account` WHERE `user_id` = :user_id',
            array('user_id' => $args['id'])
        );

        if (count($rows) == 0) {
            throw new ResponseException(404, 1500, 'The requested user could not be found');
        }

        $account_data = $rows[0];

        $sessions = $this->database->fetch(
            'SELECT `client_fingerprint`, `created_time`, `expiry_time` FROM `session` WHERE `user_id` = :user_id;',
            array('user_id' => $args['id']),
        );

        $sessions = array_map(function($session) {
            $sql_date_format = 'Y-m-d H:i:s';
            $created_time = DateTime::createFromFormat($sql_date_format, $session['created_time']);
            $expiry_time = DateTime::createFromFormat($sql_date_format, $session['expiry_time']);
            $session['created_time'] = $created_time->format(DATE_ISO8601);
            $session['expiry_time'] = $expiry_time->format(DATE_ISO8601);
            return $session;
        }, $sessions);

        if (count($sessions) > 0) {
            $account_data['sessions'] = $sessions;
        }

        foreach(['sleep', 'calm', 'steps'] as $key) {
            $val = $account_data["{$key}_target"];
            unset($account_data["{$key}_target"]);
            $account_data['targets'][$key] = $val;
            if ($val !== null) {
                if (in_array($key, ['sleep', 'calm'])) {
                    $mins = $val % 60;
                    $hrs = floor($val / 60);
                    $val_string = ($hrs > 0 ? $hrs.'h ' : '') . $mins.'m';
                }
                else {
                    $val_string = $val . '';
                }
            }
            else {
                $val_string = 'No target set';
            }
            $account_data['target_strings'][$key] = $val_string;
        }

        $res->setBody(array(
            'user' => $account_data,
            'links' => static::getLinks($args['id'], 'account'),
        ));

        return $res;
    }

    /**
     * User Sign out endpoint
     * Terminate the current session for a given user and client
     * @param Request $req
     * @param Response $res
     * @param array $payload
     * @param array $args Expects exactly one argument, the id of the user to be read
     * @return Response
     * @throws ResponseException
     */
    public function logout(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        $body = $req->getParsedBody();
        if (!isset($body['client_fingerprint'])) {
            throw new InvalidBodyAttributesException("The request body is missing the required attribute 'client_fingerprint'.");
        }

        $user_id = $payload['sub'];
        $client_fingerprint = $body['client_fingerprint'];

        $this->database->execute(
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

    /**
     * Update the user's goals
     *
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function setTargets(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        if (!isset($payload['sub']) || intval($args['id']) !== $payload['sub']) {
            throw new ResponseException(403, 2002, 'You do not have permission to access this resource.',
                'The subject of the access token may not access the requested user\'s data.');
        }

        $body = $req->getParsedBody();

        if (!isset($body['targets']) || !is_array('targets')) {
            throw new InvalidBodyAttributesException("The 'targets' object is missing or not an object.");
        }

        $targets = $body['targets'];
        $valid_targets = ['sleep', 'calm', 'steps'];

        foreach($targets as $key => $value) {
            if (!in_array($key, $valid_targets)) {
                throw new InvalidBodyAttributesException("The 'targets' object contains the invalid key '$key'");
            }
        }

        $query_sets = implode(",\n", array_map(function($k){
            return "{$k}_target = :$k";
        }, array_keys($targets)));

        $query_params = $targets;
        $query_params['user_id'] = $args['id'];

        $this->database->execute("UPDATE `user` SET $query_sets WHERE `user_id` = :user_id", $query_params);

        $res->setBody(array(
            'message' => 'Updated your target' . (count($targets) > 1 ? 's!' : '!'),
            'links' => static::getLinks($args['id'], 'targets'),
        ));

        return $res;
    }

}