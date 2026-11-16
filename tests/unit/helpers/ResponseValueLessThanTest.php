<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Text answers containing '<' must not be truncated in the response list, the single response view and the SPSS/R export.
 *
 * @see https://bugs.limesurvey.org/view.php?id=17124
 */
class ResponseValueLessThanTest extends TestBaseClass
{
    /** @var string An answer where '<' is followed by a letter, like the start of a HTML tag */
    private const ANSWER = "avant avait l<huile et a change le systeme";

    /** @var array The fieldmap entry of the short text question */
    private static $textField;

    /** @var array The fieldmap entry of the list question */
    private static $listField;

    /** @var array The fieldmap entry of the 'other' text of the list question */
    private static $otherField;

    /**
     * Imports and activates a survey with a short text question and a list question with 'other'.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->loadHelper('export');
        \Yii::import('application.helpers.viewHelper');
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_PrefillBeforeSurveyPolicy.lss');
        $activator = new \SurveyActivator(self::$testSurvey);
        $activator->activate();

        foreach (createFieldMap(self::$testSurvey, 'short', true, false, 'en') as $field) {
            if ($field['type'] === \Question::QT_S_SHORT_FREE_TEXT) {
                self::$textField = $field;
            } elseif ($field['type'] === \Question::QT_L_LIST) {
                if ($field['aid'] === 'other') {
                    self::$otherField = $field;
                } else {
                    self::$listField = $field;
                }
            }
        }
    }

    /**
     * Plain text is returned unchanged, a changed value is flattened.
     */
    public function testResponseValueToText()
    {
        $this->assertSame(self::ANSWER, \viewHelper::responseValueToText(self::ANSWER, self::ANSWER));
        $this->assertSame('<b>bold</b>', \viewHelper::responseValueToText('<b>bold</b>', '<b>bold</b>'));
        $this->assertSame('Ben & Jerry [A1]', \viewHelper::responseValueToText('<b>Ben</b> &amp; Jerry [A1]', 'A1'));
        $this->assertSame('', \viewHelper::responseValueToText('', null));
    }

    /**
     * The tags are only stripped by ellipsize() when requested.
     */
    public function testEllipsizeWithoutStripTags()
    {
        $this->assertSame('avant avait l<huile…', ellipsize(self::ANSWER, 19, 1, '…', false));
        $this->assertSame('avant avait l', ellipsize(self::ANSWER, 19));
    }

    /**
     * The SPSS/R export contains the whole answer, new lines are replaced by spaces.
     */
    public function testSpssExport()
    {
        $this->insertResponse([
            self::$textField['fieldname'] => self::ANSWER,
            self::$otherField['fieldname'] => "a<b\nc",
        ]);

        ob_start();
        SPSSExportData(self::$surveyId, 255);
        $output = ob_get_clean();

        $this->assertStringContainsString("'" . self::ANSWER . "'", $output);
        $this->assertStringContainsString("'a<b c'", $output);
    }

    /**
     * The response list shows the whole answer, HTML escaped.
     */
    public function testResponseList()
    {
        $response = $this->insertResponse([
            self::$textField['fieldname'] => self::ANSWER,
            self::$otherField['fieldname'] => '<b>bold</b>',
        ]);

        $this->assertSame(
            'avant avait l&lt;huile et a change le systeme',
            $this->getExtendedData($response, self::$textField)
        );
        $this->assertSame(
            '&lt;b&gt;bold&lt;/b&gt;',
            $this->getExtendedData($response, self::$otherField)
        );
    }

    /**
     * A long answer is ellipsized without losing the text after '<', the tooltip contains the whole answer.
     */
    public function testResponseListLongAnswer()
    {
        $answer = str_repeat('x', 20) . ' l<huile ' . str_repeat('y', 60);
        $response = $this->insertResponse([self::$textField['fieldname'] => $answer]);

        $value = $this->getExtendedData($response, self::$textField);

        $this->assertStringContainsString('title="' . htmlspecialchars($answer) . '"', $value);
        $this->assertStringContainsString('>' . str_repeat('x', 20) . ' l&lt;huile y', $value);
    }

    /**
     * Answer option texts are still flattened in the response list.
     */
    public function testResponseListAnswerOption()
    {
        $answer = \Answer::model()->findByAttributes(['qid' => self::$listField['qid'], 'code' => 'A1']);
        $answerL10n = \AnswerL10n::model()->findByAttributes(['aid' => $answer->aid, 'language' => 'en']);
        $answerL10n->answer = '<b>Ben</b> &amp; Jerry';
        $answerL10n->save();
        $response = $this->insertResponse([self::$listField['fieldname'] => 'A1']);

        $this->assertSame('Ben &amp; Jerry [A1]', $this->getExtendedData($response, self::$listField));
    }

    /**
     * Inserts a response.
     *
     * @param array $data Column values
     * @return \SurveyDynamic
     */
    private function insertResponse(array $data): \SurveyDynamic
    {
        \SurveyDynamic::model(self::$surveyId)->deleteAll();
        $responseId = \SurveyDynamic::model(self::$surveyId)->insertRecords(array_merge(['startlanguage' => 'en'], $data));
        return \SurveyDynamic::model(self::$surveyId)->findByPk($responseId);
    }

    /**
     * Returns the value of a column as shown in the response list.
     *
     * @param \SurveyDynamic $response
     * @param array $field The fieldmap entry of the column
     * @return string
     */
    private function getExtendedData(\SurveyDynamic $response, array $field): string
    {
        return $response->getExtendedData($field['fieldname'], 'en', base64_encode(json_encode($field)));
    }
}
