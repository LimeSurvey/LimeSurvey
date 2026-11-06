<?php

namespace ls\tests;

/**
 * A survey archive of a survey that stores "inherit" for the date stamp gets the date stamp of its survey group,
 * its responses must still be imported if they have no date stamp.
 */
class ImportArchiveInheritedDateStampTest extends TestBaseClass
{
    /** @var string Original date stamp setting of survey group 1 */
    private static $groupDateStamp;

    /** @var string Path of the modified survey archive */
    private static $archive;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $groupSettings = \SurveysGroupsettings::model()->findByPk(1);
        self::$groupDateStamp = $groupSettings->datestamp;
        $groupSettings->datestamp = 'Y';
        $groupSettings->save(false);
        \Survey::model()->resetCache();

        self::$archive = \Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'inherited_datestamp_' . uniqid() . '.lsa';
        copy(self::$surveysFolder . '/survey_archive_2_basic_responses.lsa', self::$archive);
        $zip = new \ZipArchive();
        $zip->open(self::$archive);
        $structure = $zip->getFromName('survey_565531.lss');
        $zip->addFromString('survey_565531.lss', str_replace('<datestamp><![CDATA[N]]></datestamp>', '<datestamp><![CDATA[I]]></datestamp>', $structure));
        $zip->close();
    }

    public static function tearDownAfterClass(): void
    {
        $groupSettings = \SurveysGroupsettings::model()->findByPk(1);
        $groupSettings->datestamp = self::$groupDateStamp;
        $groupSettings->save(false);
        \Survey::model()->resetCache();
        if (is_file(self::$archive)) {
            unlink(self::$archive);
        }
        parent::tearDownAfterClass();
    }

    /**
     * The responses are imported into the response table with date stamp.
     *
     * @return void
     */
    public function testResponsesWithoutDateStampAreImported()
    {
        self::importSurvey(self::$archive);
        \Yii::app()->db->schema->refresh();

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertSame('Y', $survey->active);
        $this->assertSame('Y', $survey->datestamp);
        $this->assertContains('datestamp', \Yii::app()->db->schema->getTable($survey->responsesTableName)->getColumnNames());
        $this->assertSame(2, (int) \SurveyDynamic::model(self::$surveyId)->count());
    }
}
