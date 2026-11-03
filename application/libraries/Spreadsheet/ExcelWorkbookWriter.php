<?php

namespace LimeSurvey\Libraries\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Workbook writer compatibility shim.
 *
 * Drop-in replacement for the subset of the legacy Spreadsheet_Excel_Writer
 * API used by the statistics exporters, backed by PhpSpreadsheet. The output
 * format is XLSX (the legacy library produced the obsolete binary XLS format,
 * which required the abandoned pear/ole package).
 *
 * Usage mirrors the legacy library:
 *  - new ExcelWorkbookWriter($path) writes to a file on close().
 *  - new ExcelWorkbookWriter() streams to the browser; call send($filename)
 *    to set the download headers, then close() to emit the file.
 */
class ExcelWorkbookWriter
{
    public const MIME_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /** @var Spreadsheet */
    private $spreadsheet;

    /** @var string|null Target file path when writing to disk. */
    private $filename;

    /** @var string|null Download file name when streaming to the browser. */
    private $downloadName = null;

    /** @var bool Whether the default first worksheet has been used yet. */
    private $firstSheetUsed = false;

    /**
     * @param string|null $filename Optional target file path. When null, the
     *                              workbook is streamed to the browser.
     */
    public function __construct($filename = null)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->filename = $filename;
    }

    /**
     * Register the download file name used when streaming to the browser.
     * The actual headers are emitted by close().
     *
     * @param string $filename
     * @return void
     */
    public function send($filename)
    {
        $this->downloadName = $this->ensureXlsxExtension($filename);
    }

    /**
     * Add a worksheet and return a writer for it.
     *
     * @param string $name
     * @return ExcelWorksheetWriter
     */
    public function addWorksheet($name)
    {
        if ($this->firstSheetUsed) {
            $sheet = $this->spreadsheet->createSheet();
        } else {
            $sheet = $this->spreadsheet->getActiveSheet();
            $this->firstSheetUsed = true;
        }
        $sheet->setTitle($this->sanitizeTitle((string)$name));
        return new ExcelWorksheetWriter($sheet);
    }

    /**
     * Create a reusable cell format.
     *
     * @param array $properties
     * @return ExcelCellFormat
     */
    public function addFormat(array $properties = [])
    {
        return new ExcelCellFormat($properties);
    }

    /**
     * Finalise the workbook: write it to disk or stream it to the browser.
     *
     * @return void
     */
    public function close()
    {
        $writer = new Xlsx($this->spreadsheet);
        if ($this->filename !== null) {
            $writer->save($this->filename);
        } else {
            if (!headers_sent() && $this->downloadName !== null) {
                header('Content-Type: ' . self::MIME_TYPE);
                header('Content-Disposition: attachment; filename="' . $this->downloadName . '"');
                header('Cache-Control: max-age=0');
            }
            $writer->save('php://output');
        }
        $this->spreadsheet->disconnectWorksheets();
    }

    /**
     * Worksheet titles are limited to 31 characters and may not contain the
     * characters * : / \ ? [ ].
     *
     * @param string $title
     * @return string
     */
    private function sanitizeTitle($title)
    {
        $title = str_replace(['*', ':', '/', '\\', '?', '[', ']'], ' ', $title);
        if (function_exists('mb_substr')) {
            return mb_substr($title, 0, 31);
        }
        return substr($title, 0, 31);
    }

    /**
     * Ensure the download name uses the .xlsx extension.
     *
     * @param string $filename
     * @return string
     */
    private function ensureXlsxExtension($filename)
    {
        $filename = (string)$filename;
        if (preg_match('/\.xlsx$/i', $filename)) {
            return $filename;
        }
        if (preg_match('/\.[^.]+$/', $filename)) {
            return preg_replace('/\.[^.]+$/', '.xlsx', $filename);
        }
        return $filename . '.xlsx';
    }
}
