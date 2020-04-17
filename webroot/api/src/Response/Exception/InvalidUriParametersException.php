<?php
/**
 * An Exception thrown when GET request URI contains invalid parameters.
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


class InvalidUriParametersException extends ResponseException
{

    public function __construct(string $detail = '')
    {
        parent::__construct(400, 1003, 'Oops! Something went wrong accessing this resource.', $detail);
    }

}