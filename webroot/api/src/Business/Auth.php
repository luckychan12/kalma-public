<?php
/**
 * Validate and Generate JSON Web Tokens
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

namespace Kalma\Api\Business;

require __DIR__ . '/../../vendor/autoload.php';

use DateTime;
use DomainException;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use Kalma\Api\Core\Logger;
use Kalma\Api\Response\Exception\ResponseException;
use Psr\Http\Message\ServerRequestInterface as Request;
use UnexpectedValueException;

class Auth
{
    const ACCESS_PUBLIC = 0;
    const ACCESS_USER = 10;
    const ACCESS_ADMIN = 100;

    /**
     * Read the public SHA256 key from file
     * @return string
     */
    private static function getPublicKey() : string
    {
        return file_get_contents(__DIR__ . '/../../public.key');
    }

    /**
     * Read the private SHA256 key from file
     * @return string
     */
    private static function getPrivateKey() : string
    {
        return file_get_contents(__DIR__ . '/../../private.key');
    }

    /**
     * Check Authorization HTTP header for valid JWT
     * @param Request $request The HTTP Request to be completed
     * @param int $access_level The access level required to complete the request
     * @return array                An array containing the payload of the validated JWT
     * @throws ResponseException
     */
    public static function authorize(Request $request, int $access_level)
    {
        // No authorization required for public endpoints
        if ($access_level == static::ACCESS_PUBLIC)
        {
            return null;
        }

        // If there is no authorization header, deny access
        if (!$request->hasHeader('Authorization') || !$request->getHeader('Authorization'))
        {
            throw new ResponseException(401, 2000, 'You do not have permission to access this resource.', 'No Authorization header was sent.');
        }

        $auth_header = $request->getHeader('authorization')[0];

        // Extract the access token
        if (strtolower(substr($auth_header, 0, 7)) != 'bearer ')
        {
            throw new ResponseException(401, 2001, 'You do not have permission to access this resource.', 'Malformed Authorization header.');
        }


        $access_token = str_ireplace('bearer ', '', $auth_header);

        // Validate the token
        $payload = static::validateSessionJWT($access_token);
        if ($payload != null && isset($payload['sub']))
        {
            $user_id = $payload['sub'];
            $user_access_level = static::getAccessLevel($user_id);
            if ($user_access_level >= $access_level)
            {
                return $payload;
            }
            else
            {
                throw new ResponseException(403, 2200, 'You do not have permission to access this resource.', 'Insufficient privileges to access the requested resource.');
            }
        }

        throw new ResponseException(401, 2100, 'You do not have permission to access this resource.', 'No token subject.');
    }

    /**
     * Decode a session JWT and return the decoded array
     * @param string $token The JWT to validate
     * @return array        The payload of the JWT, or an error message
     * @throws ResponseException
     */
    public static function validateSessionJWT(string $token) : ?array
    {
        try {
            return static::validateGenericJWT($token);
        }
        catch (ExpiredException $e)
        {
            throw new ResponseException(401, 2102, 'Your session has timed out.', 'Access token expired. Login again or refresh auth tokens.');
        }
        catch (BeforeValidException $e)
        {
            throw new ResponseException(401, 2103, 'Your session hasn\'t started yet!', 'Access token not-before claim hasn\'t elapsed. Is the client a robot?');
        }
        catch (UnexpectedValueException | SignatureInvalidException | DomainException $e)
        {
            throw new ResponseException(401, 2104, 'Sorry, we couldn\'t authenticate your request.', 'Unexpected error: ' . $e->getMessage());
        }
    }

    /**
     * Decode a JWT and, if valid, return the decoded array
     * @param string $token
     * @return array|null
     * @throws InvalidArgumentException
     * @throws ExpiredException
     * @throws BeforeValidException
     * @throws ResponseException
     * @throws UnexpectedValueException
     * @throws SignatureInvalidException
     * @throws DomainException
     * @throws ResponseException
     */
    public static function validateGenericJWT(string $token) : ?array
    {
        try {
            $key = static::getPublicKey();

            $decoded = (array) JWT::decode($token, $key, ['RS256']);
            return $decoded ?? null;
        }
        catch (InvalidArgumentException $e)
        {
            Logger::log(Logger::ERROR, 'No public key provided for JWT validation.');
            throw new ResponseException(401, 2101, 'Sorry, we couldn\'t process your request.', 'Public key unavailable.');
        }

    }

    /**
     * Generate and return an R256 encoded JSON Web Token with a given payload
     * @param array $payload
     * @return string
     */
    public static function generateJWT(array $payload) : string
    {
        $key = static::getPrivateKey();
        return JWT::encode($payload, $key, 'RS256');
    }

    /**
     * Generate a short-lifetime, stateful JWT containing a user ID
     * @param int $user_id
     * @return array
     * @throws ResponseException
     */
    public static function generateAccessToken(int $user_id) : array
    {
        $expiry = time() + 15 * 60; // Expires in 15 minutes
        $payload = array(
            'iss' => 'kalma/api',
            'aud' => 'kalma/api',
            'iat' => time(),
            'exp' => $expiry,
            'sub' => $user_id
        );

        try {
            $expiry_timestamp = (new DateTime("@$expiry"))->format(DATE_ISO8601);
        }
        catch (Exception $e) {
            throw new ResponseException(...ResponseException::INVALID_DATE_FORMAT);
        }

        return [static::generateJWT($payload), $expiry_timestamp];
    }

    /**
     * Generate a long-lifetime, stateful JWT containing a session ID
     * @param int $session_id
     * @return array
     * @throws ResponseException
     */
    public static function generateRefreshToken(int $session_id) : array
    {
        $expiry = time() + 28 * 24 * 60 * 60; // Expires in 28 days
        $payload = array(
            'iss' => 'kalma/api',
            'aud' => 'kalma/api',
            'iat' => time(),
            'exp' => $expiry,
            'sid' => $session_id
        );

        try {
            $expiry_timestamp = (new DateTime("@$expiry"))->format(DATE_ISO8601);
        }
        catch (Exception $e) {
            throw new ResponseException(...ResponseException::INVALID_DATE_FORMAT);
        }

        return [static::generateJWT($payload), $expiry_timestamp];
    }

    /**
     * Return the access level of a given user account
     * @param int $user_id
     * @return int
     */
    public static function getAccessLevel(int $user_id) : int
    {
        // TODO: Fetch access level from database record for given user
        return 10;
    }
}