<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Quota;
use QuotaLanguageSetting;
use QuotaMember;
use Yii;

/**
 * Tests that quota rules left over from deleted quotas or questions do not break
 * the survey import and are not exported (see #19577).
 */
class ImportExportOrphanedQuotaMembersTest extends TestBaseClass
{
    /** @var string Survey file with quota rules referencing a missing quota and a missing question */
    private static $surveyFile;

    /**
     * Builds the survey file and imports it.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.admin.import_helper', true);
        Yii::import('application.helpers.export_helper', true);

        $dom = new \DOMDocument();
        $dom->load(self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss');
        $xpath = new \DOMXPath($dom);
        $memberRows = $xpath->query('/document/quota_members/rows')->item(0);
        // Quota 99 is not in the file
        self::appendRow($dom, $memberRows, ['id' => 90, 'sid' => 376789, 'qid' => 17, 'quota_id' => 99, 'code' => 'M']);
        // Question 99999 is not in the file
        self::appendRow($dom, $memberRows, ['id' => 91, 'sid' => 376789, 'qid' => 99999, 'quota_id' => 1, 'code' => 'M']);
        $languageSettingRows = $xpath->query('/document/quota_languagesettings/rows')->item(0);
        self::appendRow($dom, $languageSettingRows, ['quotals_id' => 90, 'quotals_quota_id' => 99, 'quotals_language' => 'en', 'quotals_message' => 'Orphan']);

        self::$surveyFile = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'limesurvey_survey_19577_' . uniqid() . '.lss';
        $dom->save(self::$surveyFile);
        self::importSurvey(self::$surveyFile);
    }

    /**
     * Removes the generated survey file and the imported survey.
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$surveyFile && file_exists(self::$surveyFile)) {
            unlink(self::$surveyFile);
        }
        parent::tearDownAfterClass();
    }

    /**
     * Appends a row with the given fields to a rows element of a survey file.
     *
     * @param \DOMDocument $dom
     * @param \DOMElement $rows
     * @param array $fields Field name => value
     * @return void
     */
    private static function appendRow(\DOMDocument $dom, \DOMElement $rows, array $fields)
    {
        $row = $rows->appendChild($dom->createElement('row'));
        foreach ($fields as $name => $value) {
            $row->appendChild($dom->createElement($name))->appendChild($dom->createCDATASection((string) $value));
        }
    }

    /**
     * Quota rules and language settings without quota or question are skipped with a warning.
     */
    public function testImportSkipsOrphanedQuotaMembers()
    {
        $quotaIds = array_map(
            function ($quota) {
                return (int) $quota->id;
            },
            Quota::model()->findAllByAttributes(['sid' => self::$surveyId])
        );
        $this->assertCount(3, $quotaIds);

        $members = QuotaMember::model()->findAllByAttributes(['sid' => self::$surveyId]);
        $this->assertCount(3, $members);
        foreach ($members as $member) {
            $this->assertContains((int) $member->quota_id, $quotaIds);
        }

        $languageSettings = QuotaLanguageSetting::model()->findAll('quotals_message = :message', [':message' => 'Orphan']);
        $this->assertEmpty($languageSettings);
    }

    /**
     * The import reports the skipped quota rules.
     */
    public function testImportWarnsAboutSkippedQuotaMembers()
    {
        $results = importSurveyFile(self::$surveyFile, false);
        try {
            $this->assertContains(
                'Quota rules not imported because their quota or question is missing in the survey file: 2',
                $results['importwarnings']
            );
        } finally {
            Yii::app()->session['loginID'] = 1;
            \Survey::model()->findByPk($results['newsid'])->delete();
        }
    }

    /**
     * Quota rules of a deleted quota are not exported.
     */
    public function testExportSkipsQuotaMembersWithoutQuota()
    {
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0]);
        Yii::app()->db->createCommand()->insert(
            '{{quota_members}}',
            ['sid' => self::$surveyId, 'qid' => $question->qid, 'quota_id' => 999999905, 'code' => 'X']
        );
        $orphanId = getLastInsertID('{{quota_members}}');

        try {
            $xml = simplexml_load_string(surveyGetXMLData(self::$surveyId));
            $exportedQuotaIds = [];
            foreach ($xml->quota->rows->row as $row) {
                $exportedQuotaIds[] = (int) $row->id;
            }
            $this->assertCount(3, $xml->quota_members->rows->row);
            foreach ($xml->quota_members->rows->row as $row) {
                $this->assertContains((int) $row->quota_id, $exportedQuotaIds);
            }
        } finally {
            QuotaMember::model()->deleteByPk($orphanId);
        }
    }
}
