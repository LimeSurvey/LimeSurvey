<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use QuestionGroupL10n;
use QuestionL10n;
use Yii;

/**
 * Tests that group and question files without a group description or question help text
 * (empty values are left out on export) can be imported (see #19314).
 */
class ImportWithoutDescriptionTest extends TestBaseClass
{
    /**
     * Imports the survey the group and question files are imported into.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.admin.import_helper', true);

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_251297_import_group.lss');
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
     * A group file without group description and question help text is imported completely.
     *
     * @dataProvider translateLinksProvider
     * @param bool $translateLinks
     */
    public function testImportGroupWithoutDescriptionAndHelp($translateLinks)
    {
        $results = XMLImportGroup(
            self::$dataFolder . '/file_upload/limesurvey_group_19314_no_description_help.lsg',
            self::$surveyId,
            $translateLinks
        );

        $this->assertArrayNotHasKey('fatalerror', $results);
        $this->assertSame(2, $results['questions']);

        $groupL10n = QuestionGroupL10n::model()->findByAttributes(['gid' => $results['newgid'], 'language' => 'en']);
        $this->assertNotNull($groupL10n);
        $this->assertSame('Imported group', $groupL10n->group_name);
        $this->assertEmpty($groupL10n->description);
    }

    /**
     * A question file without question help text is imported completely.
     *
     * @dataProvider translateLinksProvider
     * @param bool $translateLinks
     */
    public function testImportQuestionWithoutHelp($translateLinks)
    {
        $results = XMLImportQuestion(
            self::$surveysFolder . '/limesurvey_question_19314_no_help.lsq',
            self::$surveyId,
            self::$testSurvey->groups[0]->gid,
            ['autorename' => true, 'translinkfields' => $translateLinks]
        );

        $this->assertArrayNotHasKey('fatalerror', $results);

        $questionL10n = QuestionL10n::model()->findByAttributes(['qid' => $results['newqid'], 'language' => 'en']);
        $this->assertNotNull($questionL10n);
        $this->assertEmpty($questionL10n->help);
    }
}
