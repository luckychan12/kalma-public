<?php
/**
 * Business logic utility functions for validating user data, creating users, etc.
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley
 * @category   Kalma
 * @package    Api
 * @subpackage Business
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 * @version    0.1
 * @since      File available since Pre-Alpha
 */

namespace Kalma\Api\Business;

use DateTime;
use Exception;
use Kalma\Api\Core\Auth;
use Kalma\Api\Core\Config;
use Kalma\Api\Core\DatabaseHandler;
use Kalma\Api\Core\Logger;

class UserManager
{

    private static UserManager $instance;

    public static function getInstance() : UserManager
    {
        if (!isset(self::$instance))
        {
            self::$instance = new UserManager();
        }

        return self::$instance;
    }

    public function createUser(array $user_data) : array
    {
        $validationResult = $this->validateUserData($user_data);
        // If data is invalid, return error message
        if (!$validationResult['success'])
        {
            return $validationResult;
        }

        $db = DatabaseHandler::getConnection();

        $queryParams = array
        (
            'email_address' => $user_data['email_address'],
            'password_hash' => password_hash($user_data['password'], PASSWORD_BCRYPT),
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'date_of_birth' => date('y-m-d', $user_data['date_of_birth']),
        );

        $queryResult = $db->fetch
        (
            'CALL `create_user` (:email_address, :password_hash, :first_name, :last_name, :date_of_birth)',
            $queryParams
        );

        if ($queryResult['success'])
        {
            $data = $queryResult['data'][0];
            if (isset($data['error']))
            {
                return array
                (
                    'success' => false,
                    'message' => $data['error'],
                );
            }
            else
            {
                if (Config::get('mail_enabled'))
                {
                    $confirmation_payload = array
                    (
                        'iss' => 'kalma',
                        'aud' => '*',
                        'iat' => time(),
                        'nbf' => time() + 10,

                        'user_id' => $data['user_id'],
                    );

                    $confirmation_jwt = Auth::generateJWT($confirmation_payload);
                    $url = Config::get('site_root') . Config::get('api_root');
                    $confirmation_link = "$url/user/confirm/?confirmation=" . $confirmation_jwt;

                    $to = $user_data['email_address'];
                    $subject = 'Confirm Your Account';
                    $content = file_get_contents(__DIR__ . '/../templates/confirmation_email.html');
                    $message = str_replace('{{link}}',  $confirmation_link, $content);
                    $headers = 'From: ';
                    mail($to, $subject, $message, $headers);
                }

                return array
                (
                    'success' => true,
                    'message' => 'Your account has been created, and a confirmation email has been sent to the email address you supplied.'
                );

            }
        }


        // TEMP
        return array
        (
            'success' => false,
            'message' => 'Failed to create user account.',
        );

    }

    private function validateUserData($user_data)
    {
        $required_fields = array('email_address', 'password', 'first_name', 'last_name', 'date_of_birth');
        foreach ($required_fields as $field)
        {
            if (!isset($user_data[$field]))
            {
                return array
                (
                    'success' => false,
                    'message' => "Failed to create user account. Field '$field' is required and missing."
                );
            }
        }

        if (!$this->validateEmail($user_data['email_address']))
        {
            return array
            (
                'success' => false,
                'message' => 'Failed to create user account. The provided email address is invalid.'
            );
        }

        if (!$this->validatePassword($user_data['password']))
        {
            return array
            (
                'success' => false,
                'message' => 'Failed to create user account. The provided password is too weak or contains invalid characters.'
            );
        }

        if (strlen($user_data['first_name']) > 50)
        {
            return array
            (
                'success' => false,
                'message' => 'Failed to create user account. The provided first name is too long. Max 50 chars.'
            );
        }

        if (strlen($user_data['last_name']) > 50)
        {
            return array
            (
                'success' => false,
                'message' => 'Failed to create user account. The provided first name is too long. Max 50 chars.'
            );
        }

        if (!$this->validateAge($user_data['date_of_birth']))
        {
            return array
            (
                'success' => false,
                'message' => 'Failed to create user account. You must be at least 16 years old to register an account.'
            );
        }

        return array('success' => true);
    }

    /**
     * Return true if an email address follows the correct syntax
     * @param string $email
     * @return bool
     */
    private function validateEmail(string $email) : bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Return true if a password meets the following requirements:
     *   - Contains at least one uppercase character
     *   - Contains at least one lowercase character
     *   - Contains at least one digit 0-9,
     *   - Contains at least one special character
     *   - Is at least 8 characters in length
     * @param string $password
     * @return bool
     */
    private function validatePassword(string $password) : bool
    {
        $match = preg_match
        (
            '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[ !"#$%&\'()*+,\-.:;<=>?@\[\]\\\^_`{|}~])[A-Za-z0-9 !"#$%&\'()*+,\-.:;<=>?@\[\]\\\^_`{|}~]{8,}$/',
            $password,
        );
        return $match == 1;
    }

