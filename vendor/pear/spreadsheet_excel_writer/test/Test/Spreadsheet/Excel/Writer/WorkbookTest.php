<?php

/**
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2016-01-17
 */
class Test_Spreadsheet_Excel_Writer_WorkbookTest extends Test_Spreadsheet_Excel_WriterTestCase
{
    public static function doSetUpBeforeClass()
    {
        // Preload constants from OLE
        @class_exists(OLE::class);
    }

    public function testSetVersion()
    {
        $workbook = $this->getNewWorkbook();

        $before = get_object_vars($workbook);

        $workbook->setVersion(1);

        $this->assertEquals($before, get_object_vars($workbook), "Version 1 should not change internal state");

        $workbook->setVersion(8);

        $this->assertNotEquals($before, get_object_vars($workbook), "Version 8 should change internal state");

        return $workbook;
    }

    /**
     * @depends testSetVersion
     */
    public function testWriteSingleCell(Spreadsheet_Excel_Writer $workbook)
    {
        $sheet = $workbook->addWorksheet("Example");
        $sheet->write(0, 0, "Example");

        $this->assertSameAsInFixture('example.xls', $workbook);
    }

    public function testWriteWithFormat()
    {
        $workbook = $this->getNewWorkbook();
        $workbook->setVersion(8);

        $format = $workbook->addFormat();
        $format->setFontFamily('Helvetica');
        $format->setSize(16);
        $format->setVAlign('vcenter');
        $format->setBorder(1);

        $sheet = $workbook->addWorksheet('Example report');
        $sheet->setInputEncoding('utf-8');

        $sheet->setColumn(0, 10, 35);

        $sheet->writeString(0, 0, "Test string", $format);
        $sheet->setRow(0, 40);

        $sheet->writeString(1, 0, "こんにちわ");

        $this->assertSameAsInFixture('with_format.xls', $workbook);
    }

    public function testWithDefaultVersion()
    {
        $workbook = $this->getNewWorkbook();

        $sheet = $workbook->addWorksheet("Example");

        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 10; $j++) {
                $sheet->write($i, $j, "Row $i $j");
            }
        }

        $this->assertSameAsInFixture('example2.xls', $workbook);
    }
}
