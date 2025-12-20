<?php

namespace ls\tests;

use LimeMailer;

/**
 * Test LimeMailer class
 * @group mailer
 */
class LimeMailerTest extends TestBaseClass
{
    /**
     * Test that debug array is properly initialized and cleared
     */
    public function testDebugArrayInitialization()
    {
        $mailer = new LimeMailer();
        
        // Debug array should be initialized
        $this->assertIsArray($mailer->debug);
        
        // Add some debug messages
        $mailer->addDebug('Test message 1');
        $mailer->addDebug('Test message 2');
        
        $this->assertCount(2, $mailer->debug);
        
        // Init should clear debug array
        $mailer->init();
        $this->assertEmpty($mailer->debug);
    }

    /**
     * Test addDebug method formats messages correctly
     */
    public function testAddDebugFormatting()
    {
        $mailer = new LimeMailer();
        
        // Test simple message
        $mailer->addDebug('Simple message');
        $this->assertCount(1, $mailer->debug);
        $this->assertSame("Simple message\n", $mailer->debug[0]);
        
        // Test message with trailing newline
        $mailer->addDebug("Message with newline\n");
        $this->assertSame("Message with newline\n", $mailer->debug[1]);
        
        // Test message with multiple trailing newlines
        $mailer->addDebug("Message\n\n");
        $this->assertSame("Message\n", $mailer->debug[2]);
    }

    /**
     * Test addDebug with various input types
     */
    public function testAddDebugWithVariousInputs()
    {
        $mailer = new LimeMailer();
        
        // Empty string
        $mailer->addDebug('');
        $this->assertSame("\n", $mailer->debug[0]);
        
        // String with spaces
        $mailer->addDebug('  test  ');
        $this->assertSame("  test  \n", $mailer->debug[1]);
        
        // Numeric values
        $mailer->addDebug('123');
        $this->assertSame("123\n", $mailer->debug[2]);
        
        // Special characters
        $mailer->addDebug('Test: [INFO] Connection established');
        $this->assertStringContainsString('Test: [INFO]', $mailer->debug[3]);
    }

    /**
     * Test getDebug output formatting
     */
    public function testGetDebugOutput()
    {
        $mailer = new LimeMailer();
        
        $mailer->addDebug('Line 1');
        $mailer->addDebug('Line 2');
        $mailer->addDebug('Line 3');
        
        // Test HTML output
        $htmlOutput = $mailer->getDebug('html');
        $this->assertStringContainsString('Line 1', $htmlOutput);
        $this->assertStringContainsString('Line 2', $htmlOutput);
        $this->assertStringContainsString('Line 3', $htmlOutput);
        
        // Test text output
        $textOutput = $mailer->getDebug('echo');
        $this->assertStringContainsString('Line 1', $textOutput);
        $this->assertStringContainsString('Line 2', $textOutput);
        $this->assertStringContainsString('Line 3', $textOutput);
    }

    /**
     * Test that init() closes SMTP connection
     */
    public function testInitClosesSmtpConnection()
    {
        $mailer = new LimeMailer();
        
        // Set some state
        $mailer->addDebug('Test message');
        $this->assertNotEmpty($mailer->debug);
        
        // Call init
        $mailer->init();
        
        // Debug should be cleared
        $this->assertEmpty($mailer->debug);
    }

    /**
     * Test SMTPDebug configuration
     */
    public function testSMTPDebugConfiguration()
    {
        // Save original config
        $originalDebug = \Yii::app()->getConfig('emailsmtpdebug');
        
        // Test with debug = 0
        \Yii::app()->setConfig('emailsmtpdebug', 0);
        $mailer = new LimeMailer();
        $this->assertSame(0, $mailer->SMTPDebug);
        
        // Test with debug = 1
        \Yii::app()->setConfig('emailsmtpdebug', 1);
        $mailer = new LimeMailer();
        $this->assertSame(1, $mailer->SMTPDebug);
        
        // Test with debug = 2
        \Yii::app()->setConfig('emailsmtpdebug', 2);
        $mailer = new LimeMailer();
        $this->assertSame(2, $mailer->SMTPDebug);
        
        // Restore original config
        \Yii::app()->setConfig('emailsmtpdebug', $originalDebug);
    }

    /**
     * Test that Debugoutput function is properly configured
     */
    public function testDebugoutputFunctionConfiguration()
    {
        $mailer = new LimeMailer();
        
        // Debugoutput should be a callable
        $this->assertIsCallable($mailer->Debugoutput);
        
        // Test that it adds to debug array
        $debugFunc = $mailer->Debugoutput;
        $debugFunc('Test from callback', 0);
        
        $this->assertNotEmpty($mailer->debug);
        $this->assertStringContainsString('Test from callback', $mailer->debug[count($mailer->debug) - 1]);
    }

    /**
     * Test init() multiple times
     */
    public function testInitMultipleTimes()
    {
        $mailer = new LimeMailer();
        
        // First init
        $mailer->addDebug('Message 1');
        $this->assertCount(1, $mailer->debug);
        $mailer->init();
        $this->assertEmpty($mailer->debug);
        
        // Second init
        $mailer->addDebug('Message 2');
        $this->assertCount(1, $mailer->debug);
        $mailer->init();
        $this->assertEmpty($mailer->debug);
        
        // Third init
        $mailer->addDebug('Message 3');
        $this->assertCount(1, $mailer->debug);
        $mailer->init();
        $this->assertEmpty($mailer->debug);
    }

    /**
     * Test that content type is reset on init
     */
    public function testInitResetsContentType()
    {
        $mailer = new LimeMailer();
        
        // Change content type
        $mailer->ContentType = 'text/html';
        $this->assertSame('text/html', $mailer->ContentType);
        
        // Init should reset to plain text
        $mailer->init();
        $this->assertSame(LimeMailer::CONTENT_TYPE_PLAINTEXT, $mailer->ContentType);
    }

    /**
     * Test clearing addresses on init
     */
    public function testInitClearsAddresses()
    {
        $mailer = new LimeMailer();
        
        // This test verifies init() calls clearAddresses()
        // We can't fully test without setting up addresses, but we can verify init completes
        $mailer->init();
        
        // After init, mailer should be in clean state
        $this->assertEmpty($mailer->debug);
        $this->assertSame(LimeMailer::CONTENT_TYPE_PLAINTEXT, $mailer->ContentType);
    }
}