<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * {QID}, {GID} and {SGQ} must be replaced in the validation tip, the prefix and the group title and description.
 *
 * @see https://bugs.limesurvey.org/view.php?id=13925
 * @group expression
 */
class ReplacementFieldsTipPrefixGroupTest extends TestBaseClassWeb
{
    /** @var string survey url */
    protected static $surveyUrl;

    /**
     * Import the survey: All in one format with two groups "Group {GID}", each with a short text question
     * using "{QID}-{GID}-{SGQ}" in the question text, the prefix and the validation tip.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_replacement_fields_tip_prefix_group.lss');
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        self::$surveyUrl = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
                'lang' => 'en',
            ]
        );
    }

    /**
     * Each question and group shows its own IDs, without any expression error.
     */
    public function testReplacementFieldsInTipPrefixAndGroup()
    {
        $web = self::$webDriver;
        $questions = $this->getAllSurveyQuestions();
        try {
            $web->get(self::$surveyUrl);

            $groupTitles = $web->findElements(WebDriverBy::cssSelector('.group-title'));
            $groupDescriptions = $web->findElements(WebDriverBy::cssSelector('.group-description'));
            $this->assertCount(2, $groupTitles, 'Both groups are shown');
            $this->assertCount(2, $groupDescriptions, 'Both group descriptions are shown');
            foreach (['Q1', 'Q2'] as $index => $code) {
                $qid = $questions[$code]->qid;
                $gid = $questions[$code]->gid;
                $ids = $qid . '-' . $gid . '-Q' . $qid;

                $this->assertEquals('Group ' . $gid, trim($groupTitles[$index]->getText()), "Group title of $code");
                $this->assertEquals('Description ' . $gid, trim($groupDescriptions[$index]->getText()), "Group description of $code");
                $questionText = $web->findElement(WebDriverBy::cssSelector('#question' . $qid . ' .question-text'))->getText();
                $this->assertEquals('Question ' . $ids, trim($questionText), "Question text of $code");
                $prefix = $web->findElement(WebDriverBy::cssSelector('#question' . $qid . ' .prefix'))->getText();
                $this->assertEquals($ids, trim($prefix), "Prefix of $code");
                $tip = $web->findElement(WebDriverBy::id('vmsg_' . $qid . '_q_fn_validation'))->getText();
                $this->assertEquals('Tip ' . $ids, trim($tip), "Validation tip of $code");
            }
            $this->assertCount(0, $web->findElements(WebDriverBy::cssSelector('.em-error')), 'No expression error is shown');
        } catch (\Exception $e) {
            $filename = __CLASS__ . '_' . __FUNCTION__;
            self::$testHelper->takeScreenshot(self::$webDriver, $filename);
            $this->assertFalse(
                true,
                'Url: ' . self::$surveyUrl . PHP_EOL .
                'Screenshot ' . $filename . PHP_EOL . $e->getMessage()
            );
        }
    }
}
