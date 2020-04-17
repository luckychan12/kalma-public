<?php
/**
 * A CRUD Resource to track data that can be recorded for any unique date
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley
 * @category   Kalma
 * @package    Api
 * @subpackage Resource
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 */

namespace Kalma\Api\Endpoint;

use DateTime;
use DateTimeZone;
use Exception;
use Kalma\Api\Core\Logger;
use Kalma\Api\Response\Exception\InvalidBodyAttributesException;
use Kalma\Api\Response\Exception\InvalidUriParametersException;
use Kalma\Api\Response\Exception\ResponseException;
use Kalma\Api\Response\Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoggedEndpoint extends DataEndpoint
{

    private string $name;
    private string $table_name;
    private array $attributes;

    public function __construct(string $name, string $table_name, array $attributes)
    {
        parent::__construct();
        $this->name = $name;
        $this->table_name = $table_name;
        $this->attributes = $attributes;
    }

    /**
     * CREATE a new database entry from request parameters
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function _create(Request $req, Response $res, ?array $payload, array $args): Response
    {
        $body = $req->getParsedBody();

        if (!isset($body['entries']) || !is_array($body['entries']) || !(count($body['entries']) > 0)) {
            throw new InvalidBodyAttributesException("The 'entries' array is missing, empty, or not an array.");
        }

        $this->database->beginTransaction();

        // Attempt to create a record for each entry in the array
        foreach ($body['entries'] as $entry) {
            // Build INSERT query
            $query_params = array('user_id' => $args['id'], 'date_logged' => date('Y-m-d'));

            foreach ($this->attributes as $attribute) {
                if (!isset($entry[$attribute])) {
                    $this->database->rollBack();
                    throw new InvalidBodyAttributesException("One or more entry objects are missing the required attribute '$attribute'.");
                }
                $query_params[$attribute] = $entry[$attribute];
            }

            $query_attrs = implode(", ", array_keys($query_params));
            $query_attr_params = implode(", ", array_map(function($v) { return ":$v"; }, array_keys($query_params)));
            $query = "INSERT INTO `$this->table_name` ($query_attrs) VALUES ($query_attr_params);";

            // Execute INSERT query
            $this->database->execute($query, $query_params);
        }

        $this->database->commit();

        // Build response
        $res->setBody(array(
            'message' => 'Success.',
            'links' => User::getLinks($args['id'], $this->name),
        ));
        return $res;
    }

    /**
     * READ an existing database entry
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function _read(Request $req, Response $res, ?array $payload, array $args): Response
    {
        $params = $_GET;

        // Get sort order, default to chronological
        $order_by = 'date_logged';
        if (isset($params['order'])) {
            if (in_array($params['order'], ['date_logged', ...$this->attributes])) {
                $order_by = $params['order'];
            }
            else {
                $attr = $params['order'];
                throw new InvalidUriParametersException("Cannot sort by unknown attribute '$attr'.");
            }
        }

        // Get sort direction, default to ascending
        $order_dir = isset($params['desc']) ? 'DESC' : 'ASC';

        // Get limits of the query, default to fetching all matching records
        $lim_offset = $params['offset'] ?? 0;
        $lim_count = $params['count'] ?? 2147483647;

        // Get date parameters in SQL-friendly format. Default to min/max representable dates if not specified
        // i.e. fetch records from any date
        try {
            $from_date = isset($params['from']) ? (new DateTime($params['from'], new DateTimeZone('UTC')))->format('Y-m-d') : '0000-00-00';
            $to_date = isset($params['to']) ? (new DateTime($params['to'], new DateTimeZone('UTC')))->format('Y-m-d') : '9999-12-31';
        }
        catch (Exception $e) {
            Logger::log(Logger::ERROR, $e->getMessage());
            throw new ResponseException(400, 1101, 'One or more of the form fields isn\'t valid.', 'Invalid date format.');
        };

        // Build SELECT query string
        $query_params = array(
            'user_id' => $args['id'],
            'from_date' => $from_date,
            'to_date' => $to_date,
        );
        $query_attrs = implode(', ', $this->attributes);
        $query = "SELECT {$this->table_name}_id, date_logged, $query_attrs FROM `$this->table_name`
                      WHERE user_id = :user_id
                        AND date_logged > :from_date
                        AND date_logged < :to_date
                        ORDER BY $order_by $order_dir
                        LIMIT $lim_offset,$lim_count;";

        // Execute SELECT query
        $rows = $this->database->fetch($query, $query_params);

        // Get the user's target for this data
        $target = $this->database->fetch("SELECT `{$this->name}_target` AS `target` FROM `user` WHERE `user_id` = :user_id;",
                                          array('user_id' => $args['id']))[0]['target'];

        // Build an array of entry records
        $entries = array();
        foreach ($rows as $row) {
            $id = $row["{$this->table_name}_id"];

            try {
                $date_logged = new DateTime($row['date_logged'], new DateTimeZone('UTC'));
            } catch (Exception $e) {
                throw new ResponseException(500, 3200, 'Sorry, we couldn\'t fetch the data you requested.', 'An error has occurred parsing query results.');
            }

            $entry = array(
                'id' => $id,
                'date_logged' => $date_logged->format(DATE_ISO8601),
            );

            // Calculate progress towards target, if a target is set
            $logged = $row[$this->attributes[0]];
            if (isset($target) && $target > 0) {
                $progress = floor(($logged / $target) * 100);
                $message = "$progress% of your daily goal.";
                $entry['progress_percentage'] = $progress;
                $entry['progress_message'] = $message;
            }

            foreach ($this->attributes as $attribute) {
                $entry[$attribute] = $row[$attribute];
            }

            $entries[] = $entry;
        }
        // Build the response
        $res_body = array(
            'entries' => $entries,
            'links' => User::getLinks($args['id'], $this->name),
        );

        if (isset($target)) {
            $res_body['target'] = $target;
        }

        $res->setBody($res_body);
        return $res;
    }

    /**
     * UPDATE one or more columns for a given entry
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function _update(Request $req, Response $res, ?array $payload, array $args): Response
    {
        $body = $req->getParsedBody();
        if (!isset($body['entries']) || !is_array($body['entries']) || !(count($body['entries']) > 0)) {
            throw new InvalidBodyAttributesException("The 'entries' array is missing, empty, or not an array.");
        }

        // Execute an UPDATE query for each record in the entries array
        $affected = array();
        foreach ($body['entries'] as $entry) {
            if (!isset($entry['id'])) {
                throw new InvalidBodyAttributesException("One or more entry objects are missing the required 'id' attribute.");
            }

            // Parse record body
            $allowed_fields = [...$this->attributes];
            $sets = array();
            $query_params = array('user_id' => $args['id'], "entry_id" => $entry['id']);
            foreach ($allowed_fields as $field) {
                if (isset($entry[$field])) {
                    $sets[] = "$field = :$field";
                    $query_params[$field] = $entry[$field];
                }
            }

            // Build UPDATE query string
            $set_queries = implode(', ', $sets);
            $query = "UPDATE `$this->table_name`
                      SET $set_queries
                      WHERE {$this->table_name}_id = :entry_id
                        AND user_id = :user_id;";

            // Execute UPDATE query
            $rows_affected = $this->database->execute($query, $query_params);

            // Track successfully updated records
            if ($rows_affected > 0) {
                $affected[] = $entry['id'];
            }
        }

        // Build response
        $res->setBody(array(
            'resources_affected' => $affected,
            'message' => count($affected) < count($body['entries']) ? 'One or more resources could not be updated.' : 'Success.',
            'links' => User::getLinks($args['id'], $this->name),
        ));

        return $res;
    }

    /**
     * DELETE one or more log entries
     * @param Request $req
     * @param Response $res
     * @param array|null $payload
     * @param array $args
     * @return Response
     * @throws ResponseException
     */
    public function _delete(Request $req, Response $res, ?array $payload, array $args): Response
    {
        $body = $req->getParsedBody();
        if (!isset($body['entries']) || !is_array($body['entries']) || !(count($body['entries']) > 0)) {
            throw new InvalidBodyAttributesException("The 'entries' array is missing, empty, or not an array.");
        }

        // Create and execute a DELETE query for each record in the entries array
        $resources_affected = array();
        foreach ($body['entries'] as $entry_id) {
            if (!is_integer($entry_id)) {
                throw new InvalidBodyAttributesException("One or more entry objects are missing the required 'id' attribute.");
            }

            // Build DELETE query string
            $query = "DELETE FROM `$this->table_name` 
                          WHERE {$this->table_name}_id = :entry_id
                            AND user_id = :user_id;";
            $query_params = array(
                'entry_id' => $entry_id,
                'user_id' => $args['id'],
            );

            // Execute DELETE query
            $rows_affected = $this->database->execute($query, $query_params);

            // Track successfully deleted records
            if ($rows_affected > 0) {
                $resources_affected[] = $entry_id;
            }
        }

        // Build response
        $res->setBody(array(
            'resources_affected' => $resources_affected,
            'message' => count($resources_affected) < count($body['entries']) ? 'One or more resources could not be updated.' : 'Success.',
            'links' => User::getLinks($args['id'], $this->name),
        ));

        return $res;
    }
}