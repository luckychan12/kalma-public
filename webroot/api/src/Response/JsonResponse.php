<?php
/**
 * Wrapper for PSR-7 HTTP Response with raw JSON body
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

use Psr\Http\Message\ResponseInterface as HttpResponse;

class JsonResponse extends Response
{

    private int $status;
    private array $body;
    private array $headers;

    private array $defaultHeaders = array(
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Authorization, x-requested-with, Content-Type',
        'Access-Control-Allow-Methods' => 'GET,HEAD,PUT,PATCH,POST,DELETE,OPTIONS',
        'Content-Type' => 'application/json; charset=UTF-8',
    );

    /**
     * JsonResponse constructor.
     * @param string $uri
     * @param int $status
     * @param array $body
     * @param array $headers
     */
    public function __construct(string $uri, int $status = 500, array $body = [], array $headers = [])
    {
        parent::__construct($uri);
        $this->setStatus($status);
        $this->setBody($body);
        $this->setHeaders(array_merge($this->defaultHeaders, $headers));
    }

    /**
     * @override
     * @return HttpResponse
     */
    public function getResponse(): HttpResponse
    {
        $this->body['timestamp'] = date(DATE_ISO8601);
        $this->body['status'] = $this->status;
        $this->body['uri'] = $this->getRequestURI();
        return parent::getResponse();
    }

    /**
     * Set the HTTP Response Status Code
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * Get the HTTP Response Status Code
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set the text to sent in the HTTP Response body
     * @param string $body
     */
    public function setBodyText(string $body): void
    {
        $this->body = json_decode($body);
    }

    /**
     * Get the text to be sent in the HTTP Response body
     * @return string
     */
    public function getBodyText(): string
    {
        return json_encode($this->body);
    }

    /**
     * Set data to be encoded as JSON and sent in the HTTP Response body
     * @param array $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * Get the JSON response body as an associative array
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * Set the value of an HTTP Response header
     * @param string $header
     * @param string $value
     */
    public function setHeader(string $header, string $value): void
    {
        $this->headers[$header] = $value;
    }

    /**
     * Get the value of a specific HTTP Response header
     * @param string $header
     * @return string
     */
    public function getHeader(string $header): string
    {
        return $this->headers[$header];
    }

    /**
     *
     * @param array $headers
     */
    public function setHeaders(array $headers) : void
    {
        $this->headers = $headers;
    }

    /**
     * Get an associative array of all header / value pairs
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}