<?php
use ls\models\forms\FormattingOptions;
class CsvWriter extends Writer
{
    private $separator = ',';
    private $hasOutputHeader;

    protected function renderRecord($headers, $values)
    {
        //Output the values.
        return implode(array_map([$this, 'csvEscape'], $values), $this->separator) . "\n";
    }

    /**
    * Returns the value with all necessary escaping needed to place it into a CSV string.
    *
    * @param string $value
    * @return string
    */
    protected function csvEscape($value)
    {
        return '"' . str_replace('"','""', preg_replace('~\R~u', "\n", $value)) . '"';
    }

    public function getMimeType()
    {
        return 'text/csv';
    }

    public function getFileName(Survey $survey, $language)
    {
        return "responses_{$this->options->surveyId}.csv";
    }

    public function beforeRenderRecords($headers)
    {
        return $this->renderRecord($headers, $headers);
    }


}