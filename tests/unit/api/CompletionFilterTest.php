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
            'incomplete' => 'submitdate IS NULL',
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
}
