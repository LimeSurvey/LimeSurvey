<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use DefaultValue;
use DefaultValueL10n;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\QuestionAggregateService\DefaultValuesService;
use ls\tests\TestBaseClass;
use Question;

/**
 * @group services
 */
class DefaultValuesServiceTest extends TestBaseClass
{
    /**
     * Imports a survey with List (radio) questions that have "Other" enabled.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_767665_ListRadioOtherPositionTest.lss');
    }

    /**
     * @testdox save() creates, updates and removes the default answer of a list question
     * @return void
     */
    public function testSaveStoresUpdatesAndRemovesListDefault()
    {
        $question = $this->getListQuestion();
        $service = DI::getContainer()->get(DefaultValuesService::class);

        $service->save($question, ['defaultvalues' => ['en' => ['A1']]]);
        $this->assertSame('A1', $this->getStoredDefault($question->qid, ''));

        $service->save($question, ['defaultvalues' => ['en' => ['A2']]]);
        $this->assertSame('A2', $this->getStoredDefault($question->qid, ''));
        $this->assertEquals(
            1,
            DefaultValue::model()->countByAttributes(['qid' => $question->qid, 'specialtype' => '']),
            'Updating a default value must not create a second entry'
        );

        $service->save($question, ['defaultvalues' => ['en' => ['']]]);
        $this->assertNull($this->getStoredDefault($question->qid, ''));
        $this->assertNull(
            DefaultValue::model()->findByAttributes(['qid' => $question->qid, 'specialtype' => '']),
            'Removing the last translation must remove the default value itself'
        );
    }

    /**
     * @testdox save() stores the default value of the "Other" option separately
     * @return void
     */
    public function testSaveStoresOtherDefault()
    {
        $question = $this->getListQuestion();
        $service = DI::getContainer()->get(DefaultValuesService::class);

        $service->save($question, [
            'defaultvalues' => ['en' => ['']],
            'other' => ['en' => ['Something else']],
        ]);

        $this->assertNull($this->getStoredDefault($question->qid, ''));
        $this->assertSame('Something else', $this->getStoredDefault($question->qid, 'other'));
    }

    /**
     * @testdox save() stores an "Other" default even when no ordinary default is posted
     * @return void
     */
    public function testSaveStoresOtherDefaultWithoutOrdinaryDefault()
    {
        $question = $this->getListQuestion();
        $service = DI::getContainer()->get(DefaultValuesService::class);

        $service->save($question, ['other' => ['en' => ['Only other']]]);

        $this->assertSame('Only other', $this->getStoredDefault($question->qid, 'other'));
    }

    /**
     * @testdox save() does nothing when no default answers are posted
     * @return void
     */
    public function testSaveWithoutDefaultValuesKeepsQuestionUntouched()
    {
        $question = $this->getListQuestion();
        $question->same_default = 1;
        $question->save();
        $service = DI::getContainer()->get(DefaultValuesService::class);

        $service->save($question, []);

        $question->refresh();
        $this->assertSame(1, (int)$question->same_default);
    }

    /**
     * Returns the first List (radio) question of the test survey.
     *
     * @return Question
     */
    private function getListQuestion()
    {
        $question = Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => Question::QT_L_LIST,
            'parent_qid' => 0,
        ]);
        $this->assertNotNull($question);
        return $question;
    }

    /**
     * Returns the stored English default value of a question, or null if there is none.
     *
     * @param int $qid
     * @param string $specialType
     * @return string|null
     */
    private function getStoredDefault($qid, $specialType)
    {
        $defaultValue = DefaultValue::model()->findByAttributes([
            'qid' => $qid,
            'sqid' => 0,
            'scale_id' => 0,
            'specialtype' => $specialType,
        ]);
        if (!$defaultValue) {
            return null;
        }
        $l10n = DefaultValueL10n::model()->findByAttributes([
            'dvid' => $defaultValue->dvid,
            'language' => 'en',
        ]);
        return $l10n ? $l10n->defaultvalue : null;
    }
}
