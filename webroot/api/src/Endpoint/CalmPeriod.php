<?php
/**
 * A CRUD Resource to track periods of mindfulness
 *
 * LICENSE: This code is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
 *
 * @author     Fergus Bentley
 * @category   Kalma
 * @package    Api
 * @subpackage Resource
 * @license    http://creativecommons.org/licenses/by-nc-nd/4.0/  CC BY-NC-ND 4.0
 */

namespace Kalma\Api\Endpoint;


class CalmPeriod extends PeriodicEndpoint
{

    public function __construct()
    {
        parent::__construct('calm', 'calm_period', array(
            'description',
        ));
    }

}