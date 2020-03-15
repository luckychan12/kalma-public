<?php
/**
 * An abstract CRUD Resource for tracked user data
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

use Kalma\Api\Business\Auth;
use Kalma\Api\Response\Exception\ResponseException;
use Kalma\Api\Response\Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';

abstract class DataResource extends Resource
{
    /**
     * CREATE a new database entry from request parameters
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     */
    public abstract function _create(Request $req, Response $res, ?array $payload, array $args) : Response;

    /**
     * READ an existing database entry
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     */
    public abstract function _read(Request $req, Response $res, ?array $payload, array $args) : Response;

    /**
     * UPDATE one or more columns for a given entry
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     */
    public abstract function _update(Request $req, Response $res, ?array $payload, array $args) : Response;

    /**
     * DELETE a column
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     */
    public abstract function _delete(Request $req, Response $res, ?array $payload, array $args) : Response;

    /**
     * Wrapper for _create method - implements functionality common to all data resources
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function create(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        $this->validate_user_id($payload, $args);
        return $this->_create($req, $res, $payload, $args);
    }

    /**
     * Wrapper for _read method - implements functionality common to all data resources
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function read(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        $this->validate_user_id($payload, $args);
        return $this->_read($req, $res, $payload, ...$args);
    }

    /**
     * Wrapper for _update method - implements functionality common to all data resources
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param mixed array $args
     * @return Response
     * @throws ResponseException
     */
    public function update(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        $this->validate_user_id($payload, $args);
        return $this->_update($req, $res, $payload, ...$args);
    }

    /**
     * Wrapper for _delete method - implements functionality common to all data resources
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function delete(Request $req, Response $res, ?array $payload, array $args) : Response
    {
        $this->validate_user_id($payload, $args);
        return $this->_delete($req, $res, $payload, $args);
    }

    /**
     * Ensure that the logged-in user has permission to access the requested resource
     * @param array $payload
     * @param array $args
     * @throws ResponseException
     */
    private function validate_user_id(array $payload, array $args) : void
    {
        $resource_user_id = intval($args['id']);
        $requester_id = $payload['sub'];
        $requester_access_level = Auth::getAccessLevel($requester_id);
        $is_admin = $requester_access_level >= Auth::ACCESS_ADMIN;
        $is_self = $resource_user_id === $requester_id;
        if (!$is_admin && !$is_self)
        {
            throw new ResponseException(403, 2002, 'The subject of the access token may not access the requested user\'s data.', 'This resource can only be accessed by the user it concerns.');
        }
    }
}