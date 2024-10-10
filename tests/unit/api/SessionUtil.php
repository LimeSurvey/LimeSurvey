<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Authentication\AuthenticationTokenSimple;
use LimeSurvey\Api\Authentication\SessionUtil;

/**
 * @testdox Session Util
 */
class SessionUtilTest extends TestBaseClass
{
    public static function tearDownAfterClass(): void
    {
        $_SESSION = [];
        $_POST    = [];
    }


    /**
     * @testdox jumpStartSession() init session from key.
     */
    public function testJumpStartSessionInitSessionFromKey()
    {
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $sessionUtil = new SessionUtil;
        $result = $sessionUtil->jumpStartSession($username);
        $this->assertTrue($result);
    }
}
