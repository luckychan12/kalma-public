<?php
/**
 * Control Database connections
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley (fergus.bentley@gmail.com)
 * @category   Kalma
 * @package    Api
 * @subpackage Core
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 * @version    0.1
 * @since      File available since Pre-Alpha
 */

namespace Kalma\Api\Core;

use Kalma\Api\Response\Exception\ResponseException;
use \PDO;
use \Exception;

class DatabaseHandler
{

    private static DatabaseConnection $connection;

    /**
     * Return a Database interface object for querying
     * @return DatabaseConnection
     * @throws ResponseException
     */
    public static function getConnection() : DatabaseConnection
    {
        // If a database connection doesn't already exist, create one
        if (!isset(self::$connection)) {
            // Get credentials from config
            $db_host = Config::get('db_host');
            $db_name = Config::get('db_name');
            $db_user = Config::get('db_user');
            $db_pass = Config::get('db_pass');

            // Create the connection
            try {
                $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            }
            catch (Exception $e) {
                Logger::log(
                    Logger::ERROR,
                    "Failed connect to database. Throws Exception:\n%s",
                    $e->getMessage()
                );

                throw new ResponseException(500, 3500, 'Oops! An error has occurred processing your request.', 'Failed to connect to the database.');
            }
            // Enable explicit error reporting
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            self::$connection = new DatabaseConnection($conn);
        }

        return self::$connection;
    }

}