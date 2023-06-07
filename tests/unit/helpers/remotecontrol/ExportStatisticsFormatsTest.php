<?php

namespace ls\tests;

/**
 * Tests for export_statistics function.
 * Exporting in formats other than HTML.
 */
class ExportStatisticsFormatsTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \Yii::app()->setController(new DummyController('dummyid'));

        // Import survey
        $filename = self::$surveysFolder . '/survey_statistics_one.lsa';
        self::importSurvey($filename);
    }

    /**
     * Testing that exports_statistics returns
     * statistics in xls format.
     */
    public function testXlsStatistics()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $xlsStatistics = $this->handler->export_statistics($sessionKey, self::$surveyId, 'xls');
        $this->assertNotEmpty($xlsStatistics, 'Remote control export_statistics result was empty. Expecting statistics in xls format.');
    }

    /**
     * Testing that exports_statistics returns
     * statistics in pdf format.
     */
    public function testPdfStatistics()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $pdfStatistics = $this->handler->export_statistics($sessionKey, self::$surveyId, 'pdf');
        $this->assertNotEmpty($pdfStatistics, 'Remote control export_statistics result was empty. Expecting statistics in pdf format.');
    }
}
