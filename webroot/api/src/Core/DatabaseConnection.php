<?php
/**
 * Enable standard database interactions
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


use Exception;
use PDO;

class DatabaseConnection
{

    private PDO $conn;

    /**
     * DatabaseConnection constructor.
     * @param PDO $conn The PDO instance to use
     */
    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Execute a SELECT statement and return the results as an associative array
     *
     * @param  string $query  The SQL query to execute, with parameters marked with colons ':'
     * @param  array  $params The parameters to bind to the prepared statement as an associative array
     * @return array
     */
    public function fetchAssoc(string $query, array $params) : array
    {
        $stmt = $this->conn->prepare($query, $params);
        foreach ($params as $param => $value)
        {
            $stmt->bindValue(":$param", $value);
        }

        try
        {
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $res = $stmt->fetch();
            return array
            (
                'success' => $res ? true : false,
                'data' => $res,
            );
        }
        catch (Exception $e)
        {
            Logger::log(Logger::ERROR,
                "Failed to execute query:\n'%s'\n" .
                "Throws Exception:\n%s",
                $query, $e->getMessage());

            return array
            (
                "success" => FALSE,
                "message" => "Failed to execute query",
            );
        }
    }

}