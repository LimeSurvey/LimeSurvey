<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Question;
use Yii;

/**
 * Tests that importing a question group never creates question codes that already exist in the survey.
 */
class ImportGroupDuplicateCodeTest extends TestBaseClass
{
    /** @var string[] Question codes of the group file that already exist in the survey */
    private static $duplicatedCodes = ['FixedQ03', 'R2Q03', 'R2Q04'];

    /**
     * Imports the survey that already uses the question codes of the group file.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.admin.import_helper', true);

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_17200_duplicate_codes.lss');
    }

    /**
     * Provides the "Convert resource links?" import option.
     *
     * @return array
     */
    public function translateLinksProvider()
    {
        return [
            'convert resource links' => [true],
            'keep resource links'    => [false],
        ];
    }

    /**
     * Each imported question code that is already used in the survey must be renamed.
     *
     * @dataProvider translateLinksProvider
     * @param bool $translateLinks
     */
    public function testDuplicateQuestionCodesAreRenamed($translateLinks)
    {
        $results = XMLImportGroup(
            self::$dataFolder . '/file_upload/limesurvey_group_17199_duplicate_codes.lsg',
            self::$surveyId,
            $translateLinks
        );

        $this->assertArrayNotHasKey('fatalerror', $results);
        $this->assertSame(3, $results['questions']);

        $titles = array_map(
            function ($question) {
                return $question->title;
            },
            Question::model()->findAll('sid = :sid AND parent_qid = 0', [':sid' => self::$surveyId])
        );
        $this->assertSame(array_values(array_unique($titles)), $titles);
        foreach (self::$duplicatedCodes as $code) {
            $this->assertCount(
                1,
                preg_grep("/^Question code {$code} was updated to /", $results['importwarnings'])
            );
        }
    }
}
