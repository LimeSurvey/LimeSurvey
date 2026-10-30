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
            'no space before bracket' => ['John Doe<john@example.org>', 'John Doe', 'john@example.org'],
            'empty name' => ['<john@example.org>', '', 'john@example.org'],
            'name with <' => ['u <strong <john@example.org>', 'u <strong', 'john@example.org'],
            'name with <…>' => ['John <Admin> Doe <john@example.org>', 'John <Admin> Doe', 'john@example.org'],
            'text with <…> after address' => ['Team <team@example.org> (Sales <EU>)', 'Team', 'team@example.org'],
            'unclosed text after address' => ['Team <team@example.org> <-- reply here', 'Team', 'team@example.org'],
            'missing closing bracket' => ['Admin <admin@example.org', 'Admin', 'admin@example.org'],
            'quoted name' => ['"Doe, John" <john@example.org>', 'Doe, John', 'john@example.org'],
            'quoted name with escaped quote' => ['"John \"JD\" Doe" <john@example.org>', 'John "JD" Doe', 'john@example.org'],
            'surrounding spaces' => ['  John Doe  <  john@example.org  >  ', 'John Doe', 'john@example.org'],
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
     * A name containing '<' must not break the recipient (issue #19622)
     * @return void
     */
    public function testSetToWithNameContainingLessThan()
    {
        $mailer = new \LimeMailer();
        $mailer->setTo('John <Admin> Doe <john@example.org>');
        $this->assertSame([['john@example.org', 'John <Admin> Doe']], $mailer->getToAddresses());
    }

    /**
     * setTo accepts an array of addresses
     * @return void
     */
    public function testSetToWithArray()
    {
        $mailer = new \LimeMailer();
        $mailer->setTo(['John Doe <john@example.org>', 'jane@example.org']);
        $this->assertSame(
            [['john@example.org', 'John Doe'], ['jane@example.org', '']],
            $mailer->getToAddresses()
        );
    }

    /**
     * An explicit name has priority over the name in the address string
     * @return void
     */
    public function testAddAddressWithExplicitName()
    {
        $mailer = new \LimeMailer();
        $mailer->clearAddresses();
        $mailer->addAddress('Other <john@example.org>', 'John Doe');
        $this->assertSame([['john@example.org', 'John Doe']], $mailer->getToAddresses());
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
}
