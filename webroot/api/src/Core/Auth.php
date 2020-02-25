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

namespace Kalma\Api\Core;

require __DIR__ . '/../../vendor/autoload.php';

use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
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
     * @param  Request $request     The HTTP Request to be completed
     * @param  int     $access_level The access level required to complete the request
     * @return array                An array containing the payload of the validated JWT
     */
    public static function authorize(Request $request, int $access_level) : array
    {
        // No authorization required for public endpoints
        if ($access_level == static::ACCESS_PUBLIC)
        {
            return array
            (
                'success' => true,
            );
        }

        // If there is no authorization header, deny access
        if (!$request->hasHeader('authorization'))
        {
            return array
            (
                'success' => false,
                'message' => 'No authorization header given.',
            );
        }

        $authorization_headers = $request->getHeader("authorization");

        // If the authorization header is set and has exactly one value
        if ($authorization_headers == null || count($authorization_headers) != 1)
        {
            return array
            (
                'success' => false,
                'message' => 'Failed to get authorization token.',
            );
        }

        // Extract the access token
        $access_token = str_ireplace('bearer ', '', $authorization_headers[0]);

        // Validate the token
        $res = static::validateJWT($access_token);
        if ($res['success'])
        {
            $payload = $res['payload'];
            if (isset($payload['sub']))
            {
                $user_id = $payload['sub'];
                $user_access_level = static::getAccessLevel($user_id);
                if ($user_access_level >= $access_level)
                {
                    return $res;
                }
            }

            return array
            (
                'success' => false,
                'message' => 'Insufficient privileges to access the request resource.'
            );
        }
        else
        {
                return array
                (
                    'success' => false,
                    'message' => $res['message'],
                );
        }
    }

    /**
     * Decode a JWT and return the decoded array
     * @param  string $token The JWT to validate
     * @return array         The payload of the JWT, or an error message
     */
    public static function validateJWT(string $token) : array
    {
        $key = static::getPublicKey();
        $result = array('success' => false);

        try
        {
            $decoded = (array) JWT::decode($token, $key, ['RS256']);

            $result['success'] = true;
            $result['payload'] = $decoded ?? NULL;
        }
        catch (InvalidArgumentException $e)
        {
            $msg = 'No key provided for JWT validation.';
            Logger::log(Logger::ERROR, $msg);
            $result['message'] = $msg;
        }
        catch (UnexpectedValueException | SignatureInvalidException | BeforeValidException | ExpiredException | DomainException $e)
        {
            $result['message'] = $e->getMessage();
        }

        return $result;
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
     * @return string
     */
    public static function generateAccessToken(int $user_id) : string
    {
        $payload = array(
            'iss' => 'kalma/api',
            'aud' => 'kalma/api',
            'iat' => time(),
            'exp' => time() + 15 * 60, // Expires in 15 minutes
            'sub' => $user_id
        );

        return static::generateJWT($payload);
    }

    /**
     * Generate a long-lifetime, stateful JWT containing a session ID
     * @param int $session_id
     * @return string
     */
    public static function generateRefreshToken(int $session_id) : string
    {
        $payload = array(
            'iss' => 'kalma/api',
            'aud' => 'kalma/api',
            'iat' => time(),
            'exp' => time() + 28 * 24 * 60 * 60, // Expires in 28 days
            'sub' => $session_id
        );

        return static::generateJWT($payload);
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