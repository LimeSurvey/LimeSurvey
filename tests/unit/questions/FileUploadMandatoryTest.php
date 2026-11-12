<?php

namespace ls\tests;

/**
 * A mandatory file upload question must not be accepted when it holds no file,
 * e.g. after all uploaded files were removed.
 *
 * @group fileupload
 */
class FileUploadMandatoryTest extends TestBaseClass
{
    /**
     * Import survey with a file upload question, make it mandatory and activate.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_561859.lss');

        $question = \Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => \Question::QT_VERTICAL_FILE_UPLOAD,
            'parent_qid' => 0,
        ]);
        $question->mandatory = 'Y';
        $question->save();

        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Posted values that do not contain any file.
     * @return array
     */
    public static function emptyUploadValues()
    {
        return [
            'all files removed' => ['[]'],
            'empty object' => ['{}'],
            'not json' => ['x'],
            'json null' => ['null'],
            'nothing posted' => [''],
        ];
    }

    /**
     * @dataProvider emptyUploadValues
     * @param string $postedValue
     */
    public function testMandatoryUploadWithoutFiles($postedValue)
    {
        $_POST = [];
        unset($_SESSION['responses_' . self::$surveyId]);
        $_SESSION['LEMsid'] = self::$surveyId;

        list($question, $group, $sgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \buildsurveysession(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'survey',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            false,
            0
        );
        \LimeExpressionManager::NavigateForwards();

        $_POST['relevance' . $question->qid] = 1;
        $_POST['relevanceG0'] = 1;
        $_POST['movesubmit'] = 'movesubmit';
        $_POST['thisstep'] = 1;
        $_POST['sid'] = self::$surveyId;
        $_POST[$sgqa] = $postedValue;
        $_POST[$sgqa . '_Cfilecount'] = 0;

        $moveResult = \LimeExpressionManager::NavigateForwards();

        $this->assertTrue((bool) $moveResult['mandViolation'], 'Mandatory upload without files must be a violation');
        $this->assertFalse($moveResult['finished'], 'Survey must not be submitted');
        $this->assertSame('', $_SESSION['responses_' . self::$surveyId][$sgqa], 'Unanswered upload is stored as empty string');

        $srid = $_SESSION['responses_' . self::$surveyId]['srid'];
        $response = \Response::model(self::$surveyId)->findByPk($srid);
        $this->assertNotNull($response, 'Response was saved');
        $this->assertSame('', $response->getAttribute($sgqa), 'Stored value is an empty string');
    }
}
