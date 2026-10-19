<?php


//namespace unit\models;
namespace ls\tests;


use LimeSurvey\Models\Services\IpAddressAnonymizer;
use PHPUnit\Framework\TestCase;

class IpAddressAnonymizerTest extends TestCase
{

    /**
     * Test it is ipv4
     */
    public function testIsIpV4()
    {
        $ipToBeTested = '192.168.3.1';
        $ipAnonymizer = new IpAddressAnonymizer($ipToBeTested);

        $this->assertTrue($ipAnonymizer->isIpv4());
    }

    /**
     * Test not ipv4
     */
    public function testIsNotIpv4(){
        $ipToBeTested = '192.168.3.';
        $ipAnonymizer = new IpAddressAnonymizer($ipToBeTested);

        $this->assertFalse($ipAnonymizer->isIpv4());
    }

    /**
     * Test it is ipv6
     */
    public function testIsIpV6(){
        $ipToBeTested = '2a03:2880:2117:df07:face:b00c:5:1';
        $ipToBeTested2 = '2a03:2880:2117:df07::5:1';
        $ipAnonymizer = new IpAddressAnonymizer($ipToBeTested);

        $this->assertTrue($ipAnonymizer->isIpv6());
        $ipAnonymizer = new IpAddressAnonymizer($ipToBeTested2);
        $this->assertTrue($ipAnonymizer->isIpv6());
    }

    /**
     * Test not ipv6
     */
    public function testIsNotIpv6(){
        $ipToBeTested = '192.3.4.5';
        $ipAnonymizer = new IpAddressAnonymizer($ipToBeTested);

        $this->assertFalse($ipAnonymizer->isIpv6());
    }

    /**
     * Test ip anonymization with ipv4
     * 192.168.3.4 is anomymized to 192.168.3.4
     */
    public function testIpAnonymizeIpv4(){
        $ipToBeTested = '192.168.3.4';
        $ipAnonymizer = new IpAddressAnonymizer($ipToBeTested);

        $this->assertEquals('192.168.3.0', $ipAnonymizer->anonymizeIpAddress());
    }

    /**
     * Test ip anonymization with ipv6
     *
     * 2a03:2880:2117:df07:face:b00c:5:1 is anonymized to 2a03:2880:2117:0:0:0:0:0
     */
    public function testIpAnonymizeIpv6(){
        $ipToBeTested = '2a03:2880:2117:df07:face:b00c:5:1';
        $ipAnonymizer = new IpAddressAnonymizer($ipToBeTested);

        $this->assertEquals('2a03:2880:2117:0:0:0:0:0', $ipAnonymizer->anonymizeIpAddress());
    }

    /**
     * Test ip anonymization with a "::"-compressed ipv6 address. Previously the loopback
     * address "::1" was anonymized to garbage ("':0:0:0:0:0'") because explode(':', ...)
     * on a compressed address doesn't yield the 8 groups the algorithm assumes.
     *
     * ::1 is fully "0000:0000:0000:0000:0000:0000:0000:0001", so it is anonymized to
     * 0000:0000:0000:0:0:0:0:0
     */
    public function testIpAnonymizeCompressedIpv6(){
        $ipToBeTested = '::1';
        $ipAnonymizer = new IpAddressAnonymizer($ipToBeTested);

        $this->assertEquals('0000:0000:0000:0:0:0:0:0', $ipAnonymizer->anonymizeIpAddress());
    }

    /**
     * Test ip anonymization with a "::"-compressed ipv6 address that has non-zero
     * groups on both sides of the compression.
     *
     * 2a03:2880::5:1 is fully "2a03:2880:0000:0000:0000:0000:0005:0001", so it is
     * anonymized to 2a03:2880:0000:0:0:0:0:0
     */
    public function testIpAnonymizeCompressedIpv6WithLeadingGroups(){
        $ipToBeTested = '2a03:2880::5:1';
        $ipAnonymizer = new IpAddressAnonymizer($ipToBeTested);

        $this->assertEquals('2a03:2880:0000:0:0:0:0:0', $ipAnonymizer->anonymizeIpAddress());
    }

}