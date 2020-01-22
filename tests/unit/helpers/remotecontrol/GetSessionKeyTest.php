<?php

namespace ls\tests;

class GetSessionKeyTest extends BaseTest
{
    public function testSessionKeyIsValid()
    {
        $result = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $this->assertInternalType('string', $result);
        $this->assertEquals(32, strlen($result));
    }

    public function testSessionKeyIsInvalid()
    {
        $result = $this->handler->get_session_key('username', 'password');
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('Invalid user name or password', $result['status']);
    }
}
