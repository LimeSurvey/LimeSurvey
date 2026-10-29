<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use SurveyDao;
use SurveyObj;
use Translator;

/**
 * Tests for the export helper SurveyObj, in particular the distinction
 * between NULL (question/subquestion not shown or filtered) and an empty
 * string (shown but not answered) in exported responses.
 *
 * @see https://bugs.limesurvey.org/view.php?id=19383
 */
class SurveyObjTest extends TestBaseClass
{
    /** @var SurveyObj */
    private static $surveyObj;

    /** @var Translator */
    private $translator;

    /**
     * Import the test survey and load it the same way the response export does.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.admin.export.*');
        self::importSurvey(self::$dataFolder . '/surveys/limesurvey_survey_696688_surveyObjExport.lss');
        self::$surveyObj = (new SurveyDao())->loadSurveyById(self::$surveyId, 'en');
    }

    /**
     * Create a translator stub that returns the untranslated key.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->translator = $this->createMock(Translator::class);
        $this->translator->method('translate')->willReturnArgument(0);
    }

    /**
     * Multiple choice: checked is Yes, shown but unchecked is No and
     * filtered (NULL) is N/A.
     *
     * @return void
     */
    public function testMultipleChoiceFullAnswer()
    {
        $fieldName = $this->getFieldName('MultipleChoice', 'SQ001');
        $this->assertSame('Yes', $this->getFullAnswer($fieldName, 'Y'));
        $this->assertSame('No', $this->getFullAnswer($fieldName, 'N'));
        $this->assertSame('No', $this->getFullAnswer($fieldName, ''));
        $this->assertSame('N/A', $this->getFullAnswer($fieldName, null));
    }

    /**
     * Multiple choice with comments behaves like multiple choice for the
     * subquestion columns.
     *
     * @return void
     */
    public function testMultipleChoiceWithCommentsFullAnswer()
    {
        $fieldName = $this->getFieldName('MultipleComment', 'SQ001');
        $this->assertSame('Yes', $this->getFullAnswer($fieldName, 'Y'));
        $this->assertSame('No', $this->getFullAnswer($fieldName, ''));
        $this->assertSame('N/A', $this->getFullAnswer($fieldName, null));
    }

    /**
     * The "other" and "comment" text columns are passed through unchanged,
     * keeping NULL and empty string distinct.
     *
     * @return void
     */
    public function testTextColumnsFullAnswer()
    {
        foreach (
            [
                $this->getFieldName('MultipleChoice', 'other'),
                $this->getFieldName('MultipleComment', 'SQ001comment'),
                $this->getFieldName('ListRadio', 'other'),
            ] as $fieldName
        ) {
            $this->assertSame('Some text', $this->getFullAnswer($fieldName, 'Some text'), $fieldName);
            $this->assertSame('', $this->getFullAnswer($fieldName, ''), $fieldName);
            $this->assertNull($this->getFullAnswer($fieldName, null), $fieldName);
        }
    }

    /**
     * Yes/No question: anything that is neither Y nor N is N/A.
     *
     * @return void
     */
    public function testYesNoFullAnswer()
    {
        $fieldName = $this->getFieldName('YesNo');
        $this->assertSame('Yes', $this->getFullAnswer($fieldName, 'Y'));
        $this->assertSame('No', $this->getFullAnswer($fieldName, 'N'));
        $this->assertSame('N/A', $this->getFullAnswer($fieldName, ''));
        $this->assertSame('N/A', $this->getFullAnswer($fieldName, null));
    }

    /**
     * Array (Yes/No/Uncertain): a filtered subquestion stays NULL.
     *
     * @return void
     */
    public function testArrayYesUncertainNoFullAnswer()
    {
        $fieldName = $this->getFieldName('ArrayYUN', 'SQ001');
        $this->assertSame('Uncertain', $this->getFullAnswer($fieldName, 'U'));
        $this->assertNull($this->getFullAnswer($fieldName, null));
    }

    /**
     * List (radio): known codes resolve to the answer text, NULL stays NULL.
     *
     * @return void
     */
    public function testListFullAnswer()
    {
        $fieldName = $this->getFieldName('ListRadio');
        $this->assertSame('Answer one', $this->getFullAnswer($fieldName, 'A1'));
        $this->assertSame('Other', $this->getFullAnswer($fieldName, '-oth-'));
        $this->assertSame('', $this->getFullAnswer($fieldName, ''));
        $this->assertNull($this->getFullAnswer($fieldName, null));
    }

    /**
     * Question types without special handling pass the value through,
     * keeping NULL and empty string distinct.
     *
     * @return void
     */
    public function testDefaultTypeFullAnswer()
    {
        $fieldName = $this->getFieldName('ShortText');
        $this->assertSame('Free text', $this->getFullAnswer($fieldName, 'Free text'));
        $this->assertSame('', $this->getFullAnswer($fieldName, ''));
        $this->assertNull($this->getFullAnswer($fieldName, null));
    }

    /**
     * Numerical question: decimals are normalized, NULL and empty string
     * are returned unchanged.
     *
     * @return void
     */
    public function testNumericalFullAnswer()
    {
        $fieldName = $this->getFieldName('Numerical');
        $this->assertSame('0.5', $this->getFullAnswer($fieldName, '.5000'));
        $this->assertSame('', $this->getFullAnswer($fieldName, ''));
        $this->assertNull($this->getFullAnswer($fieldName, null));
    }

    /**
     * Short answer: NULL stays NULL, empty string stays empty and numerical
     * values lose trailing decimal zeros.
     *
     * @return void
     */
    public function testShortAnswer()
    {
        $multipleChoice = $this->getFieldName('MultipleChoice', 'SQ001');
        $numerical = $this->getFieldName('Numerical');
        $this->assertNull(self::$surveyObj->getShortAnswer($multipleChoice, null));
        $this->assertSame('', self::$surveyObj->getShortAnswer($multipleChoice, ''));
        $this->assertSame('Y', self::$surveyObj->getShortAnswer($multipleChoice, 'Y'));
        $this->assertSame('1.5', self::$surveyObj->getShortAnswer($numerical, '1.5000'));
        $this->assertSame('2', self::$surveyObj->getShortAnswer($numerical, '2.000'));
    }

    /**
     * Find the response column name in the fieldmap by question code and
     * subquestion/suffix code, since question IDs change on every import.
     *
     * @param string $title Question code
     * @param string $aid Subquestion code or suffix as stored in the fieldmap, empty for the question itself
     * @return string
     */
    private function getFieldName($title, $aid = '')
    {
        foreach (self::$surveyObj->fieldMap as $fieldName => $field) {
            if ($field['title'] === $title && (string)$field['aid'] === $aid) {
                return $fieldName;
            }
        }
        $this->fail("No field found for question $title and aid '$aid'");
    }

    /**
     * Shortcut for SurveyObj::getFullAnswer() with the translator stub.
     *
     * @param string $fieldName
     * @param string|null $answerCode
     * @return string|null
     */
    private function getFullAnswer($fieldName, $answerCode)
    {
        return self::$surveyObj->getFullAnswer($fieldName, $answerCode, $this->translator, 'en');
    }
}
