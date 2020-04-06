<?php
/**
 * A CRUD Resource to track periods of sleep
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


class SleepPeriod extends PeriodicEndpoint
{

    public function __construct()
    {
        parent::__construct('sleep', 'sleep_period', array(
            'sleep_quality',
        ));
    }

}