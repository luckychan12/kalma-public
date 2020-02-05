<?php
/**
 * Log debug, info, warning and error messages
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

class Logger
{

    const INFO    = 'info';
    const DEBUG   = 'debug';
    const WARNING = 'warning';
    const ERROR   = 'error';

    /**
     * Format a message to include specified arguments, if any
     *
     * @param  string $message The message to format
     * @param  array  $args    The values to insert into the string
     * @return string          The formatted message with a timestamp
     */
    private static function format(string $message, iterable $args) : string
    {
        if (substr($message, -1) != "\n") {
            $message = $message . "\n";
        }
        $timestamp = date('[Y-m-d H:i:s:v] ');
        $formatted = sprintf($message, ...$args);
        return $timestamp . $formatted;
    }

    /**
     * Log a message to a file based on a given logging type
     *
     * @param  string $type     The type of message being logged
     * @param  string $message  The message to log
     * @param  mixed  ...$args  Information to insert into the message
     * @return void
     */
    public static function log(string $type, string $message, ...$args) : void
    {
        $dayOfMonth = date('j');
        $weekOfMonth = floor($dayOfMonth / 7.5); // Get week of month 0-4
        $log_file = date('Y-m_') . $weekOfMonth;
        $log_path = __DIR__ . "/../../logs/$log_file.log";
        $formatted_message = self::format(strtoupper($type) . ": $message", $args);
        file_put_contents(
            $log_path,
            $formatted_message,
            (file_exists($log_path) ? FILE_APPEND : 0) | LOCK_EX
        );
    }



}
