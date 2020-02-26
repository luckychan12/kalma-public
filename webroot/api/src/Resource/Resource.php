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

namespace Kalma\Api\Resource;

use Kalma\Api\Core\Config;
use Kalma\Api\Core\DatabaseConnection;
use Kalma\Api\Core\DatabaseHandler;

require __DIR__ . '/../../vendor/autoload.php';

class Resource
{

    protected DatabaseConnection $database;
    protected string $api_root;

    public function __construct()
    {
        $this->database = DatabaseHandler::getConnection();
        $this->api_root = Config::get('api_root');
    }
}