<?php

namespace ls\tests;

/**
 * Survey archive (.lsa) export and import of inactive surveys with participants.
 */
class ExportArchiveInactiveSurveyTest extends TestBaseClass
{
    /** @var string[] Temporary files to remove after each test */
    private $tempFiles = [];

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.controllers.admin.Export', true);
    }

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_186734.lss');
        \Yii::app()->session['loginID'] = 1;
        \Yii::app()->user->setId(1);
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        foreach ($this->tempFiles as $tempFile) {
            if (is_file($tempFile)) {
                unlink($tempFile);
            }
        }
        $this->tempFiles = [];
        \Yii::app()->session['loginID'] = 1;
        \Yii::app()->db->schema->refresh();
        if (self::$testSurvey) {
            self::$testSurvey->delete();
            self::$testSurvey = null;
        }
        parent::tearDown();
    }

    /**
     * An inactive survey without participants can't be exported as survey archive.
     */
    public function testInactiveSurveyWithoutParticipantsIsNotExported()
    {
        $this->assertFalse(self::$testSurvey->isActive);
        $this->assertFalse(self::$testSurvey->hasTokens());

        $result = $this->exportArchives();

        $this->assertTrue($result['bArchiveIsEmpty']);
        $this->assertFalse($result['aResults'][self::$surveyId]['result']);
    }

    /**
     * An inactive survey with participants is exported as survey archive with the participants but without responses,
     * and importing that archive restores an inactive survey with its participants.
     */
    public function testInactiveSurveyWithParticipantsIsExportedAndImported()
    {
        $this->assertNotEmpty(\Token::createTable(self::$surveyId));
        \Yii::app()->db->schema->refresh();
        $token = \Token::create(self::$surveyId);
        $token->firstname = 'Archived';
        $token->lastname = 'Participant';
        $token->email = 'participant@example.org';
        $token->generateToken();
        $this->assertTrue($token->save(), json_encode($token->getErrors()));
        \Survey::model()->resetCache();
        self::$testSurvey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertFalse(self::$testSurvey->isActive);
        $this->assertTrue(self::$testSurvey->hasTokens());

        $result = $this->exportArchives();

        $this->assertFalse($result['bArchiveIsEmpty']);
        $this->assertTrue($result['aResults'][self::$surveyId]['result']);

        // Extract the survey archive from the ZIP file containing all exported archives
        $sTempDir = \Yii::app()->getConfig('tempdir');
        $sZipPath = $sTempDir . DIRECTORY_SEPARATOR . $result['sZip'];
        $this->tempFiles[] = $sZipPath;
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($sZipPath));
        $sArchiveContent = $zip->getFromName('survey_archive_' . self::$surveyId . '.lsa');
        $zip->close();
        $this->assertNotFalse($sArchiveContent);
        $sArchivePath = $sTempDir . DIRECTORY_SEPARATOR . 'survey_archive_' . randomChars(20) . '.lsa';
        $this->tempFiles[] = $sArchivePath;
        file_put_contents($sArchivePath, $sArchiveContent);

        $archive = new \ZipArchive();
        $this->assertTrue($archive->open($sArchivePath));
        $this->assertNotFalse($archive->locateName('survey_' . self::$surveyId . '.lss'));
        $this->assertNotFalse($archive->locateName('survey_' . self::$surveyId . '_tokens.lst'));
        $this->assertFalse($archive->locateName('survey_' . self::$surveyId . '_responses.lsr'));
        $archive->close();

        $importResult = \importSurveyFile($sArchivePath, false);
        $this->assertTrue(empty($importResult['error']), $importResult['error'] ?? '');
        $this->assertNotEmpty($importResult['newsid']);
        \Survey::model()->resetCache();
        \Yii::app()->db->schema->refresh();
        $importedSurvey = \Survey::model()->findByPk($importResult['newsid']);
        try {
            $this->assertFalse($importedSurvey->isActive, 'The survey must stay inactive when the archive contains no responses.');
            $this->assertTrue($importedSurvey->hasTokensTable);
            $participants = \TokenDynamic::model($importedSurvey->sid)->findAll();
            $this->assertCount(1, $participants);
            $this->assertSame('participant@example.org', $participants[0]->email);
        } finally {
            $importedSurvey->delete();
        }
    }

    /**
     * Exports the survey archive of the test survey through the survey list mass action.
     *
     * @return array Result of Export::exportMultipleSurveys()
     */
    private function exportArchives()
    {
        $export = new \Export(new \AdminController('dummyid'), 'export');
        return $export->exportMultipleSurveys(json_encode([self::$surveyId]), 'archive');
    }
}
