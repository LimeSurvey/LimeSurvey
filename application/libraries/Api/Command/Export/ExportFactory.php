<?php

namespace LimeSurvey\Api\Command\Export;

use InvalidArgumentException;
use LimeSurvey\Api\Command\Export\ExportTypes\ExportCSV;
use LimeSurvey\Api\Command\Export\ExportTypes\ExportPDF;
use LimeSurvey\Api\Command\Export\ExportTypes\ExportXLS;

class ExportFactory
{
    private const TYPE_CSV = 'csv';
    private const TYPE_PDF = 'pdf';
    private const TYPE_XLS = 'xls';

    /**
     * @param string $type
     * @return ExportInterface
     * @throws InvalidArgumentException
     */
    public static function create(string $type): ExportInterface
    {
        switch (strtolower($type)) {
            case self::TYPE_CSV:
                return new ExportCSV();
            case self::TYPE_PDF:
                return new ExportPDF();
            case self::TYPE_XLS:
                return new ExportXLS();
            default:
                throw new InvalidArgumentException("Unsupported export type: {$type}");
        }
    }
}
