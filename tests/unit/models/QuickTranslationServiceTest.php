<?php

namespace ls\tests;

use LimeSurvey\Models\Services\QuickTranslation;

class QuickTranslationServiceTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss';
        self::importSurvey($surveyFile);
    }

    public function testGetTranslationsNoTranslation()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->getTranslations('title', 'aa');
        $this->assertEmpty($ts);
    }

    public function testGetTranslationsEnglishTitle()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->getTranslations('title', 'en');
        $this->assertNotEmpty($ts);
        $this->assertEquals($ts[0]->surveyls_title, 'translation');
    }

    public function testGetTranslationsQuestion()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->getTranslations('question', 'en');
        $this->assertNotEmpty($ts[0]->questionl10ns);
        $this->assertEquals($ts[0]->questionl10ns['en']->help, 'This is a question help text.');
    }

    public function testUpdateTranslationUnknownField()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->updateTranslations('wrongField', 'en', 'newstring');
        $this->assertNull($ts);
    }

    public function testUpdateTranslationEnglishTitle()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->updateTranslations('title', 'en', 'new-title');
        $this->assertNotNull($ts);
        $this->assertEquals(1, $ts);
    }

    /* --> something wrong with imported test-survey and the groupid, that should be 2
    public function testUpdateTranslationArabicGroup()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->updateTranslations('group', 'de', 'newGroupTitle', 2);
        $this->assertNotNull($ts);
        $this->assertEquals(1, $ts);
    }*/

    /**
     * Texts are untranslated if the translation is empty or the same as the source text.
     */
    public function testIsUntranslated()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $this->assertTrue($qt->isUntranslated('Question', ''));
        $this->assertTrue($qt->isUntranslated('Question', null));
        $this->assertTrue($qt->isUntranslated('Question', ' Question '));
        $this->assertTrue($qt->isUntranslated('Fish & chips', 'Fish &amp; chips'));
        $this->assertFalse($qt->isUntranslated('Question', 'Frage'));
    }

    /**
     * The fields are arranged like the survey: Groups by group order with their questions, subquestions and
     * answer options per scale; Fields without source text are left out.
     */
    public function testGetSurveyStructure()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $field = function ($type, $j, $textfrom, $textto, array $rowfrom) {
            return ['type' => $type, 'j' => $j, 'textfrom' => $textfrom, 'textto' => $textto, 'rowfrom' => $rowfrom];
        };
        $fieldsByType = [
            'title' => [$field('title', 0, 'Title', 'Titel', [])],
            'description' => [$field('description', 0, '', '', [])],
            'welcome' => [$field('welcome', 0, 'Welcome', 'Welcome', [])],
            'end' => [$field('end', 0, '', '', [])],
            // Queried by group ID, but the second group comes first in the survey
            'group' => [
                $field('group', 0, 'Second', 'Zweite', ['gid' => 1, 'group_order' => 2]),
                $field('group', 1, 'First', '', ['gid' => 2, 'group_order' => 1]),
            ],
            'group_desc' => [
                $field('group_desc', 0, '', '', ['gid' => 1, 'group_order' => 2]),
                $field('group_desc', 1, 'Description', 'Beschreibung', ['gid' => 2, 'group_order' => 1]),
            ],
            'question' => [
                $field('question', 0, 'Matrix', 'Matrix', ['qid' => 10, 'gid' => 2, 'title' => 'Q1', 'type' => ':']),
                $field('question', 1, 'Dual', 'Dual', ['qid' => 11, 'gid' => 1, 'title' => 'Q2', 'type' => '1']),
            ],
            'question_help' => [
                $field('question_help', 0, 'Help', 'Hilfe', ['qid' => 10, 'gid' => 2, 'title' => 'Q1', 'type' => ':']),
                $field('question_help', 1, '', '', ['qid' => 11, 'gid' => 1, 'title' => 'Q2', 'type' => '1']),
            ],
            'subquestion' => [
                $field('subquestion', 0, 'Row', 'Zeile', ['qid' => 20, 'parent_qid' => 10, 'scale_id' => 0, 'title' => 'SQ1']),
                $field('subquestion', 1, 'Column', '', ['qid' => 21, 'parent_qid' => 10, 'scale_id' => 1, 'title' => 'SQ2']),
            ],
            'answer' => [
                $field('answer', 0, 'Yes', 'Ja', ['qid' => 11, 'scale_id' => 0, 'code' => 'A1']),
                $field('answer', 1, 'Good', 'Good', ['qid' => 11, 'scale_id' => 1, 'code' => 'B1']),
            ],
            'emailinvite' => [$field('emailinvite', 0, 'Invitation', 'Einladung', [])],
            'emailinvitebody' => [$field('emailinvitebody', 0, 'Dear', '', [])],
            'emailreminder' => [$field('emailreminder', 0, '', '', [])],
            'emailreminderbody' => [$field('emailreminderbody', 0, '', '', [])],
        ];

        $structure = $qt->getSurveyStructure($fieldsByType);

        $this->assertSame(['title', 'welcome'], array_column($structure['surveyTexts'], 'type'));
        $this->assertSame(1, $structure['surveyTextsUntranslatedCount']);

        $this->assertSame([2, 1], array_column($structure['groups'], 'gid'));
        $first = $structure['groups'][0];
        $this->assertSame('First', $first['name']);
        $this->assertSame(['group', 'group_desc'], array_column($first['fields'], 'type'));
        $this->assertSame(3, $first['untranslatedCount']);

        $matrix = $first['questions'][0];
        $this->assertSame(10, $matrix['qid']);
        $this->assertSame(['question', 'question_help'], array_column($matrix['fields'], 'type'));
        $this->assertSame([gT("Subquestions (Y-scale)"), gT("Subquestions (X-scale)")], array_column($matrix['subquestionScales'], 'title'));
        $this->assertSame([], $matrix['answerScales']);
        $this->assertSame(2, $matrix['untranslatedCount']);

        $second = $structure['groups'][1];
        $this->assertSame(['group'], array_column($second['fields'], 'type'));
        $dual = $second['questions'][0];
        $this->assertSame(['question'], array_column($dual['fields'], 'type'));
        $this->assertSame([sprintf(gT("Answer options (scale %s)"), 1), sprintf(gT("Answer options (scale %s)"), 2)], array_column($dual['answerScales'], 'title'));
        $this->assertSame(2, $dual['untranslatedCount']);
        $this->assertSame(2, $second['untranslatedCount']);

        $this->assertCount(1, $structure['emails']);
        $this->assertSame('emailinvite', $structure['emails'][0]['type']);
        $this->assertSame(['emailinvite', 'emailinvitebody'], array_column($structure['emails'][0]['fields'], 'type'));
        $this->assertSame(1, $structure['emailsUntranslatedCount']);
    }
}
