<?php

namespace ls\tests;

/**
 * Access code length must be -1 (inherit) or between Token::MIN_LENGTH and Token::MAX_LENGTH,
 * both in survey settings and in survey group/global settings.
 */
class SurveyTokenLengthValidationTest extends TestBaseClass
{
    /**
     * Provide access code lengths that must be accepted.
     *
     * @return array<string, array{0: int|string}>
     */
    public function validTokenLengthProvider(): array
    {
        return [
            'inherit' => [-1],
            'minimum' => [\Token::MIN_LENGTH],
            'default' => [\Token::DEFAULT_LENGTH],
            'maximum' => [\Token::MAX_LENGTH],
            'numeric string' => ['20'],
        ];
    }

    /**
     * Provide access code lengths that must be rejected.
     *
     * @return array<string, array{0: int|string}>
     */
    public function invalidTokenLengthProvider(): array
    {
        return [
            'below minimum' => [\Token::MIN_LENGTH - 1],
            'zero' => [0],
            'below inherit' => [-2],
            'above maximum' => [\Token::MAX_LENGTH + 1],
            'numeric string below minimum' => ['4'],
        ];
    }

    /**
     * Test that valid access code lengths pass the survey validation.
     *
     * @dataProvider validTokenLengthProvider
     * @param int|string $tokenLength The access code length to check
     * @return void
     */
    public function testSurveyAcceptsValidTokenLength($tokenLength): void
    {
        $survey = new \Survey();
        $survey->tokenlength = $tokenLength;
        $this->assertTrue($survey->validate(['tokenlength']), print_r($survey->getErrors(), true));
    }

    /**
     * Test that invalid access code lengths fail the survey validation with an error message.
     *
     * @dataProvider invalidTokenLengthProvider
     * @param int|string $tokenLength The access code length to check
     * @return void
     */
    public function testSurveyRejectsInvalidTokenLength($tokenLength): void
    {
        $survey = new \Survey();
        $survey->tokenlength = $tokenLength;
        $this->assertFalse($survey->validate(['tokenlength']));
        $this->assertSame([\Survey::getInvalidTokenLengthMessage()], $survey->getErrors('tokenlength'));
    }

    /**
     * Test that valid access code lengths pass the survey group settings validation.
     *
     * @dataProvider validTokenLengthProvider
     * @param int|string $tokenLength The access code length to check
     * @return void
     */
    public function testSurveysGroupsettingsAcceptsValidTokenLength($tokenLength): void
    {
        $settings = new \SurveysGroupsettings();
        $settings->tokenlength = $tokenLength;
        $this->assertTrue($settings->validate(['tokenlength']), print_r($settings->getErrors(), true));
    }

    /**
     * Test that invalid access code lengths fail the survey group settings validation with an error message.
     *
     * @dataProvider invalidTokenLengthProvider
     * @param int|string $tokenLength The access code length to check
     * @return void
     */
    public function testSurveysGroupsettingsRejectsInvalidTokenLength($tokenLength): void
    {
        $settings = new \SurveysGroupsettings();
        $settings->tokenlength = $tokenLength;
        $this->assertFalse($settings->validate(['tokenlength']));
        $this->assertSame([\Survey::getInvalidTokenLengthMessage()], $settings->getErrors('tokenlength'));
    }
}
