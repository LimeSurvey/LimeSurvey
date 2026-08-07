<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyUseCaptcha;

/**
 * Pure-unit tests for SurveyUseCaptcha.
 *
 * These tests exercise the encoding, decoding, merging and recalculation
 * logic without touching the database.
 */
class SurveyUseCaptchaTest extends TestBaseClass
{
    /** @var SurveyUseCaptcha */
    private static $encoder;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $survey = new \Survey();
        $survey->usecaptcha = 'N';
        self::$encoder = new SurveyUseCaptcha(null, $survey);
    }

    public static function tearDownAfterClass(): void
    {
        self::$encoder = null;
        parent::tearDownAfterClass();
    }

    // ------------------------------------------------------------------
    // convertUseCaptchaForDB / convertUseCaptchaFromDB round-trip
    // ------------------------------------------------------------------

    /**
     * Encodes all 27 combinations of Y/N/I for the three screens via
     * convertUseCaptchaForDB, then decodes with convertUseCaptchaFromDB
     * and asserts the original values are recovered.
     * Ensures every encoded char belongs to the USE_CAPTCHA map.
     *
     * @dataProvider provideEncodingRoundTripCases
     */
    public function testEncodingRoundTrip(string $surveyAccess, string $registration, string $saveAndLoad)
    {
        $encoded = self::$encoder->convertUseCaptchaForDB($surveyAccess, $registration, $saveAndLoad);

        $this->assertContains(
            $encoded,
            SurveyUseCaptcha::USE_CAPTCHA,
            sprintf('Encoded value "%s" not in USE_CAPTCHA map (input: %s/%s/%s)', $encoded, $surveyAccess, $registration, $saveAndLoad)
        );

        $decoded = self::$encoder->convertUseCaptchaFromDB($encoded);

        $this->assertSame($surveyAccess, $decoded['surveyAccess'], "surveyAccess round-trip failed for {$encoded}");
        $this->assertSame($registration, $decoded['registration'], "registration round-trip failed for {$encoded}");
        $this->assertSame($saveAndLoad, $decoded['saveAndLoad'], "saveAndLoad round-trip failed for {$encoded}");
    }

    /**
     * All 27 combinations of Y/N/I for the three screens.
     */
    public static function provideEncodingRoundTripCases(): array
    {
        $cases = [];
        $triValues = ['Y', 'N', 'I'];

        foreach ($triValues as $sa) {
            foreach ($triValues as $reg) {
                foreach ($triValues as $sl) {
                    $label = sprintf('sa=%s reg=%s sl=%s', $sa, $reg, $sl);
                    $cases[$label] = [$sa, $reg, $sl];
                }
            }
        }

        return $cases;
    }

    // ------------------------------------------------------------------
    // mergeUseCaptchaValues
    // ------------------------------------------------------------------

    /**
     * Verifies one-level inheritance resolution: 'I' components in $current
     * are replaced by the corresponding value from $parent, while explicit
     * Y/N components are kept.
     *
     * @dataProvider provideMergeUseCaptchaCases
     */
    public function testMergeUseCaptchaValues(string $current, string $parent, array $expected)
    {
        $merged = self::$encoder->mergeUseCaptchaValues($current, $parent);
        $decoded = self::$encoder->convertUseCaptchaFromDB($merged);

        $this->assertSame($expected['surveyAccess'], $decoded['surveyAccess'], 'surveyAccess mismatch after merge');
        $this->assertSame($expected['registration'], $decoded['registration'], 'registration mismatch after merge');
        $this->assertSame($expected['saveAndLoad'], $decoded['saveAndLoad'], 'saveAndLoad mismatch after merge');
    }

    public static function provideMergeUseCaptchaCases(): array
    {
        $enc = new SurveyUseCaptcha(null, new \Survey());

        return [
            'all inherit from all-Y parent' => [
                $enc->convertUseCaptchaForDB('I', 'I', 'I'), // E
                $enc->convertUseCaptchaForDB('Y', 'Y', 'Y'), // A
                ['surveyAccess' => 'Y', 'registration' => 'Y', 'saveAndLoad' => 'Y'],
            ],
            'all inherit from all-N parent' => [
                $enc->convertUseCaptchaForDB('I', 'I', 'I'), // E
                $enc->convertUseCaptchaForDB('N', 'N', 'N'), // N
                ['surveyAccess' => 'N', 'registration' => 'N', 'saveAndLoad' => 'N'],
            ],
            'no inherit keeps own values' => [
                $enc->convertUseCaptchaForDB('Y', 'N', 'Y'), // C
                $enc->convertUseCaptchaForDB('N', 'Y', 'N'), // R
                ['surveyAccess' => 'Y', 'registration' => 'N', 'saveAndLoad' => 'Y'],
            ],
            'partial inherit: sa=I picks parent, rest kept' => [
                $enc->convertUseCaptchaForDB('I', 'Y', 'N'), // M
                $enc->convertUseCaptchaForDB('N', 'N', 'Y'), // S
                ['surveyAccess' => 'N', 'registration' => 'Y', 'saveAndLoad' => 'N'],
            ],
            'partial inherit: reg=I picks parent, rest kept' => [
                $enc->convertUseCaptchaForDB('Y', 'I', 'N'), // O
                $enc->convertUseCaptchaForDB('N', 'Y', 'Y'), // D
                ['surveyAccess' => 'Y', 'registration' => 'Y', 'saveAndLoad' => 'N'],
            ],
            'partial inherit: sl=I picks parent, rest kept' => [
                $enc->convertUseCaptchaForDB('N', 'Y', 'I'), // U
                $enc->convertUseCaptchaForDB('Y', 'N', 'Y'), // C
                ['surveyAccess' => 'N', 'registration' => 'Y', 'saveAndLoad' => 'Y'],
            ],
            'inherit from parent that also has inherit' => [
                $enc->convertUseCaptchaForDB('I', 'I', 'I'), // E
                $enc->convertUseCaptchaForDB('I', 'Y', 'I'), // I
                ['surveyAccess' => 'I', 'registration' => 'Y', 'saveAndLoad' => 'I'],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // reCalculateUseCaptcha
    // ------------------------------------------------------------------

    /**
     * Verifies partial update of the packed captcha value: only the keys
     * present in $data are changed, the rest are kept from the survey's
     * current usecaptcha.
     *
     * @dataProvider provideReCalculateCases
     */
    public function testReCalculateUseCaptcha(string $initial, array $data, array $expected)
    {
        $survey = new \Survey();
        $survey->usecaptcha = $initial;
        $encoder = new SurveyUseCaptcha(null, $survey);

        $result = $encoder->reCalculateUseCaptcha($data);
        $decoded = self::$encoder->convertUseCaptchaFromDB($result);

        $this->assertSame($expected['surveyAccess'], $decoded['surveyAccess'], 'surveyAccess mismatch after recalc');
        $this->assertSame($expected['registration'], $decoded['registration'], 'registration mismatch after recalc');
        $this->assertSame($expected['saveAndLoad'], $decoded['saveAndLoad'], 'saveAndLoad mismatch after recalc');
    }

    public static function provideReCalculateCases(): array
    {
        $enc = new SurveyUseCaptcha(null, new \Survey());

        $allY = $enc->convertUseCaptchaForDB('Y', 'Y', 'Y');
        $allN = $enc->convertUseCaptchaForDB('N', 'N', 'N');

        return [
            'change surveyAccess only' => [
                $allN,
                ['useCaptchaAccess' => 'Y'],
                ['surveyAccess' => 'Y', 'registration' => 'N', 'saveAndLoad' => 'N'],
            ],
            'change registration only' => [
                $allN,
                ['useCaptchaRegistration' => 'Y'],
                ['surveyAccess' => 'N', 'registration' => 'Y', 'saveAndLoad' => 'N'],
            ],
            'change saveAndLoad only' => [
                $allN,
                ['useCaptchaSaveLoad' => 'Y'],
                ['surveyAccess' => 'N', 'registration' => 'N', 'saveAndLoad' => 'Y'],
            ],
            'change all three at once' => [
                $allN,
                ['useCaptchaAccess' => 'Y', 'useCaptchaRegistration' => 'Y', 'useCaptchaSaveLoad' => 'Y'],
                ['surveyAccess' => 'Y', 'registration' => 'Y', 'saveAndLoad' => 'Y'],
            ],
            'empty data keeps everything' => [
                $allY,
                [],
                ['surveyAccess' => 'Y', 'registration' => 'Y', 'saveAndLoad' => 'Y'],
            ],
            'set to inherit' => [
                $allY,
                ['useCaptchaAccess' => 'I', 'useCaptchaRegistration' => 'I', 'useCaptchaSaveLoad' => 'I'],
                ['surveyAccess' => 'I', 'registration' => 'I', 'saveAndLoad' => 'I'],
            ],
            'mixed partial update from all-Y' => [
                $allY,
                ['useCaptchaAccess' => 'N', 'useCaptchaSaveLoad' => 'I'],
                ['surveyAccess' => 'N', 'registration' => 'Y', 'saveAndLoad' => 'I'],
            ],
        ];
    }
}
