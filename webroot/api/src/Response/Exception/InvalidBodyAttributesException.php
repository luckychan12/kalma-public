<?php
/**
 * An Exception thrown when a request body contains invalid attributes, or is missing required attributes.
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


class InvalidBodyAttributesException extends ResponseException
{

    public function __construct(string $detail = '')
    {
        parent::__construct(400, 1002, 'Oops! Something went wrong accessing this resource.', $detail);
    }

}