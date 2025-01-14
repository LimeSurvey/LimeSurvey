<?php

namespace ls\tests;

/**
 * Tests for the GititSurvey remote API.
 */
class RemoteControlExportStatisticsTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \Yii::app()->setController(new DummyController('dummyid'));
    }

    public function testSurveyWithOneQuestion()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_statistics_one.lsa';
        self::importSurvey($filename);

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $htmlStatistics = $this->handler->export_statistics($sessionKey, self::$surveyId, 'html');
        $htmlStatistics = base64_decode($htmlStatistics);

        $this->assertIsString($htmlStatistics, 'The html statistics were not returned or decoded correctly.');

        $questions = \Question::model()->getQuestionList(self::$surveyId);

        $q1Data = $this->getTableData($htmlStatistics, 'quid_' . $questions[0]->qid);

        $this->assertNotEmpty($q1Data, 'The statistics table or data were not found in the html string.');

        // Option A row.
        $this->assertSame($q1Data[0][0], 'Option A (A)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[0][1], '2', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[0][2], '20.00%', 'The Percentage is incorrect for this option.');

        // Option B row.
        $this->assertSame($q1Data[1][0], 'Option B (B)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[1][1], '3', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[1][2], '30.00%', 'The Percentage is incorrect for this option.');

        // Option C row.
        $this->assertSame($q1Data[2][0], 'Option C (C)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[2][1], '3', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[2][2], '30.00%', 'The Percentage is incorrect for this option.');

        // No answer row.
        $this->assertSame($q1Data[3][0], 'No answer', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[3][1], '2', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[3][2], '20.00%', 'The Percentage is incorrect for this option.');

        // Not completed row.
        $this->assertSame($q1Data[4][0], 'Not completed or Not displayed', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[4][1], '0', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[4][2], '0.00%', 'The Percentage is incorrect for this option.');

        // A row is left empty.
        $this->assertEmpty($q1Data[5], 'This row should be empty.');

        // Total row.
        $this->assertSame($q1Data[6][0], 'Total(gross)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[6][1], '10', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[6][2], '100.00%', 'The Percentage is incorrect for this option.');
    }

    public function testSurveyWithThreeQuestions()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_statistics_two.lsa';
        self::importSurvey($filename);

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $htmlStatistics = $this->handler->export_statistics($sessionKey, self::$surveyId, 'html');
        $htmlStatistics = base64_decode($htmlStatistics);

        $questions = \Question::model()->getQuestionList(self::$surveyId);

        // Q00
        $q1Data = $this->getTableData($htmlStatistics, 'quid_' . $questions[0]->qid);

        // Option A row.
        $this->assertSame($q1Data[0][0], 'Q00 option A (Q00A)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[0][1], '4', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[0][2], '40.00%', 'The Percentage is incorrect for this option.');

        // Option B row.
        $this->assertSame($q1Data[1][0], 'Q00 option B (Q00B)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[1][1], '2', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[1][2], '20.00%', 'The Percentage is incorrect for this option.');

        // Option C row.
        $this->assertSame($q1Data[2][0], 'Q00 option C (Q00C)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[2][1], '1', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[2][2], '10.00%', 'The Percentage is incorrect for this option.');

        // No answer row.
        $this->assertSame($q1Data[3][0], 'No answer', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[3][1], '3', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[3][2], '30.00%', 'The Percentage is incorrect for this option.');

        // Not completed row.
        $this->assertSame($q1Data[4][0], 'Not completed or Not displayed', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[4][1], '0', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[4][2], '0.00%', 'The Percentage is incorrect for this option.');

        // A row is left empty.
        $this->assertEmpty($q1Data[5], 'This row should be empty.');

        // Total row.
        $this->assertSame($q1Data[6][0], 'Total(gross)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[6][1], '10', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[6][2], '100.00%', 'The Percentage is incorrect for this option.');

        // Q01
        $q1Data = $this->getTableData($htmlStatistics, 'quid_' . $questions[1]->qid);

        // Option 1 row.
        $this->assertSame($q1Data[0][0], '1 (1)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[0][1], '1', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[0][2], '10.00%', 'The Percentage is incorrect for this option.');

        // Option 2 row.
        $this->assertSame($q1Data[1][0], '2 (2)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[1][1], '1', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[1][2], '10.00%', 'The Percentage is incorrect for this option.');

        // Option 3 row.
        $this->assertSame($q1Data[2][0], '3 (3)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[2][1], '2', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[2][2], '20.00%', 'The Percentage is incorrect for this option.');

        // Option 4 row.
        $this->assertSame($q1Data[3][0], '4 (4)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[3][1], '2', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[3][2], '20.00%', 'The Percentage is incorrect for this option.');

        // Option 5 row.
        $this->assertSame($q1Data[4][0], '5 (5)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[4][1], '2', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[4][2], '20.00%', 'The Percentage is incorrect for this option.');

        // total valid.
        $this->assertSame($q1Data[5][0], 'Total(valid)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[5][1], '8', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[5][2], '', 'The Percentage is incorrect for this option.');

        // No answer row.
        $this->assertSame($q1Data[6][0], 'No answer', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[6][1], '2', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[6][2], '20.00%', 'The Percentage is incorrect for this option.');

        // Not completed row.
        $this->assertSame($q1Data[7][0], 'Not completed or Not displayed', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[7][1], '0', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[7][2], '0.00%', 'The Percentage is incorrect for this option.');

        // A row is left empty.
        $this->assertEmpty($q1Data[8], 'This row should be empty.');

        // Total row.
        $this->assertSame($q1Data[9][0], 'Total(gross)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[9][1], '10', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[9][2], '100.00%', 'The Percentage is incorrect for this option.');

        // Q02
        $q1Data = $this->getTableData($htmlStatistics, 'quid_' . $questions[2]->qid);

        // Option A row.
        $this->assertSame($q1Data[0][0], 'Question 2 option A (Q02A)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[0][1], '3', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[0][2], '30.00%', 'The Percentage is incorrect for this option.');

        // Option B row.
        $this->assertSame($q1Data[1][0], 'Question 2 option B (Q02B)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[1][1], '3', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[1][2], '30.00%', 'The Percentage is incorrect for this option.');

        // Option C row.
        $this->assertSame($q1Data[2][0], 'Question 2 option C (Q02C)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[2][1], '2', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[2][2], '20.00%', 'The Percentage is incorrect for this option.');

        // No answer row.
        $this->assertSame($q1Data[3][0], 'No answer', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[3][1], '2', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[3][2], '20.00%', 'The Percentage is incorrect for this option.');

        // Not completed row.
        $this->assertSame($q1Data[4][0], 'Not completed or Not displayed', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[4][1], '0', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[4][2], '0.00%', 'The Percentage is incorrect for this option.');

        // A row is left empty.
        $this->assertEmpty($q1Data[5], 'This row should be empty.');

        // Total row.
        $this->assertSame($q1Data[6][0], 'Total(gross)', 'The Answer text is incorrect for this option.');
        $this->assertSame($q1Data[6][1], '10', 'The Count is incorrect for this option.');
        $this->assertSame($q1Data[6][2], '100.00%', 'The Percentage is incorrect for this option.');
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

    /**
     * Get the statistics data from the table with the
     * id specified.
     */
    private function getTableData($htmlStatistics, $tableHtmlId)
    {
        $options = array();

        // Get the table.
        $doc = new \DOMDocument();
        $doc->loadHtml($htmlStatistics);

        $table = $doc->getElementById($tableHtmlId);

        if ($table === null) {
            return $options;
        }

        $thead = $table->getElementsByTagName('thead')->item(0);
        // The td tags should be identified with the opening tbody tag but it is not added by export_statistics.
        $table->removeChild($thead);

        $rows = $table->getElementsByTagName('tr');

        foreach ($rows as $rowKey => $row) {
            $tds = $row->getElementsByTagName('td');
            $option = array();

            foreach ($tds as $td) {
                $option[] = trim($td->nodeValue);
            }

            $options[] = $option;
        }

        return $options;
    }
}
