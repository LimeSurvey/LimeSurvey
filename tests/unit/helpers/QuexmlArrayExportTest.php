<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use ls\tests\TestHelper;

/**
 * Response values of Array (Numbers) and Array (Text) questions must appear
 * as defaultValue in the queXML export used for the queXML PDF response export.
 *
 * @see https://bugs.limesurvey.org/view.php?id=20819
 */
class QuexmlArrayExportTest extends TestBaseClass
{
    /**
     * Import and activate the survey.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_20819_quexml_array_numbers.lss');
        (new TestHelper())->activateSurvey(self::$surveyId);
    }

    /**
     * Deactivate the survey so the response table is removed with it.
     */
    public static function tearDownAfterClass(): void
    {
        (new TestHelper())->deactivateSurvey(self::$surveyId);
        parent::tearDownAfterClass();
    }

    /**
     * Every cell of both array questions keeps its stored value in the export.
     */
    public function testArrayCellValuesAreExported()
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $fieldmap = createFieldMap($survey, 'short', false, false, 'en');
        $expected = [];
        $values = [];
        $i = 1;
        foreach ($fieldmap as $fieldname => $field) {
            if (in_array($field['type'], [\Question::QT_COLON_ARRAY_NUMBERS, \Question::QT_SEMICOLON_ARRAY_TEXT], true)) {
                $values[$fieldname] = $field['type'] === \Question::QT_COLON_ARRAY_NUMBERS ? (string) $i : 'text ' . $i;
                // Column Q<qid>_S<row sqid>_S<column sqid> is exported with varName <qid>_S<row sqid>_<column sqid>
                $expected[preg_replace('/^Q(\d+)_S(\d+)_S(\d+)$/', '$1_S$2_$3', $fieldname)] = $values[$fieldname];
                $i++;
            }
        }
        $this->assertCount(8, $expected, 'Fieldmap should contain 2 x 4 array cells');

        $values['startdate'] = date('Y-m-d H:i:s');
        $values['datestamp'] = date('Y-m-d H:i:s');
        $values['submitdate'] = date('Y-m-d H:i:s');
        $values['lastpage'] = 1;
        $values['startlanguage'] = 'en';
        $response = \Response::create(self::$surveyId);
        $response->setAttributes($values, false);
        $this->assertTrue($response->encryptSave(), 'Could not save response');
        $responseId = $response->id;

        \Yii::app()->loadHelper('export');
        $quexml = quexml_export(self::$surveyId, 'en', $responseId);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($quexml);
        $xpath = new \DOMXPath($dom);

        foreach ($expected as $varName => $value) {
            $nodes = $xpath->query("//response[@varName='" . $varName . "']");
            $this->assertSame(1, $nodes->length, 'Missing response element for ' . $varName);
            $this->assertSame($value, $nodes->item(0)->getAttribute('defaultValue'), 'Wrong defaultValue for ' . $varName);
        }
    }
}
