<?php
declare(strict_types=1);

namespace Kalma\API\Test\Core;

require __DIR__ . '/../../vendor/autoload.php';

use Kalma\Api\Core\DatabaseConnection;
use Kalma\Api\Core\DatabaseHandler;
use PHPUnit\Framework\TestCase;

final class DatabaseHandlerTest extends TestCase
{
    public function testConnectsToDatabase() : void
    {
        $this->assertInstanceOf(DatabaseConnection::class, DatabaseHandler::getConnection());
    }
}