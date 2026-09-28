<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

class SurveyURLParameterTest extends TestCase
{
    /**
     * Provide parameter names that must be accepted.
     *
     * @return array<string, array{0: string}>
     */
    public function validParameterNameProvider(): array
    {
        return [
            'letters only' => ['source'],
            'leading underscore' => ['_source'],
            'with digits' => ['panel2'],
            'with hyphen' => ['test-test'],
            'trailing hyphen' => ['test-'],
            'mixed' => ['Panel_ID-2'],
        ];
    }

    /**
     * Provide parameter names that must be rejected.
     *
     * @return array<string, array{0: string}>
     */
    public function invalidParameterNameProvider(): array
    {
        return [
            'empty' => [''],
            'leading hyphen' => ['-test'],
            'leading digit' => ['2panel'],
            'dot' => ['test.test'],
            'space' => ['test test'],
            'reserved sid' => ['sid'],
            'reserved newtest' => ['newtest'],
            'reserved token' => ['token'],
            'reserved lang' => ['lang'],
        ];
    }

    /**
     * Test that valid parameter names, including hyphenated ones, are accepted.
     *
     * @dataProvider validParameterNameProvider
     * @param string $parameterName The parameter name to check
     * @return void
     */
    public function testValidParameterNameIsAccepted(string $parameterName): void
    {
        $this->assertTrue(\SurveyURLParameter::isValidParameterName($parameterName));
    }

    /**
     * Test that invalid or reserved parameter names are rejected.
     *
     * @dataProvider invalidParameterNameProvider
     * @param string $parameterName The parameter name to check
     * @return void
     */
    public function testInvalidParameterNameIsRejected(string $parameterName): void
    {
        $this->assertFalse(\SurveyURLParameter::isValidParameterName($parameterName));
    }
}