    /**
     * Return true if the user meets the age requirement, else false;
     * @param  int  $dob_timestamp The user's date of birth in seconds since the Unix epoch
     * @return bool
     */
    private function validateAge(int $dob_timestamp) : bool
    {
        $min_age = 16;
        try {
            $dob = new DateTime("@$dob_timestamp");
            $now = new DateTime();
            $age = $now->diff($dob);
            return $age->y >= $min_age;
        } catch (Exception $e) {
            Logger::log(Logger::ERROR, "Failed to parse date timestamp '$dob_timestamp' in AccountManager::validateAge()");
            return false;
        }
    }

    /**
     * Query the database to check whether a user record exists with matching email address and password
     * @param string $email
     * @param string $pass
     * @return array         Return
     */
    public function verifyCredentials(string $email, string $pass) : array
    {
        $db = DatabaseHandler::getConnection();
        $result = $db->fetch
        (
            'SELECT user_id, password_hash, activated FROM `user` WHERE email_address = (:email_address)',
            array('email_address' => $email),
        );

        if ($result['success'])
        {
            $rows = $result['data'];
            if (count($rows) > 0)
            {
                $data = $rows[0];
                if (password_verify($pass, $data['password_hash']))
                {
                    if ($data['activated'] == 1)
                    {
                        return array
                        (
                            'success' => true,
                            'user_id' => $data['user_id'],
                        );
                    }
                    else
                    {
                        return array
                        (
                            'success' => false,
                            'message' => 'You must confirm your account before you can log in.',
                            'status' => 403,
                        );
                    }
                }
                else
                {
                    return array
                    (
                        'success' => false,
                        'message' => 'Incorrect email or password.',
                        'status' => 401,
                    );
                }
            }
            else
            {
                return array
                (
                    'success' => false,
                    'message' => 'No user exists with this email address.',
                    'status' => 400,
                );
            }
        }
        else
         {
            return array
            (
                'success' => false,
                'message' => 'Database error.',
                'status' => 500,
            );
        }
    }

    /**
     * Create a session for a given user and client
     * @param int    $user_id            The ID of the user starting a session
     * @param int    $client_fingerprint A unique identifier for the user's client
     * @return array
     */
    public function createSession(int $user_id, int $client_fingerprint) : array
    {
        $db = DatabaseHandler::getConnection();
        $result = $db->fetch
        (
            'CALL `create_session` (:user_id, :client_fingerprint)',
            array('user_id' => $user_id, "client_fingerprint" => $client_fingerprint),
        );

        if ($result['success'])
        {
            $rows = $result['data'];
            if (count($rows) > 0)
            {
                $data = $rows[0];
                $session_id = $data['session_id'];
                $access_token = Auth::generateAccessToken($user_id);
                $refresh_token = Auth::generateRefreshToken($session_id);
                return array
                (
                    'success' => true,
                    'access_token' => $access_token,
                    'refresh_token' => $refresh_token
                );
            }
        }

        return array
        (
            'success' => false,
            'message' => 'A database error has occurred.',
        );
    }

    /**
     * Validate a refresh token. If valid, return a new set of tokens
     * @param string $refresh_token
     * @param int $client_fingerprint
     * @return array
     */
    public function refreshSession(string $refresh_token, int $client_fingerprint) : array
    {

        $validationResult = Auth::validateJWT($refresh_token);

        if (!$validationResult['success'])
        {
            return array
            (
                'success' => false,
                'status' => 400,
                'message' => $validationResult['message'],
            );
        }

        $payload = $validationResult['payload'];

        if (!isset($payload['sid']))
        {
            return array
            (
                'success' => false,
                'status' => 400,
                'message' => 'Invalid refresh token. No session id given.',
            );
        }

        $session_id = $payload['sid'];

        $db = DatabaseHandler::getConnection();
        $result = $db->fetch(
            'SELECT `user_id`, `client_fingerprint` FROM `session` WHERE `session`.`session_id` = :session_id;',
            array('session_id' => $session_id),
        );

        if (!$result['success'])
        {
            return array
            (
                'success' => false,
                'status' => 500,
                'message' => 'A database error has occurred.',
            );
        }

        // If no records are returned, the session ID is invalid
        $rows = $result['data'];
        if (count($rows) == 0)
        {
            return array
            (
                'success' => false,
                'status' => 400,
                'message' => 'Refresh token has no valid session ID.',
            );
        }

        // If the client fingerprints don't match, the refresh token is being used on a different client to the target
        $data = $rows[0];
        if ($data['client_fingerprint'] != $client_fingerprint)
        {
            return array
            (
                'success' => false,
                'status' => 400,
                'message' => 'The requested session belongs to a different client.',
            );
        }

        // If the refresh token is valid, issue new access and refresh tokens
        $res = static::createSession($data['user_id'], $client_fingerprint);
        $res['status'] = $res['success'] ? 200 : 500;
        return $res;
    }

}