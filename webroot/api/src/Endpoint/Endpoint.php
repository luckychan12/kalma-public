<?php
/**
 * API Resource parent class.
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley
 * @category   Kalma
 * @package    Api
 * @subpackage Resource
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 * @version    0.1
 * @since      File available since Pre-Alpha
 */

namespace Kalma\Api\Endpoint;

use Kalma\Api\Core\Config;
use Kalma\Api\Core\DatabaseConnector;
use Kalma\Api\Core\DatabaseHandler;
use Kalma\Api\Response\Exception\ResponseException;

require __DIR__ . '/../../vendor/autoload.php';

class Endpoint
{

    protected DatabaseHandler $database;
    protected string $api_root;

    /**
     * Resource constructor.
     * @throws ResponseException
     */
    public function __construct()
    {
        $db = DatabaseConnector::getConnection();
        if ($db === null)
        {
            throw new ResponseException(500, 3500, 'Oops! An error has occurred processing your request.', 'Failed to connect to the database.');
        }
        $this->database = $db;
        $this->api_root = Config::get('api_root');
    }
}