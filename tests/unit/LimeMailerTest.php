<?php

namespace ls\tests;

/**
 * Tests for the «Name <email>» parsing in LimeMailer
 */
class LimeMailerTest extends TestBaseClass
{
    /**
     * Cases for parseNameAndAddress: input string, expected name, expected address
     * @return array
     */
    public static function provideNameAndAddressCases()
    {
        return [
            'plain address' => ['john@example.org', null, 'john@example.org'],
            'name and address' => ['John Doe <john@example.org>', 'John Doe', 'john@example.org'],
            'empty name' => ['<john@example.org>', '', 'john@example.org'],
            'name with <…>' => ['John <Admin> Doe <john@example.org>', 'John <Admin> Doe', 'john@example.org'],
            'missing closing bracket' => ['Admin <admin@example.org', null, 'Admin <admin@example.org'],
        ];
    }

    /**
     * @dataProvider provideNameAndAddressCases
     * @param string $string
     * @param string|null $expectedName
     * @param string $expectedAddress
     * @return void
     */
    public function testParseNameAndAddress($string, $expectedName, $expectedAddress)
    {
        $parsed = \LimeMailer::parseNameAndAddress($string);
        $this->assertSame($expectedName, $parsed['name']);
        $this->assertSame($expectedAddress, $parsed['address']);
    }

    /**
     * A name containing '<' must not break the recipient (issue #19622), an explicit name "0" is kept
     * @return void
     */
    public function testSetTo()
    {
        $mailer = new \LimeMailer();
        $mailer->setTo('John <Admin> Doe <john@example.org>');
        $this->assertSame([['john@example.org', 'John <Admin> Doe']], $mailer->getToAddresses());

        $mailer->setTo('Other <john@example.org>', '0');
        $this->assertSame([['john@example.org', '0']], $mailer->getToAddresses());
    }

    /**
     * setFrom with a name containing '<', with an empty name, and without a name
     * @return void
     */
    public function testSetFrom()
    {
        $mailer = new \LimeMailer();

        $mailer->setFrom('John <Admin> Doe <john@example.org>');
        $this->assertSame('john@example.org', $mailer->From);
        $this->assertSame('John <Admin> Doe', $mailer->FromName);

        /* «<email>» forces an empty name */
        $mailer->setFrom('<jane@example.org>');
        $this->assertSame('jane@example.org', $mailer->From);
        $this->assertSame('', $mailer->FromName);

        /* No name part: keep the current name */
        $mailer->FromName = 'Current name';
        $mailer->setFrom('team@example.org');
        $this->assertSame('team@example.org', $mailer->From);
        $this->assertSame('Current name', $mailer->FromName);
    }

    /**
     * An HTML email sent to several recipients with the same mailer keeps its links and its HTML
     * for every recipient, not only for the first one
     * @return void
     */
    public function testHtmlEmailToSeveralRecipients()
    {
        $demoMode = \Yii::app()->getConfig('demoMode');
        \Yii::app()->setConfig('demoMode', false);
        $mailer = new class extends \LimeMailer {
            /** @var string[] The MIME bodies of the emails handed over for sending */
            public $sentBodies = [];

            /**
             * Called by PHPMailer for the mailer "capture": Records the email without sending it
             * @param string $header The MIME header
             * @param string $body The MIME body
             * @return bool Always true: The email counts as sent
             */
            protected function captureSend($header, $body)
            {
                $this->sentBodies[] = $body;
                return true;
            }
        };
        $mailer->Mailer = 'capture';
        $mailer->setFrom('admin@example.org', 'Admin');
        $mailer->isHTML(true);
        $mailer->aUrlsPlaceholders = ['VIEWRESPONSE'];
        $mailer->addAndReplaceReplacement(['VIEWRESPONSEURL' => 'http://example.org/response/1']);
        $mailer->rawSubject = 'New response';
        $mailer->rawBody = 'See the response: {VIEWRESPONSEURL}';

        try {
            foreach (['first@example.org', 'second@example.org'] as $recipient) {
                $mailer->setTo($recipient);
                $this->assertTrue($mailer->sendMessage(), (string) $mailer->getError());
                $this->assertTrue($mailer->getIsHtml(), 'The mailer must still know that the email is HTML after sending.');
            }
        } finally {
            \Yii::app()->setConfig('demoMode', $demoMode);
        }

        $this->assertCount(2, $mailer->sentBodies);
        foreach ($mailer->sentBodies as $body) {
            $this->assertStringContainsString('<html>', $body);
            $this->assertStringContainsString('<a href="http://example.org/response/1">', $body);
        }
    }
}
