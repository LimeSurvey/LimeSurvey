<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use PHPUnit\Framework\TestCase;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\QuestionAggregateService\ValidateTrait;

/**
 * @group services
 */
class ValidateCodesTest extends TestCase
{
    private function validate(array $data): void
    {
        $validator = new class {
            use ValidateTrait;
        };
        $validator->validateCodes($data);
    }

    /**
     * @testdox numeric codes with and without leading zeros are different codes
     */
    public function testLeadingZeroCodesAreNotDuplicates(): void
    {
        $this->validate([
            [0 => ['code' => '01']],
            [0 => ['code' => '1']],
            [0 => ['code' => '001']],
            [0 => ['code' => '1E1']],
            [0 => ['code' => '10']],
        ]);
        $this->assertTrue(true);
    }

    /**
     * @testdox identical numeric codes in the same scale are rejected
     */
    public function testIdenticalNumericCodesAreRejected(): void
    {
        $this->expectException(PersistErrorException::class);
        $this->validate([[0 => ['code' => '01'], 1 => ['code' => '1']], [0 => ['code' => '01']]]);
    }

    /**
     * @testdox the same code may exist in different scales
     */
    public function testSameCodeInDifferentScalesIsAllowed(): void
    {
        $this->validate([[0 => ['code' => '1'], 1 => ['code' => '1']]]);
        $this->assertTrue(true);
    }

    /**
     * @testdox integer and string versions of a code are duplicates
     */
    public function testIntegerAndStringCodesAreDuplicates(): void
    {
        $this->expectException(PersistErrorException::class);
        $this->validate([[0 => ['code' => 1]], [0 => ['code' => '1']]]);
    }
}
