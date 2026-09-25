<?php

namespace LimeSurvey\Libraries\Spreadsheet;

/**
 * Lightweight cell format holder.
 *
 * Compatibility shim that replaces the format object formerly returned by
 * Spreadsheet_Excel_Writer::addFormat(). It stores the small subset of
 * formatting options actually used by the statistics exporters (bold font and
 * a number format code) and is applied to cells by ExcelWorksheetWriter.
 */
class ExcelCellFormat
{
    /** @var bool */
    private $bold = false;

    /** @var string|null */
    private $numberFormat = null;

    /**
     * @param array $properties Subset of the legacy PEAR format properties.
     *                          Recognises the "Bold" key (truthy = bold).
     */
    public function __construct(array $properties = [])
    {
        if (!empty($properties['Bold'])) {
            $this->bold = true;
        }
    }

    /**
     * Set the Excel number format code (e.g. "0.00%").
     *
     * @param string $format
     * @return void
     */
    public function setNumFormat($format)
    {
        $this->numberFormat = $format;
    }

    /**
     * @return bool
     */
    public function isBold()
    {
        return $this->bold;
    }

    /**
     * @return string|null
     */
    public function getNumFormat()
    {
        return $this->numberFormat;
    }
}
