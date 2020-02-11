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

use PDO;
use Kalma\Api\Core\DatabaseHandler;
require __DIR__ . '/../../vendor/autoload.php';

class Resource
{

    protected PDO $database_handler;

    public function __construct()
    {
        $this->database_handler = DatabaseHandler::getConnection();
    }
}