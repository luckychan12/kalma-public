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
use Kalma\Api\Core\DatabaseHandler;

class AccountManager
{

    private static AccountManager $instance;

    public static function getInstance() : AccountManager
    {
        if (!isset(self::$instance))
        {
            self::$instance = new AccountManager();
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

        // TEMP
        return array
        (
            'success' => true,
            'message' => 'Almost done! Please check your inbox for a confirmation email, before you can login in. (Not really I haven\' finished implementing this yet pls forgive)'
        );

        // TODO: Complete signup endpoint
        // Attempt to create user record
        // $db = \Kalma\Api\Core\DatabaseHandler::getConnection();
        // $queryResult = $db->fetchAssoc('');

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
                'message' => 'Failed to create user account. The provided password is too weak.'
            );
        }

        if (sizeof($user_data['first_name']) > 50)
        {
            return array
            (
                'success' => false,
                'message' => 'Failed to create user account. The provided first name is too long. Max 50 chars.'
            );
        }

        if (sizeof($user_data['last_name']) > 50)
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
        return preg_match
        (
            '/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[ !"#$%&\'()*+,\-.:;<=>?@[\\\]\^_`{\|}~])[A-Za-z0-9 !"#$%&\'()*+,\-.:;<=>?@[\\\]\^_`{\|}~]{8,}/',
            $password,
        );
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
            $dob = new DateTime($dob_timestamp);
            $now = new DateTime();
            $age = $now->diff($dob);
            return $age->y >= $min_age;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Query the database to check whether a user record exists with matching email address and password
     * @param string $email
     * @param string $pass
     * @param int    $client_fingerprint
     * @return array         Return
     */
    public function verifyCredentials(string $email, string $pass, int $client_fingerprint) : array
    {
        $db = DatabaseHandler::getConnection();
        $result = $db->fetchAssoc
        (
            'SELECT user_id, password_hash, activated FROM `user` WHERE email_address = (:email_address)',
            array('email_address' => $email),
        );

        if (!$result) {
            return array
            (
                'success' => false,
                'message' => 'Database error.',
                'status' => 500,
            );
        }

        if ($result['success'])
        {
            $data = $result['data'];
            if (password_verify($pass, $data['password_hash']))
            {
                if ($data['activated'] == 1)
                {
                    return array
                    (
                        'success' => true,
                        'jwt' => Auth::generateJWT(Auth::ACCESS_USER, $client_fingerprint, $data['user_id']),
                        'user_id' => $data['user_id'],
                    );
                }
                else
                {
                    return array
                    (
                        'success' => false,
                        'message' => 'You must confirm your account before you can log in.',
                    );
                }
            }
            else
            {
                return array
                (
                    'success' => false,
                    'message' => 'Invalid email or password.',
                );
            }
        }

        return array
        (
            'verified' => false,
            'message' => 'Database error',
        );
    }

}