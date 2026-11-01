<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RobThree\Auth\Algorithm;
use RobThree\Auth\Providers\Time\HttpTimeProvider;
use RobThree\Auth\Providers\Time\NTPTimeProvider;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;

class TwoFactorAuthTest extends TestCase
{
    public function testConstructorThrowsOnInvalidDigits(): void
    {
        $this->expectException(TwoFactorAuthException::class);

        new TwoFactorAuth('Test', 0);
    }

    public function testConstructorThrowsOnInvalidPeriod(): void
    {
        $this->expectException(TwoFactorAuthException::class);

        new TwoFactorAuth('Test', 6, 0);
    }

    public function testGetCodeReturnsCorrectResults(): void
    {
        $tfa = new TwoFactorAuth('Test');
        $this->assertSame('543160', $tfa->getCode('VMR466AB62ZBOKHE', 1426847216));
        $this->assertSame('538532', $tfa->getCode('VMR466AB62ZBOKHE', 0));
    }

    public function testEnsureAllTimeProvidersReturnCorrectTime(): void
    {
        $tfa = new TwoFactorAuth('Test', 6, 30, Algorithm::Sha1);
        $tfa->ensureCorrectTime(array(
            new NTPTimeProvider(),                         // Uses pool.ntp.org by default
            //new \RobThree\Auth\Providers\Time\NTPTimeProvider('time.google.com'),      // Somehow time.google.com and time.windows.com make travis timeout??
            new HttpTimeProvider(),                        // Uses google.com by default
            //new \RobThree\Auth\Providers\Time\HttpTimeProvider('https://github.com'),  // github.com will periodically report times that are off by more than 5 sec
            new HttpTimeProvider('https://yahoo.com'),
        ));
        $this->expectNotToPerformAssertions();
    }

