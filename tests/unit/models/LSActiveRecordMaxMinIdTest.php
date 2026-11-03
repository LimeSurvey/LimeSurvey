<?php

namespace ls\tests;

/**
 * Tests for LSActiveRecord::getMaxId() and LSActiveRecord::getMinId().
 *
 * @see https://bugs.limesurvey.org/view.php?id=18699
 */
class LSActiveRecordMaxMinIdTest extends TestBaseClass
{
    /** @var \Survey Second survey, imported in addition to self::$testSurvey */
    private static $secondSurvey;

    /** @var int[] Response ids inserted into the first survey */
    private static $firstSurveyResponseIds = [];

    /** @var int[] Response ids inserted into the second survey */
    private static $secondSurveyResponseIds = [];

    /**
     * Import and activate two surveys and add a different number of responses to each.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_854771.lss');
        self::$secondSurvey = self::$testSurvey;
        (new \SurveyActivator(self::$secondSurvey))->activate();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss');
        (new \SurveyActivator(self::$testSurvey))->activate();

        for ($i = 0; $i < 3; $i++) {
            self::$firstSurveyResponseIds[] = (int) \SurveyDynamic::model(self::$surveyId)->insertRecords(['startlanguage' => 'en']);
        }
        self::$secondSurveyResponseIds[] = (int) \SurveyDynamic::model(self::$secondSurvey->sid)->insertRecords(['startlanguage' => 'en']);
    }

    /**
     * Delete the second survey; the first one is removed by the parent class.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->session['loginID'] = 1;
        if (self::$secondSurvey) {
            \Yii::app()->db->schema->refresh();
            self::$secondSurvey->delete();
            self::$secondSurvey = null;
        }
        parent::tearDownAfterClass();
    }

    /**
     * getMaxId() must return the value of the table of the current survey,
     * not a value cached for another survey in the same request.
     *
     * @return void
     */
    public function testGetMaxIdForDifferentSurveysInSameRequest()
    {
        $this->assertSame(
            max(self::$firstSurveyResponseIds),
            (int) \SurveyDynamic::model(self::$surveyId)->getMaxId()
        );
        $this->assertSame(
            max(self::$secondSurveyResponseIds),
            (int) \SurveyDynamic::model(self::$secondSurvey->sid)->getMaxId()
        );
    }

    /**
     * getMinId() must return the value of the table of the current survey,
     * not a value cached for another survey in the same request.
     *
     * @return void
     */
    public function testGetMinIdForDifferentSurveysInSameRequest()
    {
        \SurveyDynamic::model(self::$surveyId)->deleteByPk(min(self::$firstSurveyResponseIds));
        $this->assertSame(
            min(self::$firstSurveyResponseIds) + 1,
            (int) \SurveyDynamic::model(self::$surveyId)->getMinId()
        );
        $this->assertSame(
            min(self::$secondSurveyResponseIds),
            (int) \SurveyDynamic::model(self::$secondSurvey->sid)->getMinId()
        );
    }

    /**
     * getMaxId() must reflect records inserted after a previous call.
     *
     * @return void
     */
    public function testGetMaxIdAfterInsert()
    {
        $model = \SurveyDynamic::model(self::$secondSurvey->sid);
        $before = (int) $model->getMaxId();
        $newId = (int) $model->insertRecords(['startlanguage' => 'en']);

        $this->assertGreaterThan($before, $newId);
        $this->assertSame($newId, (int) \SurveyDynamic::model(self::$secondSurvey->sid)->getMaxId());
    }
}
