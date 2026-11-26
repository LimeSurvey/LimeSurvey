<?php

namespace ls\tests\unit\api\command;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting\AnswerLabelOrder;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting\ResponseSort;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting\SingleChoiceSorter;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting\SingleValueSorter;
use ls\tests\TestBaseClass;
use SurveyDynamic;

/**
 * Sorting of the response list by single choice questions. The orders run
 * against a real responses table, so they are checked on every database
 * driver of the CI.
 */
class SingleChoiceSorterTest extends TestBaseClass
{
    /** @var array<string, int> Response IDs by name */
    private static array $responseIds = [];

    /**
     * Import and activate a survey with one question of each single choice type
     * (English and German) and add responses covering answers, Other, "No answer"
     * and questions that weren't shown.
     *
     * Answer texts (English / German): qList A1 Banana / Zitrone, A2 <b>apple</b> / Apfel,
     * A3 Cherry / Banane, with Other. qDropdown B1 Zebra, B2 Ant. qComment C1 Yes please,
     * C2 Absolutely. qImage I1 /images/zebra.png?v=2#top, I2 https://…/apple.jpg.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_524309_sort_single_choice.lss');
        self::$testHelper->activateSurvey(self::$surveyId);

        $qids = [];
        foreach (\Question::model()->findAllByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0]) as $question) {
            $qids[$question->title] = $question->qid;
        }
        $columns = [
            'qList' => 'Q' . $qids['qList'],
            'qListOther' => 'Q' . $qids['qList'] . '_Cother',
            'qDropdown' => 'Q' . $qids['qDropdown'],
            'qComment' => 'Q' . $qids['qComment'],
            'qCommentText' => 'Q' . $qids['qComment'] . '_Ccomment',
            'qImage' => 'Q' . $qids['qImage'],
            'qFive' => 'Q' . $qids['qFive'],
            'qYesNo' => 'Q' . $qids['qYesNo'],
            'qNumber' => 'Q' . $qids['qNumber'],
            'qDate' => 'Q' . $qids['qDate'],
        ];
        // Name => values by column key, inserted in this order (ascending IDs). Missing values are NULL.
        $responses = [
            'r1' => [
                'qList' => 'A1', 'qDropdown' => 'B1', 'qComment' => 'C1', 'qCommentText' => 'z comment',
                'qImage' => 'I1', 'qFive' => '3', 'qYesNo' => 'Y', 'qNumber' => 10, 'qDate' => '2026-01-02 00:00:00',
            ],
            'r2' => [
                'qList' => 'A2', 'qDropdown' => 'B2', 'qComment' => 'C2', 'qCommentText' => 'a comment',
                'qImage' => 'I2', 'qFive' => '1', 'qYesNo' => 'N', 'qNumber' => 9.5, 'qDate' => '2025-12-31 00:00:00',
            ],
            'r3' => ['qList' => 'A3', 'qDropdown' => '', 'qComment' => '', 'qImage' => '', 'qFive' => '5', 'qYesNo' => ''],
            // The Other text would sort first if it took part
            'r4' => [
                'qList' => '-oth-', 'qListOther' => 'Aardvark', 'qFive' => '', 'qNumber' => -1,
                'qDate' => '2026-01-01 10:00:00',
            ],
            'r5' => [
                'qList' => '', 'qDropdown' => 'B1', 'qComment' => 'C1', 'qImage' => 'I1', 'qYesNo' => 'Y',
                'qNumber' => 100, 'qDate' => '2026-01-01 09:00:00',
            ],
            'r6' => [],
            'r7' => ['qList' => 'A1'],
            // An answer option that was deleted after the response came in
            'r8' => ['qList' => 'A9'],
        ];
        $table = \Survey::model()->findByPk(self::$surveyId)->responsesTableName;
        $db = \Yii::app()->db;
        foreach ($responses as $values) {
            $row = [
                'startlanguage' => 'en',
                'lastpage' => 1,
                // Required even without date stamps
                'startdate' => '2026-01-01 09:00:00',
                'datestamp' => '2026-01-01 09:00:00',
            ];
            foreach ($values as $key => $value) {
                $row[$columns[$key]] = $value;
            }
            $db->createCommand()->insert($table, $row);
        }
        // The table was just created, so its IDs in ascending order are the responses in insert order.
        $ids = $db->createCommand()->select('id')->from($table)->order('id ASC')->queryColumn();
        self::$responseIds = array_combine(array_keys($responses), array_map('intval', $ids));
    }

    /**
     * Sort builder for the test survey with the single choice sorters.
     *
     * @param string $language Language of the response table
     * @return ResponseSort Sort builder
     */
    private function makeResponseSort(string $language): ResponseSort
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        return new ResponseSort(
            createFieldMap($survey, 'full', true, false, $language),
            [new SingleChoiceSorter(new AnswerLabelOrder($language)), new SingleValueSorter()]
        );
    }

    /**
     * Response names in the order the sort by a question returns them.
     *
     * @param string $title Question code
     * @param string $direction "asc" or "desc"
     * @param string $language Language of the response table
     * @return string[] Response names, in result order
     */
    private function getSortedNames(string $title, string $direction, string $language = 'en'): array
    {
        $qid = (string) $this->getAllSurveyQuestions()[$title]->qid;
        $order = $this->makeResponseSort($language)->getOrder([$qid => $direction]);
        $rows = SurveyDynamic::model(self::$surveyId)->findAll(['order' => $order]);
        $names = array_flip(self::$responseIds);
        return array_map(static fn($row) => $names[(int) $row->id], $rows);
    }

    /**
     * @testdox Single choice questions sort by answer text, then unknown codes, Other, No answer and not shown
     * @dataProvider sortProvider
     * @param string $title Question code
     * @param string $direction "asc" or "desc"
     * @param string $language Language of the response table
     * @param string[] $expected Response names in the expected order
     * @return void
     */
    public function testSortByQuestion(string $title, string $direction, string $language, array $expected): void
    {
        $this->assertSame($expected, $this->getSortedNames($title, $direction, $language));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string[]}>
     */
    public function sortProvider(): array
    {
        $answersThenMissing = ['r2', 'r5', 'r1', 'r3', 'r8', 'r7', 'r6', 'r4'];
        return [
            // apple (HTML removed, case ignored), Banana (newest first), Cherry, deleted code, Other, No answer, not shown
            'list by English text' => ['qList', 'asc', 'en', ['r2', 'r7', 'r1', 'r3', 'r8', 'r4', 'r5', 'r6']],
            'list descending flips the whole order' => [
                'qList', 'desc', 'en', ['r6', 'r5', 'r4', 'r8', 'r3', 'r7', 'r1', 'r2'],
            ],
            // Apfel, Banane, Zitrone
            'list by German text' => ['qList', 'asc', 'de', ['r2', 'r3', 'r7', 'r1', 'r8', 'r4', 'r5', 'r6']],
            'dropdown' => ['qDropdown', 'asc', 'en', $answersThenMissing],
            'list with comment ignores the comment' => ['qComment', 'asc', 'en', $answersThenMissing],
            // apple.jpg before zebra.png, although the URL of zebra.png comes first
            'image select by image name' => ['qImage', 'asc', 'en', $answersThenMissing],
            '5 point choice by number' => ['qFive', 'asc', 'en', ['r2', 'r1', 'r3', 'r4', 'r8', 'r7', 'r6', 'r5']],
            'yes/no' => ['qYesNo', 'asc', 'en', $answersThenMissing],
            'numerical input by number' => ['qNumber', 'asc', 'en', ['r4', 'r2', 'r1', 'r5', 'r8', 'r7', 'r6', 'r3']],
            'date oldest first' => ['qDate', 'asc', 'en', ['r2', 'r5', 'r4', 'r1', 'r8', 'r7', 'r6', 'r3']],
        ];
    }

    /**
     * @testdox All single choice questions are sortable, other question types aren't
     * @return void
     */
    public function testSortableKeys(): void
    {
        $keys = $this->makeResponseSort('en')->getSortableKeys();
        foreach ($this->getAllSurveyQuestions() as $title => $question) {
            if ($title === 'qText') {
                $this->assertNotContains((string) $question->qid, $keys, $title);
            } else {
                $this->assertContains((string) $question->qid, $keys, $title);
            }
        }
    }

    /**
     * @testdox Answers are ranked by text, and answers with the same text share a rank
     * @return void
     */
    public function testRanks(): void
    {
        $qid = (int) $this->getAllSurveyQuestions()['qList']->qid;
        $this->assertSame(['A2' => 1, 'A1' => 2, 'A3' => 3], (new AnswerLabelOrder('en'))->getRanks($qid));

        $this->withCherryRenamedToBanana(function () use ($qid) {
            $this->assertSame(['A2' => 1, 'A1' => 2, 'A3' => 2], (new AnswerLabelOrder('en'))->getRanks($qid));
        });
    }

    /**
     * @testdox Without php-intl, answers differing in the case of non-ASCII letters share a rank
     * @return void
     */
    public function testRanksWithoutIntl(): void
    {
        $qid = (int) $this->getAllSurveyQuestions()['qList']->qid;
        // The PHP fallback sorts these as Äpfel, Öl, äpfel: Only ASCII letters are compared case-insensitively
        $texts = ['A1' => 'Äpfel', 'A2' => 'Öl', 'A3' => 'äpfel'];
        $this->withEnglishAnswerTexts($texts, function () use ($qid) {
            // Make the sort helper use the PHP fallback, as without php-intl
            \LimeSurvey\Helpers\SortHelper::getInstance('en');
            $collator = new \ReflectionProperty(\LimeSurvey\Helpers\SortHelper::class, 'collator');
            $collator->setValue(null, null);
            try {
                $this->assertSame(['A1' => 1, 'A3' => 1, 'A2' => 2], (new AnswerLabelOrder('en'))->getRanks($qid));
            } finally {
                // The next getInstance() call creates the helper again, with the collator
                $instance = new \ReflectionProperty(\LimeSurvey\Helpers\SortHelper::class, 'instance');
                $instance->setValue(null, null);
            }
        });
    }

    /**
     * @testdox Responses of answers with the same text are ordered newest first, not by answer code
     * @return void
     */
    public function testSameTextSortsNewestFirst(): void
    {
        $this->withCherryRenamedToBanana(function () {
            // apple, then Banana A1 (r1, r7) and BANANA A3 (r3) mixed by age, then deleted code, Other, No answer, not shown
            $this->assertSame(['r2', 'r7', 'r3', 'r1', 'r8', 'r4', 'r5', 'r6'], $this->getSortedNames('qList', 'asc'));
            $this->assertSame(['r6', 'r5', 'r4', 'r8', 'r7', 'r3', 'r1', 'r2'], $this->getSortedNames('qList', 'desc'));
        });
    }

    /**
     * Run checks while the English text of qList's answer A3 is "BANANA", the same
     * text as A1 in other case, and restore "Cherry" afterwards.
     *
     * @param callable $checks Assertions to run with the renamed answer
     * @return void
     */
    private function withCherryRenamedToBanana(callable $checks): void
    {
        $this->withEnglishAnswerTexts(['A3' => 'BANANA'], $checks);
    }

    /**
     * Run checks while answers of qList have other English texts, and restore
     * the original texts afterwards.
     *
     * @param array<string, string> $texts Answer code => English text to use during the checks
     * @param callable $checks Assertions to run with the changed texts
     * @return void
     */
    private function withEnglishAnswerTexts(array $texts, callable $checks): void
    {
        $qid = (int) $this->getAllSurveyQuestions()['qList']->qid;
        $originals = [];
        try {
            foreach ($texts as $code => $text) {
                $answer = \Answer::model()->findByAttributes(['qid' => $qid, 'code' => $code]);
                $l10n = \AnswerL10n::model()->findByAttributes(['aid' => $answer->aid, 'language' => 'en']);
                $originals[] = [$l10n, $l10n->answer];
                $l10n->answer = $text;
                $l10n->save();
            }
            $checks();
        } finally {
            foreach ($originals as [$l10n, $original]) {
                $l10n->answer = $original;
                $l10n->save();
            }
        }
    }
}
