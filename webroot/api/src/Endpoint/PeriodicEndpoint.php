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

        $this->database->beginTransaction();

        foreach ($body['periods'] as $period)
        {
            $query_params = array('user_id' => $args['id']);

            // Check additional attributes
            foreach ($this->attributes as $attribute)
            {
                if (!isset($period[$attribute]))
                {
                    $this->database->rollBack();
                    throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
                }

                // If valid, use the attribute in the SQL query
                $query_params[$attribute] = $period[$attribute];
            }

            // Check start/stop times
            if (!isset($period['start_time']) || !isset($period['stop_time']))
            {
                $this->database->rollBack();
                throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
            }
            // Parse start/stop times
            try {
                $utc = new DateTimeZone('UTC');
                $start_time = new DateTime($period['start_time'], $utc);
                $stop_time = new DateTime($period['stop_time'], $utc);
                $now = new DateTime('now', $utc);
                if ($start_time > $now || $stop_time > $now)
                {
                    throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
                }

                $query_params['start_time'] = $start_time->format('Y-m-d H:i:s');
                $query_params['stop_time'] = $stop_time->format('Y-m-d H:i:s');
            }
            catch (ResponseException $e)
            {
                $this->database->rollBack();
                throw $e;
            }
            catch (Exception $e) {
                $this->database->rollBack();
                Logger::log(Logger::ERROR, $e->getMessage());
                throw new ResponseException(400, 1101, 'One or more of the form fields isn\'t valid.', 'Invalid date format.');
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

        $res->setBody(array('message' => 'Success.'));
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

        $order_by = 'start_time';
        if (isset($params['order']))
        {
            if (in_array($params['order'], ['start_time', 'stop_time', ...$this->attributes]))
            {
                $order_by = $params['order'];
            }
            else
            {
                throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
            }
        }

        $order_dir = isset($params['desc']) ? 'DESC' : 'ASC';


        $lim_offset = $params['offset'] ?? 0;
        $lim_count = $params['count'] ?? 2147483647;

        $query_attrs = implode(', ', $this->attributes);

        $query = "SELECT {$this->table_name}_id, start_time, stop_time, $query_attrs FROM `$this->table_name`
                      WHERE user_id = :user_id
                        AND start_time > :from_date
                        AND start_time < :to_date
                        ORDER BY $order_by $order_dir
                        LIMIT $lim_offset,$lim_count;";


        try {
            $from_date = isset($params['from']) ? (new DateTime($params['from'], new DateTimeZone('UTC')))->format('Y-m-d') : '0000-00-00';
            $to_date = isset($params['to']) ? (new DateTime($params['to'], new DateTimeZone('UTC')))->format('Y-m-d') : '9999-12-31';
        }
        catch (Exception $e) {
            Logger::log(Logger::ERROR, $e->getMessage());
            throw new ResponseException(400, 1101, 'One or more of the form fields isn\'t valid.', 'Invalid date format.');
        };

        $query_params = array(
            'user_id' => $args['id'],
            'from_date' => $from_date,
            'to_date' => $to_date,
        );

        $rows = $this->database->fetch($query, $query_params);

        $target = $this->database->fetch("SELECT `{$this->name}_target` AS `target` FROM `user` WHERE `user_id` = :user_id;",
            array('user_id' => $args['id']))[0]['target'];

        $periods = array();
        foreach ($rows as $row)
        {
            $id = $row["{$this->table_name}_id"];

            try {
                $start_time = new DateTime($row['start_time'], new DateTimeZone('UTC'));
                $stop_time = new DateTime($row['stop_time'], new DateTimeZone('UTC'));
            } catch (Exception $e) {
                throw new ResponseException(500, 3200, 'Sorry, we couldn\'t fetch the data you requested.', 'An error has occurred parsing query results.');
            }

            $duration = abs($start_time->getTimestamp() - $stop_time->getTimestamp()) / 60;
            $duration_text = $duration . 'm';
            if ($duration > 60)
            {
                $duration_text = floor($duration / 60).'h';
                if ($duration % 60 > 0)
                {
                    $duration_text .= ' '.($duration % 60).'m';
                }
            }

            $period = array(
                'id' => $id,
                'start_time' => $start_time->format(DATE_ISO8601),
                'stop_time' => $stop_time->format(DATE_ISO8601),
                'duration' => $duration,
                'duration_text' => $duration_text,
            );

            foreach ($this->attributes as $attribute)
            {
                $period[$attribute] = $row[$attribute];
            }

            if (isset($target)) {
                $progress = floor(($duration / $target) * 100);
                if ($progress <= 100) {
                    $message = "$progress% of your daily goal.";
                }
                else if ($progress > 100) {
                    $excess = $progress - 100;
                    $message = "$excess% over your daily goal.";
                }
                $period['progress_percentage'] = $progress;
                $period['progress_message'] = $message;
            }

            $periods[] = $period;
        }

        $res->setBody(array(
            'periods' => $periods,
        ));
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
        if (!isset($body['periods']) || !is_array($body['periods']))
        {
            throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
        }

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
            
            $set_queries = implode(', ', $sets);

            $query = "UPDATE `$this->table_name`
                      SET $set_queries
                      WHERE {$this->table_name}_id = :period_id
                        AND user_id = :user_id;";

            $rows_affected = $this->database->execute($query, $query_params);

            if ($rows_affected > 0)
            {
                $affected[] = $period['id'];
            }
        }

        $res->setBody(array(
            'resources_affected' => $affected,
            'message' => count($affected) < count($body['periods']) ? 'One or more resources could not be updated.' : 'Success.',
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
        if (!isset($body['periods']) || !is_array($body['periods']))
        {
            throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
        }

        $resources_affected = array();

        foreach ($body['periods'] as $period_id)
        {
            if (!is_integer($period_id))
            {
                throw new ResponseException(...ResponseException::INVALID_BODY_ATTRS);
            }

            $query = "DELETE FROM `$this->table_name` 
                          WHERE {$this->table_name}_id = :period_id
                            AND user_id = :user_id;";
            $query_params = array(
                'period_id' => $period_id,
                'user_id' => $args['id'],
            );

            $rows_affected = $this->database->execute($query, $query_params);

            if ($rows_affected > 0)
            {
                $resources_affected[] = $period_id;
            }
        }

        $res->setBody(array(
            'resources_affected' => $resources_affected,
            'message' => count($resources_affected) < count($body['periods']) ? 'One or more resources could not be updated.' : 'Success.',
        ));

        return $res;
    }
}