    public function testVerifyCodeWorksCorrectly(): void
    {
        $tfa = new TwoFactorAuth('Test', 6, 30);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847190));
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 0, 1426847190 + 29));    //Test discrepancy
        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 0, 1426847190 + 30));    //Test discrepancy
        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 0, 1426847190 - 1));    //Test discrepancy

        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205));    //Test discrepancy
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205 + 35));    //Test discrepancy
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205 - 35));    //Test discrepancy

        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205 + 65));    //Test discrepancy
        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 1, 1426847205 - 65));    //Test discrepancy

        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 2, 1426847205 + 65));    //Test discrepancy
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 2, 1426847205 - 65));    //Test discrepancy
    }

    public function testVerifyCorrectTimeSliceIsReturned(): void
    {
        $tfa = new TwoFactorAuth('Test', 6, 30);

        // We test with discrepancy 3 (so total of 7 codes: c-3, c-2, c-1, c, c+1, c+2, c+3
        // Ensure each corresponding timeslice is returned correctly
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '534113', 3, 1426847190, $timeslice1));
        $this->assertSame(47561570, $timeslice1);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '819652', 3, 1426847190, $timeslice2));
        $this->assertSame(47561571, $timeslice2);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '915954', 3, 1426847190, $timeslice3));
        $this->assertSame(47561572, $timeslice3);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '543160', 3, 1426847190, $timeslice4));
        $this->assertSame(47561573, $timeslice4);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '348401', 3, 1426847190, $timeslice5));
        $this->assertSame(47561574, $timeslice5);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '648525', 3, 1426847190, $timeslice6));
        $this->assertSame(47561575, $timeslice6);
        $this->assertTrue($tfa->verifyCode('VMR466AB62ZBOKHE', '170645', 3, 1426847190, $timeslice7));
        $this->assertSame(47561576, $timeslice7);

        // Incorrect code should return false and a 0 timeslice
        $this->assertFalse($tfa->verifyCode('VMR466AB62ZBOKHE', '111111', 3, 1426847190, $timeslice8));
        $this->assertSame(0, $timeslice8);
    }

    public function testGetCodeThrowsOnInvalidBase32String1(): void
    {
        $tfa = new TwoFactorAuth('Test');

        $this->expectException(TwoFactorAuthException::class);

        $tfa->getCode('FOO1BAR8BAZ9');    //1, 8 & 9 are invalid chars
    }

    public function testGetCodeThrowsOnInvalidBase32String2(): void
    {
        $tfa = new TwoFactorAuth('Test');

        $this->expectException(TwoFactorAuthException::class);

        $tfa->getCode('mzxw6===');        //Lowercase
    }

    public function testKnownBase32DecodeTestVectors(): void
    {
        // We usually don't test internals (e.g. privates) but since we rely heavily on base32 decoding and don't want
        // to expose this method nor do we want to give people the possibility of implementing / providing their own base32
        // decoding/decoder (as we do with Rng/QR providers for example) we simply test the private base32Decode() method
        // with some known testvectors **only** to ensure base32 decoding works correctly following RFC's so there won't
        // be any bugs hiding in there. We **could** 'fool' ourselves by calling the public getCode() method (which uses
        // base32decode internally) and then make sure getCode's output (in digits) equals expected output since that would
        // mean the base32Decode() works as expected but that **could** hide some subtle bug(s) in decoding the base32 string.

        // "In general, you don't want to break any encapsulation for the sake of testing (or as Mom used to say, "don't
        // expose your privates!"). Most of the time, you should be able to test a class by exercising its public methods."
        //                                                           Dave Thomas and Andy Hunt -- "Pragmatic Unit Testing
        $tfa = new TwoFactorAuth('Test');

        $method = new ReflectionMethod(TwoFactorAuth::class, 'base32Decode');

        // Test vectors from: https://tools.ietf.org/html/rfc4648#page-12
        $this->assertSame('', $method->invoke($tfa, ''));
        $this->assertSame('f', $method->invoke($tfa, 'MY======'));
        $this->assertSame('fo', $method->invoke($tfa, 'MZXQ===='));
        $this->assertSame('foo', $method->invoke($tfa, 'MZXW6==='));
        $this->assertSame('foob', $method->invoke($tfa, 'MZXW6YQ='));
        $this->assertSame('fooba', $method->invoke($tfa, 'MZXW6YTB'));
        $this->assertSame('foobar', $method->invoke($tfa, 'MZXW6YTBOI======'));
    }

    public function testKnownBase32DecodeUnpaddedTestVectors(): void
    {
        // See testKnownBase32DecodeTestVectors() for the rationale behind testing the private base32Decode() method.
        // This test ensures that strings without the padding-char ('=') are also decoded correctly.
        // https://tools.ietf.org/html/rfc4648#page-4:
        //   "In some circumstances, the use of padding ("=") in base-encoded data is not required or used."
        $tfa = new TwoFactorAuth('Test');

        $method = new ReflectionMethod(TwoFactorAuth::class, 'base32Decode');

        // Test vectors from: https://tools.ietf.org/html/rfc4648#page-12
        $this->assertSame('', $method->invoke($tfa, ''));
        $this->assertSame('f', $method->invoke($tfa, 'MY'));
        $this->assertSame('fo', $method->invoke($tfa, 'MZXQ'));
        $this->assertSame('foo', $method->invoke($tfa, 'MZXW6'));
        $this->assertSame('foob', $method->invoke($tfa, 'MZXW6YQ'));
        $this->assertSame('fooba', $method->invoke($tfa, 'MZXW6YTB'));
        $this->assertSame('foobar', $method->invoke($tfa, 'MZXW6YTBOI'));
    }

    public function testKnownTestVectors_sha1(): void
    {
        //Known test vectors for SHA1: https://tools.ietf.org/html/rfc6238#page-15
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';   //== base32encode('12345678901234567890')
        $tfa = new TwoFactorAuth('Test', 8, 30, Algorithm::Sha1);
        $this->assertSame('94287082', $tfa->getCode($secret, 59));
        $this->assertSame('07081804', $tfa->getCode($secret, 1111111109));
        $this->assertSame('14050471', $tfa->getCode($secret, 1111111111));
        $this->assertSame('89005924', $tfa->getCode($secret, 1234567890));
        $this->assertSame('69279037', $tfa->getCode($secret, 2000000000));
        $this->assertSame('65353130', $tfa->getCode($secret, 20000000000));
    }

    public function testKnownTestVectors_sha256(): void
    {
        //Known test vectors for SHA256: https://tools.ietf.org/html/rfc6238#page-15
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZA';   //== base32encode('12345678901234567890123456789012')
        $tfa = new TwoFactorAuth('Test', 8, 30, Algorithm::Sha256);
        $this->assertSame('46119246', $tfa->getCode($secret, 59));
        $this->assertSame('68084774', $tfa->getCode($secret, 1111111109));
        $this->assertSame('67062674', $tfa->getCode($secret, 1111111111));
        $this->assertSame('91819424', $tfa->getCode($secret, 1234567890));
        $this->assertSame('90698825', $tfa->getCode($secret, 2000000000));
        $this->assertSame('77737706', $tfa->getCode($secret, 20000000000));
    }

    public function testKnownTestVectors_sha512(): void
    {
        //Known test vectors for SHA512: https://tools.ietf.org/html/rfc6238#page-15
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQGEZDGNA';   //== base32encode('1234567890123456789012345678901234567890123456789012345678901234')
        $tfa = new TwoFactorAuth('Test', 8, 30, Algorithm::Sha512);
        $this->assertSame('90693936', $tfa->getCode($secret, 59));
        $this->assertSame('25091201', $tfa->getCode($secret, 1111111109));
        $this->assertSame('99943326', $tfa->getCode($secret, 1111111111));
        $this->assertSame('93441116', $tfa->getCode($secret, 1234567890));
        $this->assertSame('38618901', $tfa->getCode($secret, 2000000000));
        $this->assertSame('47863826', $tfa->getCode($secret, 20000000000));
    }
}
