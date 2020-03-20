<?php
/**
 * Generic wrapper for PSR-7 HTTP Response
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley (fergus.bentley@gmail.com)
 * @category   Kalma
 * @package    Api
 * @subpackage Response
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 */

namespace Kalma\Api\Response;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface as HttpResponse;

abstract class Response
{

    protected string $uri;

    /**
     * Response constructor
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    /**
     * Get the request URI that resulted in this response
     * @return string
     */
    public function getRequestURI() : string
    {
        return $this->uri;
    }

    /**
     * Set the HTTP Response Status Code
     * @param int $status
     */
    public abstract function setStatus(int $status) : void;

    /**
     * Get the HTTP Response Status Code
     * @return int
     */
    public abstract function getStatus() : int;

    /**
     * Set the HTTP Response body
     * Non-string body values must coerced into a string before emission
     * @param mixed $body
     */
    public abstract function setBody($body) : void;

    /**
     * Get an object representing the text to be sent in the HTTP Response body
     * @return mixed
     */
    public abstract function getBody();

    /**
     * Set the text to sent in the HTTP Response body
     * @param string $body
     */
    public abstract function setBodyText(string $body) : void;

    /**
     * Get the text to be sent in the HTTP Response body
     * @return string
     */
    public abstract function getBodyText() : string;

    /**
     * Set the value of an HTTP Response header
     * @param string $header
     * @param string $value
     */
    public abstract function setHeader(string $header, string $value) : void;

    /**
     * Get the value of a specific HTTP Response header
     * @param string $header
     * @return string
     */
    public abstract function getHeader(string $header) : string;

    /**
     * Get an associative array of all header / value pairs
     * @return array
     */
    public abstract function getHeaders() : array;

    /**
     * Return a PSR-7 HTTP response
     * @return HttpResponse
     */
    public function getResponse() : HttpResponse
    {
        $response = (new Psr17Factory())
            ->createResponse()
            ->withStatus($this->getStatus())
            ->withBody(Stream::create($this->getBodyText()));

        foreach ($this->getHeaders() as $name => $value)
        {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}