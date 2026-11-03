<?php

namespace LimeSurvey\Libraries\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Worksheet writer compatibility shim.
 *
 * Exposes the small subset of the legacy Spreadsheet_Excel_Writer_Worksheet
 * API used by the statistics exporters (write/writeNumber/setColumn) and
 * forwards the calls to a PhpSpreadsheet worksheet.
 *
 * Like the legacy library, row and column indexes are zero-based.
 */
class ExcelWorksheetWriter
{
    /** @var Worksheet */
    private $sheet;

    public function __construct(Worksheet $sheet)
    {
        $this->sheet = $sheet;
    }

    /**
     * Set the width of a range of columns.
     *
     * @param int $firstColumn Zero-based index of the first column.
     * @param int $lastColumn  Zero-based index of the last column.
     * @param float|int $width  Column width.
     * @return void
     */
    public function setColumn($firstColumn, $lastColumn, $width)
    {
        for ($column = (int)$firstColumn; $column <= (int)$lastColumn; $column++) {
            $letter = Coordinate::stringFromColumnIndex($column + 1);
            $this->sheet->getColumnDimension($letter)->setWidth((float)$width);
        }
    }

    /**
     * Write a value to a cell. Values are written as text to preserve the
     * legacy behaviour and to avoid spreadsheet formula injection from
     * user-supplied content (a leading "=" would otherwise be treated as a
     * formula).
     *
     * @param int $row    Zero-based row index.
     * @param int $column Zero-based column index.
     * @param mixed $value
     * @param ExcelCellFormat|null $format
     * @return void
     */
    public function write($row, $column, $value, $format = null)
    {
        $coordinate = [(int)$column + 1, (int)$row + 1];
        $this->sheet->setCellValueExplicit($coordinate, (string)$value, DataType::TYPE_STRING);
        $this->applyFormat($row, $column, $format);
    }

    /**
     * Write a numeric value to a cell.
     *
     * @param int $row    Zero-based row index.
     * @param int $column Zero-based column index.
     * @param float|int $value
     * @param ExcelCellFormat|null $format
     * @return void
     */
    public function writeNumber($row, $column, $value, $format = null)
    {
        $coordinate = [(int)$column + 1, (int)$row + 1];
        $this->sheet->setCellValueExplicit($coordinate, (float)$value, DataType::TYPE_NUMERIC);
        $this->applyFormat($row, $column, $format);
    }

    /**
     * Apply an ExcelCellFormat to a single cell.
     *
     * @param int $row
     * @param int $column
     * @param ExcelCellFormat|null $format
     * @return void
     */
    private function applyFormat($row, $column, $format)
    {
        if (!$format instanceof ExcelCellFormat) {
            return;
        }
        $coordinate = Coordinate::stringFromColumnIndex((int)$column + 1) . ((int)$row + 1);
        $style = $this->sheet->getStyle($coordinate);
        if ($format->isBold()) {
            $style->getFont()->setBold(true);
        }
        $numberFormat = $format->getNumFormat();
        if ($numberFormat !== null) {
            $style->getNumberFormat()->setFormatCode($numberFormat);
        }
    }
}
