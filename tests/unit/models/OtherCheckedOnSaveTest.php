<?php

namespace ls\tests;

/**
 * Saving a response stores the multiple choice Other tick that goes with the Other text.
 */
class OtherCheckedOnSaveTest extends TestBaseClass
{
    /** @var string Q{qid}_Cother of the not mandatory question */
    private static $otherColumn;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_213203_other_checked.lss');
        self::$testHelper->activateSurvey(self::$surveyId);
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'q1']);
        self::$otherColumn = 'Q' . $question->qid . '_Cother';
    }

    public function testTextTicksOther()
    {
        $row = $this->insert([self::$otherColumn => 'some text']);
        $this->assertSame('Y', $row[self::$otherColumn . 'checked']);
    }

    public function testTickWithoutTextIsKept()
    {
        $row = $this->insert([self::$otherColumn => '', self::$otherColumn . 'checked' => 'Y']);
        $this->assertSame('Y', $row[self::$otherColumn . 'checked']);
    }

    public function testNoTextLeavesTickEmpty()
    {
        $row = $this->insert([self::$otherColumn => '']);
        $this->assertEmpty($row[self::$otherColumn . 'checked']);
    }

    public function testResponseEditTicksOther()
    {
        $id = $this->insert([self::$otherColumn => ''])['id'];
        $response = \Response::model(self::$surveyId)->findByPk($id);
        $response->setAttribute(self::$otherColumn, 'edited');
        $this->assertTrue($response->encryptSave());
        $this->assertSame('Y', $this->read($id)[self::$otherColumn . 'checked']);
    }

    public function testSavingOnlyTheTextAlsoSavesTheTick()
    {
        $id = $this->insert([self::$otherColumn => ''])['id'];
        $response = \SurveyDynamic::model(self::$surveyId)->findByPk($id);
        $response->setAttribute(self::$otherColumn, 'only text');
        $this->assertTrue($response->encryptSave(true, [self::$otherColumn]));
        $this->assertSame('Y', $this->read($id)[self::$otherColumn . 'checked']);
    }

    public function testSavingOnlyAnEmptyTickKeepsTheTickOfTheText()
    {
        $id = $this->insert([self::$otherColumn => 'some text'])['id'];
        $response = \SurveyDynamic::model(self::$surveyId)->findByPk($id);
        $response->setAttribute(self::$otherColumn . 'checked', '');
        $this->assertTrue($response->encryptSave(true, [self::$otherColumn . 'checked']));
        $this->assertSame('Y', $this->read($id)[self::$otherColumn . 'checked']);
    }

    /**
     * Insert a response like the imports and RemoteControl do
     * @param array $attributes
     * @return array the saved row
     */
    private function insert(array $attributes)
    {
        \SurveyDynamic::sid(self::$surveyId);
        $response = new \SurveyDynamic();
        $defaults = ['startlanguage' => 'en'];
        foreach (['startdate', 'datestamp'] as $dateColumn) {
            if ($response->hasAttribute($dateColumn)) {
                $defaults[$dateColumn] = date('Y-m-d H:i:s');
            }
        }
        $response->setAttributes(array_merge($defaults, $attributes), false);
        $this->assertTrue($response->encryptSave());
        return $this->read($response->id);
    }

    /**
     * @param integer $id
     * @return array the row as stored
     */
    private function read($id)
    {
        return \Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{responses_' . self::$surveyId . '}}')
            ->where('id = :id', [':id' => $id])
            ->queryRow();
    }
}
