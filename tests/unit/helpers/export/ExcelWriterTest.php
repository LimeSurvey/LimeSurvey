<?php

namespace ls\tests\unit\helpers\export;

use ls\tests\TestBaseClass;

/**
 * Excel export of a survey whose export matches no response.
 */
class ExcelWriterTest extends TestBaseClass
{
    /**
     * Import the test survey and load the export classes.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->loadHelper('admin.exportresults');
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss');
    }

    /**
     * When write() is never called (no response matched), the workbook still has the header row.
     *
     * @return void
     */
    public function testExportWithoutResponsesHasHeaderRow(): void
    {
        $options = new \FormattingOptions();
        $options->output = 'file';
        $options->headingFormat = 'code';
        $options->answerFormat = 'short';
        $options->selectedColumns = ['id', 'submitdate'];

        $survey = (new \SurveyDao())->loadSurveyById(self::$surveyId, 'en', $options);
        $writer = new \ExcelWriter();
        $writer->init($survey, 'en', $options);
        $writer->close();

        try {
            $sheet = $this->readFirstSheet($writer->filename);
        } finally {
            @unlink($writer->filename);
        }

        $this->assertStringContainsString('>id<', $sheet, 'The header row must name the id column');
        $this->assertStringContainsString('>submitdate<', $sheet, 'The header row must name the submitdate column');
    }

    /**
     * The XML of the workbook's first worksheet, with its shared strings inlined.
     *
     * @param string $file Path of the .xlsx file
     * @return string
     */
    private function readFirstSheet(string $file): string
    {
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($file) === true, 'The export is not a valid workbook');
        $sheet = (string)$zip->getFromName('xl/worksheets/sheet1.xml');
        $strings = (string)$zip->getFromName('xl/sharedStrings.xml');
        $zip->close();
        return $sheet . $strings;
    }
}
