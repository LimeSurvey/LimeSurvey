<?php

namespace Test\Spreadsheet\Excel\Writer;

use Spreadsheet_Excel_Writer_Worksheet;
use Spreadsheet_Excel_Writer_Workbook;

class WorksheetTest extends \LegacyPHPUnit\TestCase
{
    private $workbook;
    private $worksheet;

    public function doSetUp()
    {
        parent::doSetUp();
        $this->workbook = new Spreadsheet_Excel_Writer_Workbook('php://memory');

        $activesheet = 0;
        $str_total = 0;
        $str_unique = 0;
        $str_table = 0;
        $url_format = '';
        $parser = '';
        $tmp_dir = '';

        $this->worksheet = new Spreadsheet_Excel_Writer_Worksheet(0x0500, 'Test', 0, $activesheet, $this->workbook->_url_format, $str_total, $str_unique, $str_table, $url_format, $parser, $tmp_dir);
    }

    public function doTearDown()
    {
        if ($this->workbook) {
            $this->workbook->close();
        }
        parent::doTearDown();
    }

    /**
     * Test that _substituteCellref handles regex with dollar signs correctly
     */
    public function testSubstituteCellrefWithDollarSigns()
    {
        $method = new \ReflectionMethod($this->worksheet, '_substituteCellref');
        $method->setAccessible(true);

        // Test absolute cell reference
        $result = $method->invoke($this->worksheet, '$A$1');
        $this->assertEquals([0, 0], $result);

        // Test mixed references
        $result = $method->invoke($this->worksheet, 'A$1');
        $this->assertEquals([0, 0], $result);

        $result = $method->invoke($this->worksheet, '$A1');
        $this->assertEquals([0, 0], $result);

        // Test cell range with dollar signs
        $result = $method->invoke($this->worksheet, '$A$1:$B$2');
        $this->assertEquals([0, 0, 1, 1], $result);
    }

    /**
     * Test that _cellToRowcol handles regex with dollar signs correctly
     */
    public function testCellToRowcolWithDollarSigns()
    {
        $method = new \ReflectionMethod($this->worksheet, '_cellToRowcol');
        $method->setAccessible(true);

        // Test with absolute reference
        $result = $method->invoke($this->worksheet, '$A$1');
        $this->assertEquals([0, 0], $result);

        // Test with relative reference
        $result = $method->invoke($this->worksheet, 'B2');
        $this->assertEquals([1, 1], $result);

        // Test with mixed reference
        $result = $method->invoke($this->worksheet, '$C3');
        $this->assertEquals([2, 2], $result);
    }

    /**
     * Test that getData properly handles clearing _data property
     */
    public function testGetDataClearsDataProperty()
    {
        // Access protected property
        $property = new \ReflectionProperty($this->worksheet, '_data');
        $property->setAccessible(true);

        // Set some data
        $testData = 'test data';
        $property->setValue($this->worksheet, $testData);

        // Call getData
        $result = $this->worksheet->getData();

        // Check that data was returned
        $this->assertEquals($testData, $result);

        // Check that _data is now null (not unset)
        $this->assertNull($property->getValue($this->worksheet));
    }
}
