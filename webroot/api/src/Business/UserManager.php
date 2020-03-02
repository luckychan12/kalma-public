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
 */

namespace Kalma\Api\Business;

use DateTime;
use Exception;
use Kalma\Api\Core\Config;
use Kalma\Api\Core\DatabaseHandler;
use Kalma\Api\Core\Logger;
use Kalma\Api\Response\Exception\ResponseException;

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

    /**
     * Create a user record in the database
     * @param array $user_data
     * @throws ResponseException
     */
    public function createUser(array $user_data) : void
    {
        $this->validateUserData($user_data);

        $db = DatabaseHandler::getConnection();

        $rows = $db->fetch('SELECT user_id FROM `user` WHERE `email_address` = :email_address',
                array('email_address' => $user_data['email_address']));

        if (count($rows) > 0)
        {
            throw new ResponseException(400, 1200, 'This email address is taken.');
        }

        $queryParams = array
        (
            'email_address' => $user_data['email_address'],
            'password_hash' => password_hash($user_data['password'], PASSWORD_BCRYPT),
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'date_of_birth' => date('y-m-d', $user_data['date_of_birth']),
        );

        $rows = $db->fetch
        (
            'CALL `create_user` (:email_address, :password_hash, :first_name, :last_name, :date_of_birth)',
            $queryParams
        );

        $data = $rows[0];
        if (isset($data['error']))
        {
            throw new ResponseException(500, 3100, 'Oops! Something went wrong creating your account.', 'An error has occurred whilst calling the `create_user` procedure.');
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
        }
    }

    /**
     * Validate each field required to create a new user
     * @param $user_data
     * @throws ResponseException
     */
    private function validateUserData($user_data) : void
    {
        $required_fields = array('email_address', 'password', 'first_name', 'last_name', 'date_of_birth');
        foreach ($required_fields as $field)
        {
            if (!isset($user_data[$field]))
            {
                throw new ResponseException(400, 1100, 'Some required fields have been left blank.', "Field '$field' is required and missing.");
            }
        }

        if (!$this->validateEmail($user_data['email_address']))
        {
            throw new ResponseException(400, 1101, 'One or more of the form fields isn\'t valid.', 'The provided email address is invalid.');
        }

        if (!$this->validatePassword($user_data['password']))
        {
            throw new ResponseException(400, 1101, 'One or more of the form fields isn\'t valid.', 'The provided password is too weak or contains invalid characters.');
        }

        if (strlen($user_data['first_name']) > 50)
        {
            throw new ResponseException(400, 1101, 'One or more of the form fields isn\'t valid.', 'The provided first name exceeds 50 characters.');
        }

        if (strlen($user_data['last_name']) > 50)
        {
            throw new ResponseException(400, 1101, 'One or more of the form fields isn\'t valid.', 'The provided last name exceeds 50 characters.');
        }

        if (!$this->validateAge($user_data['date_of_birth']))
        {
            throw new ResponseException(400, 1101, 'One or more of the form fields isn\'t valid.', 'The provided date of birth is too recent.');
        }
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
     * @return int          The user ID of the verified user
     * @throws ResponseException
     */
    public function verifyCredentials(string $email, string $pass) : int
    {
        $db = DatabaseHandler::getConnection();
        $rows = $db->fetch
        (
            'SELECT user_id, password_hash, activated FROM `user` WHERE email_address = (:email_address)',
            array('email_address' => $email),
        );

        if (count($rows) === 0)
        {
            throw new ResponseException(400, 1201, 'No account exists with this email address.');
        }

        $data = $rows[0];
        if (!password_verify($pass, $data['password_hash']))
        {
            throw new ResponseException(400, 1202, 'Invalid email address or password.');
        }

        if (!$data['activated'])
        {
            throw new ResponseException(403, 1203, 'You must confirm your account before you can log in.');
        }

        return $data['user_id'];
    }

    /**
     * Create a session for a given user and client
     * @param int $user_id The ID of the user starting a session
     * @param string $client_fingerprint A unique identifier for the user's client
     * @return array
     * @throws ResponseException
     */
    public function createSession(int $user_id, string $client_fingerprint) : array
    {
        $db = DatabaseHandler::getConnection();
        $rows = $db->fetch
        (
            'CALL `create_session` (:user_id, :client_fingerprint)',
            array('user_id' => $user_id, "client_fingerprint" => $client_fingerprint),
        );

        $data = $rows[0];
        $session_id = $data['session_id'];
        [$access_token, $access_expiry] = Auth::generateAccessToken($user_id);
        [$refresh_token, $refresh_expiry] = Auth::generateRefreshToken($session_id);

        return array
        (
            'access_token' => $access_token,
            'access_expiry' => $access_expiry,
            'refresh_token' => $refresh_token,
            'refresh_expiry' => $refresh_expiry,
        );
    }

    /**
     * Validate a refresh token. If valid, return a new set of tokens
     * @param string $refresh_token
     * @param string $client_fingerprint
     * @return array
     * @throws ResponseException
     */
    public function refreshSession(string $refresh_token, string $client_fingerprint) : array
    {

        $payload = Auth::validateJWT($refresh_token);

        if (!isset($payload['sid']))
        {
            throw new ResponseException(400, 2105, 'Oops! Something went wrong refreshing your session.',
                    'Refresh token contains no session id.');
        }

        $session_id = $payload['sid'];
        $db = DatabaseHandler::getConnection();
        $rows = $db->fetch(
            'SELECT `user_id`, `client_fingerprint` FROM `session` WHERE `session`.`session_id` = :session_id;',
            array('session_id' => $session_id),
        );

        // If no records are returned, the session ID is invalid
        if (count($rows) == 0)
        {
            throw new ResponseException(403, 2106, 'Your session has timed out. You\'ll need to log back in to continue.',
                    'Invalid refresh token session id claim.');
        }

        // If the client fingerprints don't match, the refresh token is being used on a different client to the target
        $data = $rows[0];
        if ($data['client_fingerprint'] != $client_fingerprint)
        {
            throw new ResponseException(400, 2107, 'Oops! Something went wrong refreshing your session.',
                    'The requested session belongs to a different client.');
        }

        // If the refresh token is valid, issue new access and refresh tokens
        $session = static::createSession($data['user_id'], $client_fingerprint);
        return $session;
    }

}