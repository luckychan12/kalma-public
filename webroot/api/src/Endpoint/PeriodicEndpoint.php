<?php
/**
 * A CRUD Resource to track data that is recorded primarily as a period of time
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
use Kalma\Api\Response\Exception\ResponseException;
use Kalma\Api\Response\Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PeriodicEndpoint extends DataEndpoint
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
     * Take a duration in minutes and return a string of the format
     * "??h ??m" where either segment is omitted if possible
     * @param int $mins
     * @return string
     */
    private static function minsToText(int $mins) : string
    {
        $m = $mins % 60;
        $h = floor($mins / 60);
        if ($h > 0) {
            return "{$h}h" . ($m > 0 ? "{$m}m" : '');
        }
        else {
            return "{$m}m";
        }
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

        if (!isset($body['periods']) || !is_array($body['periods'] || count($body['periods']) == 0)) {
            throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
        }

        $this->database->beginTransaction();
        foreach ($body['periods'] as $period) {
            $query_params = array('user_id' => $args['id']);

            // Check additional attributes
            foreach ($this->attributes as $attribute) {
                if (!isset($period[$attribute])) {
                    $this->database->rollBack();
                    throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
                }

                // If valid, use the attribute in the SQL query
                $query_params[$attribute] = $period[$attribute];
            }

            // Check start/stop times
            if (!isset($period['start_time']) || !isset($period['stop_time'])) {
                $this->database->rollBack();
                throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
            }
            // Parse start/stop times
            try {
                $utc = new DateTimeZone('UTC');
                $start_time = new DateTime($period['start_time'], $utc);
                $stop_time = new DateTime($period['stop_time'], $utc);
                $now = new DateTime('now', $utc);
                if ($start_time > $now || $stop_time > $now) {
                    throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
                }

                // Disallow creation of periods that overlap existing records
                $overlaps = $this->database->fetch(
                    "SELECT * FROM kalma.sleep_period
	                     WHERE (:start_time BETWEEN start_time AND stop_time
                                OR :stop_time BETWEEN start_time AND stop_time)
                           AND (user_id = :user_id);",
                    array('start_time' => $start_time, 'stop_time' => $stop_time, 'user_id' => $args['id'])
                );
                if (count($overlaps) > 0) {
                    throw new ResponseException(400, 1204, 'New period overlaps with an existing one.',
                        'Measures in the client application prevent overlaps have failed.');
                }

                $query_params['start_time'] = $start_time->format('Y-m-d H:i:s');
                $query_params['stop_time'] = $stop_time->format('Y-m-d H:i:s');
            }
            catch (ResponseException $e) {
                $this->database->rollBack();
                throw $e; // Allow response exceptions to bubble up
            }
            catch (Exception $e) {
                $this->database->rollBack();
                Logger::log(Logger::ERROR, $e->getMessage());
                throw new ResponseException(...ResponseException::INVALID_DATE_FORMAT);
            }

            // Create new record with provided attributes
            $query_attrs = implode(', ', array_keys($query_params));
            $query_attr_params = implode(', ', array_map(function($x) { return ":$x"; }, array_keys($query_params)));
            $this->database->execute(
                "INSERT INTO `$this->table_name` ($query_attrs) 
                                 VALUES ($query_attr_params);",
                $query_params,
            );
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

        // Get sort order. Default to start time
        $order_by = 'start_time';
        if (isset($params['order'])) {
            if (in_array($params['order'], ['start_time', 'stop_time', ...$this->attributes])) {
                $order_by = $params['order'];
            }
            else {
                throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
            }
        }

        // Get sort direction. Defaults to ascending
        $order_dir = isset($params['desc']) ? 'DESC' : 'ASC';

        // Get limits of the query. Default to fetching all matching data.
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
            throw new ResponseException(...ResponseException::INVALID_DATE_FORMAT);
        };

        // Build SQL query string
        $query_attrs = implode(', ', $this->attributes);
        $query = "SELECT {$this->table_name}_id, start_time, stop_time, $query_attrs FROM `$this->table_name`
                      WHERE user_id = :user_id
                        AND start_time > :from_date
                        AND start_time < :to_date
                        ORDER BY $order_by $order_dir
                        LIMIT $lim_offset,$lim_count;";

        $query_params = array(
            'user_id' => $args['id'],
            'from_date' => $from_date,
            'to_date' => $to_date,
        );

        // Execute SELECT query
        $rows = $this->database->fetch($query, $query_params);

        // Fetch user's target for this data
        $target_query_results = $this->database->fetch(
            "SELECT `{$this->name}_target` AS `target` FROM `user` WHERE `user_id` = :user_id;",
            array('user_id' => $args['id']));
        $target = $target_query_results[0]['target'];

        // Build array containing each period's data
        $periods = array();
        foreach ($rows as $row) {
            $id = $row["{$this->table_name}_id"];

            try {
                $start_time = new DateTime($row['start_time'], new DateTimeZone('UTC'));
                $stop_time = new DateTime($row['stop_time'], new DateTimeZone('UTC'));
            } catch (Exception $e) {
                throw new ResponseException(500, 3200, 'Sorry, we couldn\'t fetch the data you requested.', 'An error has occurred parsing query results.');
            }

            // Calculate period duration
            $duration = abs($start_time->getTimestamp() - $stop_time->getTimestamp()) / 60;
            // Get duration as string in hours & minutes
            $duration_text = static::minsToText($duration);

            // Create periods array item for this record
            $period = array(
                'id' => $id,
                'start_time' => $start_time->format(DATE_ISO8601),
                'stop_time' => $stop_time->format(DATE_ISO8601),
                'duration' => $duration,
                'duration_text' => $duration_text,
            );

            foreach ($this->attributes as $attribute) {
                $period[$attribute] = $row[$attribute];
            }

            // If user has a target for this data, include this period's progress towards it
            if (isset($target)) {
                $progress = floor(($duration / $target) * 100);
                if ($progress <= 100) {
                    $message = "$progress% of your daily goal.";
                }
                else {
                    $excess = $progress - 100;
                    $message = "$excess% over your daily goal.";
                }
                $period['progress_percentage'] = $progress;
                $period['progress_message'] = $message;
            }

            $periods[] = $period;
        }

        // Build response
        $res_body['periods'] = $periods;
        if (isset($target)) {
            $res_body['target'] = $target;
            $res_body['target_string'] = self::minsToText($target);
        }
        $res_body['links'] = User::getLinks($args['id'], $this->name);

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
        if (!isset($body['periods']) || !is_array($body['periods']) || count($body['periods']) == 0)
        {
            throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
        }

        // Apply the updates, keeping track of which records were updated successfully
        $affected = array();
        foreach ($body['periods'] as $period)
        {
            if (!isset($period['id']))
            {
                throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
            }

            $allowed_fields = ['start_time', 'stop_time', ...$this->attributes];
            $sets = array();
            $query_params = array('user_id' => $args['id'], "period_id" => $period['id']);
            foreach ($allowed_fields as $field)
            {
                if (isset($period[$field]))
                {
                    $sets[] = "$field = :$field";
                    $query_params[$field] = $period[$field];
                }
            }

            // Build UPDATE query string
            $set_queries = implode(', ', $sets);
            $query = "UPDATE `$this->table_name`
                      SET $set_queries
                      WHERE {$this->table_name}_id = :period_id
                        AND user_id = :user_id;";

            // Execute UPDATE query
            $rows_affected = $this->database->execute($query, $query_params);

            // If a record was successfully updated, include it's ID in the response
            if ($rows_affected > 0)
            {
                $affected[] = $period['id'];
            }
        }

        // Build response
        $res->setBody(array(
            'resources_affected' => $affected,
            'message' => count($affected) < count($body['periods']) ? 'One or more resources could not be updated.' : 'Success.',
            'links' => User::getLinks($args['id'], $this->name),
        ));

        return $res;
    }

    /**
     * DELETE a column
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
        if (!isset($body['periods']) || !is_array($body['periods'])) {
            throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
        }

        // Apply deletions, keeping track of which periods were successfully deleted
        $resources_affected = array();
        foreach ($body['periods'] as $period_id) {
            if (!is_integer($period_id)) {
                throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
            }

            // Build DELETE query string
            $query = "DELETE FROM `$this->table_name` 
                          WHERE {$this->table_name}_id = :period_id
                            AND user_id = :user_id;";
            $query_params = array(
                'period_id' => $period_id,
                'user_id' => $args['id'],
            );
            // Execute DELETE query
            $rows_affected = $this->database->execute($query, $query_params);

            if ($rows_affected > 0) {
                $resources_affected[] = $period_id;
            }
        }

        // Build response
        $res->setBody(array(
            'resources_affected' => $resources_affected,
            'message' => count($resources_affected) < count($body['periods']) ? 'One or more resources could not be updated.' : 'Success.',
            'links' => User::getLinks($args['id'], $this->name),
        ));

        return $res;
    }
}