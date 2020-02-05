<?php
/**
 * Loads and stores configurable settings from config.cfg
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

require __DIR__ . '/../../vendor/autoload.php';

class Config
{
    private static array $config;

    /**
     * Load config data from the config file
     * @return array An associative array of config settings
     */
    private static function loadData() : array
    {
        $config_contents = file_get_contents(__DIR__ . '/../../config.cfg');
        if (!$config_contents) {
            Logger::log(Logger::ERROR, 'Failed to read config file.');
            return array();
        }
        $lines = explode("\n", $config_contents);
        $assoc = array();
        foreach ($lines as $line) {
            if (!empty($line)) {
                $parts = explode('=', $line);
                $key = $parts[0];
                $value = str_replace("\r", '', substr($line, strlen($key) + 1));
                $assoc[$key] = $value;
            }
        }
        return $assoc;
    }


    /**
     * Retrieve a config value from the settings file
     *
     * @param  string $key The setting to retrieve
     * @return string      The configured value. NULL if undefined.
     */
    public static function get(string $key) : ?string
    {
        if (!isset(self::$config)) {
            self::$config = self::loadData();
        }

        if (array_key_exists($key, self::$config)) {
            return self::$config[$key];
        }
        else {
            Logger::log(Logger::WARNING, "No such configuration key '$key'");
            return NULL;
        }
    }
}
