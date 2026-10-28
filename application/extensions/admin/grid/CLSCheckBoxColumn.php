<?php

/**
 * Checkbox column that gives each row checkbox its own accessible name,
 * so screen readers announce which row is selected (WCAG 4.1.2).
 */
class CLSCheckBoxColumn extends CCheckBoxColumn
{
    /**
     * @var string|null PHP expression evaluated for each row to build the aria-label of the checkbox.
     * In this expression, the variable $data refers to the row data and $row the row number.
     */
    public $ariaLabelExpression;

    /**
     * Returns the data cell content, with the aria-label of the row checkbox.
     *
     * @param integer $row the row number (zero-based)
     * @return string the data cell content
     */
    public function getDataCellContent($row)
    {
        if ($this->ariaLabelExpression === null) {
            return parent::getDataCellContent($row);
        }
        $checkBoxHtmlOptions = $this->checkBoxHtmlOptions;
        $data = $this->grid->dataProvider->data[$row];
        $this->checkBoxHtmlOptions['aria-label'] = $this->evaluateExpression(
            $this->ariaLabelExpression,
            ['data' => $data, 'row' => $row]
        );
        $content = parent::getDataCellContent($row);
        $this->checkBoxHtmlOptions = $checkBoxHtmlOptions;
        return $content;
    }
}
