<?php

namespace ls\tests\unit\helpers\remotecontrol;

class GetSessionKeyTest extends BaseTest
{
    public function testSessionKeyIsValid()
    {
        $result = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $this->assertIsString($result, 'result from get_session_key was invalid');
        $this->assertEquals(32, strlen($result));
    }

    /**
     * Logging in with the email address must open the session for the username resolved by the auth plugin
     */
    public function testSessionKeyByEmailUsesUsername()
    {
        $result = $this->handler->get_session_key($this->user->email, $this->getPassword());
        $this->assertIsString($result, 'result from get_session_key was invalid');
        $session = \Session::model()->findByPk($result);
        $this->assertNotNull($session);
        $this->assertEquals($this->user->users_name, $session->data);
        $this->handler->release_session_key($result);
    }

    public function testSessionKeyIsInvalid()
    {
        $result = $this->handler->get_session_key('username', 'password');
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('Invalid user name or password', $result['status']);
    }
}
