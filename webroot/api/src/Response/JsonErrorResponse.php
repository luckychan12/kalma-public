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


use Kalma\Api\Response\Exception\ResponseException;

class JsonErrorResponse extends JsonResponse
{

    private ResponseException $exception;

    public function __construct(string $uri, ResponseException $exception)
    {
        parent::__construct($uri, $exception->getStatus());
        $this->$exception = $exception;

        $body = array('error' => $exception->getCode());

        if ($exception->getMessage() !== '')
        {
            $body['message'] = $exception->getMessage();
        }

        if ($exception->getDetail() !== '')
        {
            $body['detail'] = $exception->getDetail();
        }

        $this->setBody($body);
    }

}