<?php
declare(strict_types=1);

namespace Kalma\API\Test\Core;

require __DIR__ . '/../../vendor/autoload.php';

use Kalma\Api\Core\Config;
use PHPUnit\Framework\TestCase;


final class ConfigTest extends TestCase
{
    public function testReadsValueForValidKey() : void
    {
        $this->assertEquals('localhost', Config::get('db_host'));
    }

    public function testReturnsNullForInvalidKey() : void
    {
        $this->assertEquals(NULL, Config::get('INVALID_KEY'));
    }
}