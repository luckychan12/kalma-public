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
use Kalma\Api\Response\Exception\ResponseException;
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
     * @param string $query The SQL query to execute, with parameters marked with colons ':'
     * @param array $params The parameters to bind to the prepared statement as an associative array
     * @return array
     * @throws ResponseException
     */
    public function fetch(string $query, array $params) : array
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
            $res = $stmt->fetchAll();
            if ($res === false)
            {
                throw new ResponseException(500, 3000, 'Oops! Something went wrong processing your request.', 'A database error has occurred.');
            }

            return $res;
        }
        catch (Exception $e)
        {
            Logger::log(Logger::ERROR,
                "Failed to execute query:\n\t'%s'\n" .
                "\tThrows Exception:\n\t%s",
                $query, $e->getMessage());

            throw new ResponseException(500, 3001, 'Oops! Something went wrong processing your request.', 'An SQL error has occurred.');
        }
    }

    /**
     * Execute an SQL statement with no output
     * @param string $query
     * @param array $params
     * @return int          The number of rows affected by the statement.
     * @throws ResponseException
     */
    public function execute(string $query, array $params) : int
    {
        $stmt = $this->conn->prepare($query, $params);
        foreach ($params as $param => $value)
        {
            $stmt->bindValue(":$param", $value);
        }

        if($stmt->execute())
        {
            return $stmt->rowCount();
        }
        else
        {
            Logger::log(Logger::ERROR, "Failed to execute query: \n\t'%s'" .
                                       "Error Info: \n\t %s",
                                        $query, $this->conn->errorInfo());


            throw new ResponseException(500, 3001, 'Oops! Something went wrong processing your request.', 'An SQL error has occurred.');
        }

    }

}