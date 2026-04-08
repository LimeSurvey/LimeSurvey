<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlExportStatisticsArrayQuestionsTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \Yii::app()->setController(new DummyController('dummyid'));
    }

    public function testSurveyWithOneQuestion()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_statistics_three.lsa';
        self::importSurvey($filename);

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $htmlStatistics = $this->handler->export_statistics($sessionKey, self::$surveyId, 'html');
        $htmlStatistics = base64_decode($htmlStatistics);

        // Q00
        $questionData = $this->getTableData($htmlStatistics);

        // Subquestion one

        // Option one row.
        $this->assertSame($questionData[0][0], 'Option one (AO01)', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[0][1], '3', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[0][2], '30.00%', 'The Percentage is incorrect for this option.');

        // Option two row.
        $this->assertSame($questionData[1][0], 'Option two (AO02)', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[1][1], '4', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[1][2], '40.00%', 'The Percentage is incorrect for this option.');

        // No answer row.
        $this->assertSame($questionData[2][0], 'No answer', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[2][1], '3', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[2][2], '30.00%', 'The Percentage is incorrect for this option.');

        // Not completed row.
        $this->assertSame($questionData[3][0], 'Not completed or Not displayed', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[3][1], '0', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[3][2], '0.00%', 'The Percentage is incorrect for this option.');

        // A row is left empty.
        $this->assertEmpty($questionData[4], 'This row should be empty.');

        // Total row.
        $this->assertSame($questionData[5][0], 'Total(gross)', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[5][1], '10', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[5][2], '100.00%', 'The Percentage is incorrect for this option.');


        // Subquestion two

        // Option one row.
        $this->assertSame($questionData[6][0], 'Option one (AO01)', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[6][1], '4', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[6][2], '40.00%', 'The Percentage is incorrect for this option.');

        // Option two row.
        $this->assertSame($questionData[7][0], 'Option two (AO02)', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[7][1], '3', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[7][2], '30.00%', 'The Percentage is incorrect for this option.');

        // No answer row.
        $this->assertSame($questionData[8][0], 'No answer', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[8][1], '3', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[8][2], '30.00%', 'The Percentage is incorrect for this option.');

        // Not completed row.
        $this->assertSame($questionData[9][0], 'Not completed or Not displayed', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[9][1], '0', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[9][2], '0.00%', 'The Percentage is incorrect for this option.');

        // A row is left empty.
        $this->assertEmpty($questionData[10], 'This row should be empty.');

        // Total row.
        $this->assertSame($questionData[11][0], 'Total(gross)', 'The Answer text is incorrect for this option.');
        $this->assertSame($questionData[11][1], '10', 'The Count is incorrect for this option.');
        $this->assertSame($questionData[11][2], '100.00%', 'The Percentage is incorrect for this option.');
    }

    /**
     * Get the statistics data from the table with the
     * id specified.
     */
    private function getTableData($htmlStatistics)
    {
        $options = array();

        // Get the table.
        $doc = new \DOMDocument();
        $doc->loadHtml($htmlStatistics);

        $tables = $doc->getElementsByTagName('table');

        if ($tables === null) {
            return $options;
        }

        foreach ($tables as $table) {
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
        }

        return $options;
    }
}
