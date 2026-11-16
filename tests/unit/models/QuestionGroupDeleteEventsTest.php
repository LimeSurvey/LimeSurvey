<?php

namespace ls\tests;

use Question;
use QuestionGroup;
use Survey;

/**
 * Tests that deleting a question group raises the question group delete plugin events.
 */
class QuestionGroupDeleteEventsTest extends TestBaseClass
{
    /**
     * Import a fresh copy of the test survey, so the deleted group doesn't affect other tests.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_594264_getGroupDescription.lss');
    }

    /**
     * Delete the survey imported in setUp().
     *
     * @return void
     */
    public function tearDown(): void
    {
        \Yii::app()->session['loginID'] = 1;
        Survey::model()->deleteSurvey(self::$surveyId);
        self::$testSurvey = null;
        parent::tearDown();
    }

    /**
     * The before and after question group delete events are dispatched once each, and the group's
     * questions still exist when they are raised, so plugins can see what is deleted with the group.
     *
     * @return void
     */
    public function testDeleteWithDependencyRaisesGroupDeleteEvents(): void
    {
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0]);
        $this->assertNotEmpty($question, 'The test survey has no question.');
        $gid = $question->gid;
        $questionCount = (int) Question::model()->countByAttributes(['gid' => $gid]);

        require_once __DIR__ . '/../../data/plugins/QuestionGroupDeleteEventsPlugin.php';
        $pluginManager = App()->getPluginManager();
        $plugin = new \QuestionGroupDeleteEventsPlugin($pluginManager, 0);
        $plugin->init();
        try {
            $this->assertSame(1, QuestionGroup::deleteWithDependency($gid));
        } finally {
            $pluginManager->unsubscribe($plugin, 'beforeQuestionGroupDelete');
            $pluginManager->unsubscribe($plugin, 'afterQuestionGroupDelete');
        }

        $this->assertSame(
            [
                'beforeQuestionGroupDelete' => [$questionCount],
                'afterQuestionGroupDelete' => [$questionCount],
            ],
            $plugin->questionCounts
        );
        $this->assertNull(QuestionGroup::model()->findByPk($gid));
        $this->assertSame(0, (int) Question::model()->countByAttributes(['gid' => $gid]));
    }
}
