<?php
declare(strict_types=1);

namespace Kalma\API\Test\Core;

require __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Kalma\Api\Core\Auth;


final class AuthTest extends TestCase
{
    /**
     * Test that a JWT can be generated given a valid access level and user id
     * @return string
     */
    public function testGeneratesValidJwt() : string
    {
        $user_id = 1234;
        $access_level = 10;
        $jwt = Auth::generateJWT($access_level, $user_id);

        self::assertNotNull($jwt);

        return $jwt;
    }

    /**
     * Test that a valid JWT can be validated
     * @depends testGeneratesValidJwt
     * @param   string $jwt The JWT to validate. Output from testGeneratesValidJWT
     */
    public function testValidatesValidJwt(string $jwt) : void
    {
        sleep(1); // Wait for JWT to become valid
        $res = Auth::validateJWT($jwt);

        self::assertTrue($res['success']);
        self::assertEquals(10, $res['access_level']);
        self::assertEquals(1234, $res['payload']['user_id']);
    }
}