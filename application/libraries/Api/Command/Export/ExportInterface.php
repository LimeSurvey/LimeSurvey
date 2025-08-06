<?php

namespace LimeSurvey\Api\Command\Export;

interface ExportInterface
{
    /**
     * @param array $data The data to export
     * @param string $filename Name without extension
     * @param bool $download If true, send file directly to browser
     */
    public function export(array $data, string $filename, bool $download = true);
}
