<?php

namespace ls\tests\unit\helpers;

use LimeSurvey\Models\Services\GenderQuestionConverter;
use ls\tests\TestBaseClass;

/**
 * The Gender question type was removed. Gender questions are converted into
 * List (radio) questions with the answer options F (Female) and M (Male),
 * both on import and by the database update.
 */
class GenderQuestionConversionTest extends TestBaseClass
{
    /** @var string Survey containing the Gender question Q00 with the languages de and en */
    const SURVEY_FILE = 'limesurvey_survey_373616_copySurvey.lss';

    /**
     * Display type of the Gender question and the question theme it is converted to.
     *
     * @return array
     */
    public function displayTypeProvider(): array
    {
        return [
            'button group' => ['0', 'bootstrap_buttons'],
            'radio list' => ['1', 'listradio'],
        ];
    }

    /**
     * Importing a survey with a Gender question converts it into a List (radio) question.
     *
     * @dataProvider displayTypeProvider
     * @param string $displayType Value of the display_type attribute in the imported file
     * @param string $expectedTheme Expected question theme of the converted question
     * @return void
     */
    public function testSurveyImportConvertsGenderQuestion(string $displayType, string $expectedTheme): void
    {
        $xml = file_get_contents(self::$surveysFolder . '/' . self::SURVEY_FILE);
        $xml = str_replace(
            "<attribute><![CDATA[display_type]]></attribute>\n    <value><![CDATA[0]]></value>",
            "<attribute><![CDATA[display_type]]></attribute>\n    <value><![CDATA[{$displayType}]]></value>",
            $xml,
            $count
        );
        $this->assertSame(1, $count, 'The display_type attribute of the Gender question was not found in the fixture.');

        \Yii::app()->session['loginID'] = 1;
        $survey = null;
        try {
            $result = XMLImportSurvey('', $xml);
            $survey = \Survey::model()->findByPk($result['newsid']);
            $this->assertNotNull($survey);

            $question = \Question::model()->findByAttributes(['sid' => $survey->sid, 'title' => 'Q00']);
            $this->assertConvertedQuestion($question->qid, $expectedTheme, ['de', 'en']);

            $warnings = implode("\n", $result['importwarnings']);
            $this->assertStringContainsString('Q00', $warnings);
        } finally {
            if ($survey) {
                \Yii::app()->session['loginID'] = 1;
                $survey->delete();
            }
        }
    }

    /**
     * The converter used by the database update converts remaining Gender questions
     * and does not add the answer options twice.
     *
     * @return void
     */
    public function testConverterConvertsLegacyGenderQuestion(): void
    {
        \Yii::app()->session['loginID'] = 1;
        $survey = null;
        try {
            $result = XMLImportSurvey(self::$surveysFolder . '/' . self::SURVEY_FILE);
            $survey = \Survey::model()->findByPk($result['newsid']);
            $this->assertNotNull($survey);
            $question = \Question::model()->findByAttributes(['sid' => $survey->sid, 'title' => 'Q00']);
            $qid = (int) $question->qid;

            // Put the question back into the state of a Gender question before the update
            $db = \Yii::app()->db;
            $aids = $db->createCommand()->select('aid')->from('{{answers}}')->where('qid = :qid', [':qid' => $qid])->queryColumn();
            $db->createCommand()->delete('{{answer_l10ns}}', ['in', 'aid', $aids]);
            $db->createCommand()->delete('{{answers}}', 'qid = :qid', [':qid' => $qid]);
            $db->createCommand()->update('{{questions}}', ['type' => 'G', 'question_theme_name' => 'gender'], 'qid = :qid', [':qid' => $qid]);
            $db->createCommand()->insert('{{question_attributes}}', ['qid' => $qid, 'attribute' => 'display_type', 'value' => '1']);

            $converter = new GenderQuestionConverter($db);
            $this->assertSame([$qid => (int) $survey->sid], $converter->convert((int) $survey->sid));
            $this->assertConvertedQuestion($qid, 'listradio', ['de', 'en']);

            // Running it again on an already converted question must not add the answer options twice
            $converter->convertQuestion($qid, (int) $survey->sid);
            $this->assertConvertedQuestion($qid, 'listradio', ['de', 'en']);
            $this->assertSame([], $converter->convert((int) $survey->sid));
        } finally {
            if ($survey) {
                \Yii::app()->session['loginID'] = 1;
                $survey->delete();
            }
        }
    }

    /**
     * Assert that a question was converted into a List (radio) question with the
     * answer options F (Female) and M (Male) in all its languages.
     *
     * @param int $qid The question ID
     * @param string $expectedTheme Expected question theme
     * @param string[] $languages Languages the answer options must exist in
     * @return void
     */
    private function assertConvertedQuestion(int $qid, string $expectedTheme, array $languages): void
    {
        $db = \Yii::app()->db;
        $question = $db->createCommand()->select('*')->from('{{questions}}')->where('qid = :qid', [':qid' => $qid])->queryRow();
        $this->assertSame('L', $question['type']);
        $this->assertSame($expectedTheme, $question['question_theme_name']);

        $answers = $db->createCommand()
            ->select('aid, code, sortorder')
            ->from('{{answers}}')
            ->where('qid = :qid', [':qid' => $qid])
            ->order('sortorder')
            ->queryAll();
        $this->assertSame(['F', 'M'], array_column($answers, 'code'));
        $this->assertEquals([0, 1], array_column($answers, 'sortorder'));

        foreach ($languages as $language) {
            $labels = [];
            foreach ($answers as $answer) {
                $labels[] = $db->createCommand()
                    ->select('answer')
                    ->from('{{answer_l10ns}}')
                    ->where('aid = :aid AND language = :language', [':aid' => $answer['aid'], ':language' => $language])
                    ->queryScalar();
            }
            $this->assertSame(
                [gT('Female', 'unescaped', $language), gT('Male', 'unescaped', $language)],
                $labels,
                "Answer option labels for language {$language}"
            );
        }

        $displayType = $db->createCommand()
            ->select('COUNT(*)')
            ->from('{{question_attributes}}')
            ->where('qid = :qid AND attribute = :attribute', [':qid' => $qid, ':attribute' => 'display_type'])
            ->queryScalar();
        $this->assertEquals(0, $displayType, 'The display_type attribute must be removed.');
    }
}
