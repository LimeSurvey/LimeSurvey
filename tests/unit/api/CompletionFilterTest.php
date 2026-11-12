<?php

namespace ls\tests\unit\api;

use CDbCriteria;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\CompletionFilter;
use PHPUnit\Framework\TestCase;

class CompletionFilterTest extends TestCase
{
    public function testCompletionStatesUseDatabasePredicates(): void
    {
        foreach ([
            'all' => '',
            'completed' => 'submitdate IS NOT NULL',
            'incomplete' => 'submitdate IS NULL AND (quota_exit IS NULL OR quota_exit = 0)',
            'screened_out' => 'quota_exit IS NOT NULL AND quota_exit <> 0',
        ] as $state => $expected) {
            $criteria = new CDbCriteria();
            CompletionFilter::apply($criteria, $state, ['submitdate', 'quota_exit']);
            $this->assertSame($expected, $criteria->condition);
        }
    }

    public function testScreenedOutRequiresQuotaColumn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CompletionFilter::apply(new CDbCriteria(), 'screened_out', ['submitdate']);
    }

    public function testIncompleteWithoutQuotaColumnOnlyChecksSubmitdate(): void
    {
        $this->assertSame('submitdate IS NULL', CompletionFilter::predicate('incomplete', false));
        $this->assertNull(CompletionFilter::predicate('all', true));
    }

    public function testUnknownStatesAreRejected(): void
    {
        foreach (['COMPLETED', 'complete', '', 'typo'] as $state) {
            try {
                CompletionFilter::assertValid($state);
                $this->fail("$state should be rejected");
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('completed', $e->getMessage());
            }
        }
    }

    public function testApplyDoesNotTouchTheCriteriaForAll(): void
    {
        $criteria = new CDbCriteria();
        CompletionFilter::apply($criteria, 'all', []);
        $this->assertSame('', $criteria->condition);
    }
}
