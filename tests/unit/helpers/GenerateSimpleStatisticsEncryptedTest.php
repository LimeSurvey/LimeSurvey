<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

use Yii;

/**
 * Tests for statistics_helper::generate_simple_statistics() with encrypted questions.
 *
 * @see https://bugs.limesurvey.org/view.php?id=19538
 */
class GenerateSimpleStatisticsEncryptedTest extends TestBaseClass
{
    /** @var \Question[] Questions indexed by title */
    private static $questions = [];

    public static function setUpBeforeClass(): void
    {
        Yii::app()->loadHelper('admin.statistics');
        Yii::app()->loadHelper('common');

        parent::setUpBeforeClass();

        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/survey-dual-scale-question-api-test.lss');

        // G01Q04 is an array, G01Q06 a list (radio) and G01Q07 a multiple choice question
        foreach (['G01Q04', 'G01Q06', 'G01Q07'] as $title) {
            $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => $title, 'parent_qid' => 0]);
            $question->encrypted = 'Y';
            $question->save();
            self::$questions[$title] = $question;
        }

        $activator = new \SurveyActivator(self::$testSurvey);
        $activator->activate();

        $arrayColumn = self::getSubquestionColumn('G01Q04', 'SQ001');
        $listColumn = 'Q' . self::$questions['G01Q06']->qid;
        $multipleChoiceColumn1 = self::getSubquestionColumn('G01Q07', 'SQ001');
        $multipleChoiceColumn2 = self::getSubquestionColumn('G01Q07', 'SQ002');

        $responses = [
            [$arrayColumn => 'AO01', $listColumn => 'AO03', $multipleChoiceColumn1 => 'Y', $multipleChoiceColumn2 => 'Y'],
            [$arrayColumn => 'AO02', $listColumn => 'AO03', $multipleChoiceColumn1 => 'Y', $multipleChoiceColumn2 => ''],
            [$arrayColumn => 'AO02', $listColumn => 'AO01', $multipleChoiceColumn1 => 'Y', $multipleChoiceColumn2 => ''],
        ];
        foreach ($responses as $response) {
            // insertRecords() encrypts the values of encrypted questions
            \SurveyDynamic::model(self::$surveyId)->insertRecords(array_merge([
                'startlanguage' => 'en',
                'submitdate' => date('Y-m-d H:i:s'),
            ], $response));
        }
    }

    /**
     * Answers to encrypted questions must be counted, not only the empty ones.
     *
     * @return void
     */
    public function testGenerateSimpleStatisticsCountsEncryptedAnswers(): void
    {
        $this->assertStringNotContainsString(
            'AO01',
            (string) Yii::app()->db->createCommand()
                ->select(self::getSubquestionColumn('G01Q04', 'SQ001'))
                ->from('{{responses_' . self::$surveyId . '}}')
                ->queryScalar(),
            'The response data was not stored encrypted.'
        );

        $summary = getStatisticsFieldNames(self::$surveyId, array_values(self::$questions), null);

        $helper = new \statistics_helper();
        $statistics = $helper->generate_simple_statistics(self::$surveyId, $summary, $summary, 1, 'html', 'DD');

        $this->assertMatchesRegularExpression(
            '/grawdata : \[1,2,0,0\]/',
            $this->getGraphScript($statistics, self::getSubquestionColumn('G01Q04', 'SQ001')),
            'The array statistics values are not correct.'
        );
        $this->assertMatchesRegularExpression(
            '/grawdata : \[1,2,0,0\]/',
            $this->getGraphScript($statistics, 'Q' . self::$questions['G01Q06']->qid),
            'The list statistics values are not correct.'
        );
        $this->assertMatchesRegularExpression(
            '/grawdata : \[3,1,0,0\]/',
            $this->getGraphScript($statistics, 'Q' . self::$questions['G01Q07']->qid),
            'The multiple choice statistics values are not correct.'
        );
    }

    /**
     * Returns the response column of a subquestion.
     *
     * @param string $questionTitle Title of the parent question
     * @param string $subquestionTitle Title of the subquestion
     * @return string
     */
    private static function getSubquestionColumn(string $questionTitle, string $subquestionTitle): string
    {
        $parent = self::$questions[$questionTitle];
        $subquestion = \Question::model()->findByAttributes(['parent_qid' => $parent->qid, 'title' => $subquestionTitle]);
        return 'Q' . $parent->qid . '_S' . $subquestion->qid;
    }

    /**
     * Returns the graph script of a statistics field from the generated HTML.
     *
     * @param string $html Generated statistics HTML
     * @param string $field Statistics field name
     * @return string
     */
    private function getGraphScript(string $html, string $field): string
    {
        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHtml($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach ($doc->getElementsByTagName('script') as $script) {
            if (str_contains($script->nodeValue, "['quid'+'" . $field . "']")) {
                return trim($script->nodeValue);
            }
        }
        $this->fail('No graph found for ' . $field);
    }
}
