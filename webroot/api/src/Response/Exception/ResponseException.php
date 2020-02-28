<?php
/**
 * Represents an Exception thrown when an error occurs processing a request.
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley (fergus.bentley@gmail.com)
 * @category   Kalma
 * @package    Api
 * @subpackage Response
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 */

namespace Kalma\Api\Response\Exception;

use \Exception;

class ResponseException extends Exception
{

    private int $status;
    private string $detail;

    public function __construct(int $status, int $code, string $message = '', string $detail = '')
    {
        parent::__construct($message, $code, null);
        $this->status = $status;
        $this->detail = $detail;
    }

    /**
     * Set the HTTP Response Status Code corresponding to the type of error encountered
     * @param int $status
     */
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }

    /**
     * Get the HTTP Response Status Code corresponding to the type of error encountered
     * @return int
     */
    public function getStatus() : int
    {
        return $this->status;
    }

    /**
     * Set specific details about the error, e.g. the circumstances that caused it.
     * @param string $detail
     */
    public function setDetail(string $detail) : void
    {
        $this->detail = $detail;
    }

    /**
     * Get any specific details about the error
     * @return string
     */
    public function getDetail() : string
    {
        return $this->detail;
    }

}