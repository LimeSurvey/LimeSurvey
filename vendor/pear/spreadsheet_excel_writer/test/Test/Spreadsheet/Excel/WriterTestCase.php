<?php

/**
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2016-01-17
 */
class Test_Spreadsheet_Excel_WriterTestCase extends \LegacyPHPUnit\TestCase
{
    const FIXTURES_PATH = 'test/fixture/';

    /**
     * @param string $filename
     * @return Spreadsheet_Excel_Writer
     */
    protected function getNewWorkbook($filename = '')
    {
        // we're writing to the standard output by defaulr
        return new Spreadsheet_Excel_Writer($filename);
    }

    protected function assertSameAsInFixture($filename, Spreadsheet_Excel_Writer $workbook)
    {
        $this->assertEmpty($workbook->_filename, "Testing with fixtures works only for standard output");

        // we have to fix timestamp for fixtures to work
        $workbook->_timestamp = 1000000000; // somewhere in 2001

        ob_start();
        $workbook->close();
        $data = ob_get_clean();

        $fullPath = self::FIXTURES_PATH.$filename;

        if ($this->shouldUpdateFixtures()) {
            file_put_contents($fullPath, $data);
        }

        if (!is_file($fullPath)) {
            $this->fail("Fixture $filename not found");
        }

        // TODO: should we save data for future analysis?
        //file_put_contents("{$fullPath}.work", $data);

        $this->assertEquals(file_get_contents($fullPath), $data, "Output differs for $filename");
    }

    /**
     * We should update golden files
     */
    private function shouldUpdateFixtures()
    {
        return isset($_SERVER['GOLDEN']);
    }
}