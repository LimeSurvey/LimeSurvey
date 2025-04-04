<?php

namespace ls\tests;

/**
 * Test expression warning.
 *
 * Test for feature "16263: New config setting for date format and question code"
 * @see https://bugs.limesurvey.org/view.php?id=16263
 */
class QuexmlPDFTest extends TestBaseClass
{
    private static $questions = [];
    /**
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $surveyFile = self::$surveysFolder . '/survey_archive_821351.lsa';
        self::importSurvey($surveyFile);
        /* Login */
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        $rawQuestions = \Question::model()->findAllByAttributes(['sid' => self::$surveyId]);
        foreach ($rawQuestions as $rawQuestion) {
            self::$questions[$rawQuestion->title] = $rawQuestion;
        }
    }

    /**
     * Test format with reformat function disabled.
     */
    public function testKeepDbDateValues()
    {
        // Disable the setting
        \Yii::app()->setConfig('quexmlkeepsurveydateformat', false);

        $xpath = $this->getXPath();

        // Test question with date and time
        $element = $xpath->query("//response[@varName='" . self::$questions['q1']->qid . "']")[0];
        $value = $element->getAttribute("defaultValue");
        $this->assertEquals($value, "2020-05-01 15:50:00", "Unexpected value in question with date and time");

        // Test question with time only
        $element = $xpath->query("//response[@varName='" . self::$questions['q2']->qid . "']")[0];
        $value = $element->getAttribute("defaultValue");
        $this->assertEquals($value, "1970-01-01 15:55:00", "Unexpected value in question with time only");

        // Test question with date and time
        $element = $xpath->query("//response[@varName='" . self::$questions['q3']->qid . "']")[0];
        $value = $element->getAttribute("defaultValue");
        $this->assertEquals($value, "2020-05-02 00:00:00", "Unexpected value in question with date only");
    }

    /**
     * Test format with reformat function enabled.
     */
    public function testKeepSurveyDateFormat()
    {
        // Enable the setting
        \Yii::app()->setConfig('quexmlkeepsurveydateformat', true);

        $xpath = $this->getXPath();

        // Test question with date and time
        $element = $xpath->query("//response[@varName='" . self::$questions['q1']->qid . "']")[0];
        $value = $element->getAttribute("defaultValue");
        $this->assertEquals($value, "05/01/2020 15:50", "Unexpected value in question with date and time");

        // Test question with time only
        $element = $xpath->query("//response[@varName='" . self::$questions['q2']->qid . "']")[0];
        $value = $element->getAttribute("defaultValue");
        $this->assertEquals($value, "15:55", "Unexpected value in question with time only");

        // Test question with date and time
        $element = $xpath->query("//response[@varName='" . self::$questions['q3']->qid . "']")[0];
        $value = $element->getAttribute("defaultValue");
        $this->assertEquals($value, "2020-05-02", "Unexpected value in question with date only");
    }

    /**
     * Test XML generation with function disabled.
     */
    public function testDoNotUseQuestionTitle()
    {
        // Disable the setting
        \Yii::app()->setConfig('quexmlusequestiontitleasid', false);

        $id = $this->getQuestionIdentifier();
        $this->assertEquals($id, "A1.", "Unexpected identifier for question 1.");
    }

    /**
     * Test XML generation with function enabled.
     */
    public function testUseQuestionTitle()
    {
        // Enable the setting
        \Yii::app()->setConfig('quexmlusequestiontitleasid', true);

        $id = $this->getQuestionIdentifier();
        $this->assertEquals($id, self::$questions['q1']->qid . '.', "Unexpected identifier for question 1.");

    }

    /**
     * @return string
     */
    protected function getQuestionIdentifier()
    {
        \Yii::app()->loadHelper('export');
        $quexml = quexml_export(self::$surveyId, 'es-AR', 1);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($quexml);

        \Yii::app()->loadHelper('globalsettings');
        \Yii::import("application.libraries.admin.quexmlpdf", true);
        $quexmlpdf = new \quexmlpdf();
        $quexmlpdf->setLanguage('es-AR');
        $xml = $quexmlpdf->createqueXML($quexml);

        return $xml['sections'][0]['questions'][0]['title'];
    }

    /**
     * @return DOMXpath
     */
    protected function getXPath()
    {
        \Yii::app()->loadHelper('export');
        $quexml = quexml_export(self::$surveyId, 'es-AR', 1);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($quexml);
        return new \DOMXpath($dom);
    }
}
