<?php

namespace ls\tests;

use LimeSurvey\Datavalueobjects\OperationResult;
use LimeSurvey\Datavalueobjects\TypedMessage;

class OperationResultTest extends TestBaseClass
{
    private static $messages;

    public static function setUpBeforeClass(): void
    {
        $messages = array();

        $messages[] = new TypedMessage('Success message one', 'success');
        $messages[] = new TypedMessage('Success message two', 'success');
        $messages[] = new TypedMessage('Error message one', 'error');
        $messages[] = new TypedMessage('Error message two', 'error');

        self::$messages = $messages;
    }

    public function testSuccessfulOperation()
    {
        $operationResult = new OperationResult(true);

        $this->assertTrue($operationResult->isSuccess(), 'The operation should have been successful.');
    }

    public function testUnsuccessfulOperation()
    {
        $operationResult = new OperationResult();
        $this->assertFalse($operationResult->isSuccess(), 'The operation should not have been successful.');

        $newOperationResult = new OperationResult(false);
        $this->assertFalse($newOperationResult->isSuccess(), 'The operation should not have been successful.');
    }

    public function testSetSuccess()
    {
        $operationResult = new OperationResult();

        // Unsuccessful by default.
        $this->assertFalse($operationResult->isSuccess(), 'The operation should not have been successful.');

        // Set result to success.
        $operationResult->setSuccess(true);
        $this->assertTrue($operationResult->isSuccess(), 'The operation should have been successful.');

        // Set result back to fail.
        $operationResult->setSuccess(false);
        $this->assertFalse($operationResult->isSuccess(), 'The operation should not have been successful.');
    }

    public function testGetMessages()
    {
        $messages = self::$messages;

        $operationResult = new OperationResult(true, $messages);

        $operationResultMessages = $operationResult->getMessages();

        $this->assertIsArray($operationResultMessages, 'The result messages should be in an array.');
        $this->assertCount(4, $operationResultMessages, 'All messages should have been returned.');
    }

    public function testGetSuccessMessages()
    {
        $messages = self::$messages;

        $operationResult = new OperationResult(true, $messages);

        $operationResultMessages = $operationResult->getMessages('success');

        $this->assertIsArray($operationResultMessages, 'The result messages should be in an array.');
        $this->assertCount(2, $operationResultMessages, 'Only success messages should have been returned.');

        foreach ($operationResultMessages as $message) {
            $this->assertSame('success', $message->getType(), 'Only success typed messages should have been returned.');
        }
    }

    public function testGetErrorMessages()
    {
        $messages = self::$messages;

        $operationResult = new OperationResult(false, $messages);

        $operationResultMessages = $operationResult->getMessages('error');

        $this->assertIsArray($operationResultMessages, 'The result messages should be in an array.');
        $this->assertCount(2, $operationResultMessages, 'Only error messages should have been returned.');

        foreach ($operationResultMessages as $message) {
            $this->assertSame('error', $message->getType(), 'Only error typed messages should have been returned.');
        }
    }

    public function testSetMessages()
    {
        $messages = self::$messages;

        $operationResult = new OperationResult(true);
        $operationResult->setMessages($messages);

        $operationResultMessages = $operationResult->getMessages();

        $this->assertIsArray($operationResultMessages, 'The result messages should be in an array.');
        $this->assertCount(4, $operationResultMessages, 'All messages should have been returned.');
    }

    public function testAppendMessage()
    {
        $messages = self::$messages;
        $newMessage = new TypedMessage('Success message three', 'success');

        $operationResult = new OperationResult(true, $messages);
        $operationResult->appendMessage($newMessage);

        $operationResultMessages = $operationResult->getMessages();

        $this->assertIsArray($operationResultMessages, 'The result messages should be in an array.');
        $this->assertCount(5, $operationResultMessages, 'All messages should have been returned.');
    }

    public function testAddMessage()
    {
        $messages = self::$messages;

        $operationResult = new OperationResult(true, $messages);
        $operationResult->addMessage('Success message three', 'success');

        $operationResultMessages = $operationResult->getMessages();

        $this->assertIsArray($operationResultMessages, 'The result messages should be in an array.');
        $this->assertCount(5, $operationResultMessages, 'All messages should have been returned.');
    }

    public function testSetRawMessages()
    {
        $messages = self::$messages;
        $rawMessages = array(
            'Raw message one',
            'Raw message two',
            'Raw message three',
        );

        $operationResult = new OperationResult(true, $messages);

        $operationResultMessages = $operationResult->getMessages();

        $this->assertIsArray($operationResultMessages, 'The result messages should be in an array.');
        $this->assertCount(4, $operationResultMessages, 'All messages should have been returned.');

        $operationResult->setRawMessages($rawMessages);

        $operationRawResultMessages = $operationResult->getMessages();

        $this->assertIsArray($operationRawResultMessages, 'The result messages should be in an array.');
        $this->assertCount(3, $operationRawResultMessages, 'All raw messages should have been returned.');
    }
}
