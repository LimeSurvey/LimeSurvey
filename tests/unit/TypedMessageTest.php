<?php

namespace ls\tests;

use LimeSurvey\Datavalueobjects\TypedMessage;

class TypedMessageTest extends TestBaseClass
{
    public function testGetType()
    {
        $message = new TypedMessage('Success message', 'Success');
        $this->assertSame('Success', $message->getType(), 'Unexpected message type. The message is of "Success type"');
    }

    public function testSetType()
    {
        $message = new TypedMessage('Error message');

        $message->setType('Error');
        $this->assertSame('Error', $message->getType(), 'Unexpected message type. The message is of "Error type"');
    }

    public function testGetMessage()
    {
        $message = new TypedMessage('Information message', 'info');
        $this->assertSame('Information message', $message->getMessage(), 'Unexpected message. The message does not match the one previously set.');
    }

    public function testSetMessage()
    {
        $message = new TypedMessage('Initial message');

        $message->setMessage('New message');
        $this->assertSame('New message', $message->getMessage(), 'Unexpected message. The message does not match the one previously set.');
    }

    public function testMessageWithDefaultType()
    {
        $message = new TypedMessage('Information message');

        $this->assertSame('', $message->getType(), 'Unexpected type. The type returned does not match the default type.');
        $this->assertSame('Information message', $message->getMessage(), 'Unexpected message. The message does not match the one previously set.');
    }
}
