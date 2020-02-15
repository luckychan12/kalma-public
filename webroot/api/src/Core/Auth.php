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
    const ACCESS_ADMIN = 20;

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
     * @param  int     $accessLevel The access level required to complete the request
     * @return array                An array containing the payload of the validated JWT
     */
    public static function authorize(Request $request, int $accessLevel) : array
    {
        if ($accessLevel == static::ACCESS_PUBLIC)
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

        $authorizationHeaders = $request->getHeader("authorization");

        // If the authorization header is set and has exactly one value
        if ($authorizationHeaders == null || count($authorizationHeaders) != 1)
        {
            return array
            (
                'success' => false,
                'message' => 'Failed to get authorization token.',
            );
        }

        $authToken = str_ireplace('bearer ', '', $authorizationHeaders[0]);
        $res = static::validateJWT($authToken);

        if ($res['success'])
        {
            if ($res['access_level'] >= $accessLevel)
            {
                return $res;
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
            $result['access_level'] = $decoded['access_level'] ?? 0;
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

    public static function generateJWT(int $access_level, int $client_fingerprint, int $user_id = null) : string
    {
        $payload = array(
            'iss' => 'localhost',
            'aud' => '*',
            'iat' => time(),
            'nbf' => time(),

            'access_level' => $access_level,
            'client_fingerprint' => $client_fingerprint
        );

        if ($user_id != null)
        {
            $payload['user_id'] = $user_id;
        }

        $key = static::getPrivateKey();

        return JWT::encode($payload, $key, 'RS256');
    }
}