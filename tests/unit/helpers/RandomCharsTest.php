<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Unit tests for randomChars() in application/helpers/common_helper.php.
 */
class RandomCharsTest extends TestBaseClass
{
    /**
     * Loads the helper under test.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.common_helper', true);
    }

    /**
     * Resets the Mersenne Twister seed so other tests don't get a fixed sequence.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        mt_srand();
        parent::tearDown();
    }

    /**
     * The result has the requested length and only uses characters of the pattern.
     *
     * @return void
     */
    public function testResultUsesRequestedLengthAndPattern(): void
    {
        $result = randomChars(200, 'ABC123');

        $this->assertSame(200, strlen($result));
        $this->assertMatchesRegularExpression('/^[ABC123]+$/', $result);
    }

    /**
     * Every character of the pattern, including the first and the last one, can be picked.
     *
     * @return void
     */
    public function testEveryPatternCharacterCanBePicked(): void
    {
        // With 1000 picks from 6 characters, the chance that one never appears is below 1e-75
        $result = randomChars(1000, 'ABC123');

        $usedCharacters = count_chars($result, 3);
        $this->assertSame('123ABC', $usedCharacters);
    }

    /**
     * The default pattern leaves out characters that are easily confused (0, 1, l, o).
     *
     * @return void
     */
    public function testDefaultPatternIsUsed(): void
    {
        $result = randomChars(200);

        $this->assertSame(200, strlen($result));
        $this->assertMatchesRegularExpression('/^[23456789abcdefghijkmnpqrstuvwxyz]+$/', $result);
    }

    /**
     * The result must not depend on the Mersenne Twister seed: Values generated
     * after the same mt_srand() call have to differ, otherwise anyone knowing
     * one value could work out the others.
     *
     * @return void
     */
    public function testResultDoesNotDependOnMtRandSeed(): void
    {
        mt_srand(12345);
        $first = randomChars(32);
        mt_srand(12345);
        $second = randomChars(32);

        $this->assertNotSame($first, $second);
    }
}
