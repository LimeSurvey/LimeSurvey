<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use LimeExpressionManager;
use Yii;

/**
 * Tests that the survey logic file shows the array filter of a ranking question on the ranking items, not on the ranks.
 */
class SurveyLogicFileRankingArrayFilterTest extends TestBaseClass
{
    /**
     * Imports the survey: Ranking C1x0 (9 items, 7 ranks) is filtered by the multiple choice A123.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_15385_ranking_array_filter.lss');
    }

    /**
     * The ranks have no filter, each ranking item has the filter on its own code.
     */
    public function testArrayFilterIsShownOnRankingItems()
    {
        SetSurveyLanguage(self::$surveyId, '');
        killSurveySession(self::$surveyId);

        $result = LimeExpressionManager::ShowSurveyLogicFile(
            self::$surveyId,
            null,
            null,
            LEM_DEBUG_VALIDATION_SUMMARY + LEM_DEBUG_VALIDATION_DETAIL + LEM_PRETTY_PRINT_ALL_SYNTAX
        );
        $this->assertEmpty($result['errors']);

        $html = str_replace('&nbsp;', ' ', strip_tags($result['html']));
        $rankingPart = substr($html, strpos($html, 'C1x0'));
        // No filter between the ranks
        foreach (range(1, 7) as $rank) {
            $this->assertMatchesRegularExpression('/C1x0_' . $rank . '\s*Rank ' . $rank . '/', $rankingPart);
        }
        // Each ranking item has its own filter, also the items beyond the number of ranks
        foreach (['1' => 'one', '4' => 'four', 'A5' => 'five', 'A9' => 'nine'] as $code => $text) {
            $this->assertMatchesRegularExpression(
                '/' . $code . '\s*\(\( ! is_empty\(A123_' . $code . '\.NAOK\)\)\)( and 1)?\s*' . $text . '/',
                $rankingPart
            );
        }
    }
}
