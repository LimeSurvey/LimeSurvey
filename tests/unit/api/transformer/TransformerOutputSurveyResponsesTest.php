<?php

namespace ls\tests\unit\api;

use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use ls\tests\TestBaseClass;

/**
 * @testdox Survey response output transformer
 */
class TransformerOutputSurveyResponsesTest extends TestBaseClass
{
    /**
     * @testdox Quota exit name is null when survey context is missing
     */
    public function testQuotaExitNameIsNullWithoutSurveyContext(): void
    {
        $transformer = new TransformerOutputSurveyResponses();

        $result = $transformer->transform([$this->makeResponse(12)]);

        $this->assertNull($result[0]['quotaExitName']);
    }

    /**
     * @testdox Quota exit name is null when quota ID does not match
     */
    public function testQuotaExitNameIsNullForUnmatchedQuotaId(): void
    {
        $transformer = new TransformerOutputSurveyResponses();
        $transformer->hasTokenTable = false;
        $survey = $this->makeSurvey([
            (object) ['id' => 7, 'name' => 'Completed quota'],
        ]);

        $result = $transformer->transform(
            [$this->makeResponse(12)],
            ['survey' => $survey]
        );

        $this->assertNull($result[0]['quotaExitName']);
    }

    /**
     * @testdox Quota exit name is resolved from survey context
     */
    public function testQuotaExitNameIsResolvedFromSurveyContext(): void
    {
        $transformer = new TransformerOutputSurveyResponses();
        $transformer->hasTokenTable = false;
        $survey = $this->makeSurvey([
            (object) ['id' => 12, 'name' => 'Completed quota'],
        ]);

        $result = $transformer->transform(
            [$this->makeResponse(12)],
            ['survey' => $survey]
        );

        $this->assertSame('Completed quota', $result[0]['quotaExitName']);
    }

    private function makeResponse(int $quotaId): \SurveyDynamic
    {
        return new class (['quota_exit' => $quotaId]) extends \SurveyDynamic {
            private array $testAttributes;

            public function __construct(array $attributes)
            {
                $this->testAttributes = $attributes;
            }

            public function getAttributes($names = true)
            {
                return $this->testAttributes;
            }

            public function __get($name)
            {
                if ($name === 'attributes') {
                    return $this->getAttributes();
                }

                return parent::__get($name);
            }
        };
    }

    private function makeSurvey(array $quotas): object
    {
        return (object) [
            'sid' => 123456,
            'quotas' => $quotas,
            'anonymized' => 'Y',
        ];
    }
}
