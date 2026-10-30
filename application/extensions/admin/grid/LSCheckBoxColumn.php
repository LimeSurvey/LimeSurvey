<?php

Yii::import('zii.widgets.grid.CCheckBoxColumn');

/**
 * Checkbox column that gives every row checkbox an accessible name.
 *
 * CLSGridView uses this class instead of CCheckBoxColumn, so row checkboxes
 * are announced by screen readers as e.g. "Select survey Foo" instead of just "checkbox".
 */
class LSCheckBoxColumn extends CCheckBoxColumn
{
    /**
     * @var string|callable|null A PHP expression or callable evaluated for every data cell,
     * returning the aria-label of the row checkbox.
     * Variables: $data (the row data), $row (zero-based row number) and $this (the column);
     * a callable gets them as arguments.
     * If not set, a generic "Select row N" label is used.
     */
    public $ariaLabel;

    /**
     * Returns the data cell content, a checkbox with an aria-label for the row.
     *
     * @param integer $row The row number (zero-based)
     * @return string The data cell content
     */
    #[\Override]
    public function getDataCellContent($row)
    {
        if (isset($this->checkBoxHtmlOptions['aria-label']) || isset($this->checkBoxHtmlOptions['aria-labelledby'])) {
            return parent::getDataCellContent($row);
        }
        $checkBoxHtmlOptions = $this->checkBoxHtmlOptions;
        $this->checkBoxHtmlOptions['aria-label'] = $this->getRowAriaLabel($row);
        $content = parent::getDataCellContent($row);
        $this->checkBoxHtmlOptions = $checkBoxHtmlOptions;
        return $content;
    }

    /**
     * Returns the aria-label of the checkbox in the given row.
     *
     * @param integer $row The row number (zero-based)
     * @return string
     */
    protected function getRowAriaLabel($row)
    {
        if ($this->ariaLabel !== null) {
            $data = $this->grid->dataProvider->data[$row];
            $label = trim((string) $this->evaluateExpression($this->ariaLabel, ['data' => $data, 'row' => $row]));
            if ($label !== '') {
                return $label;
            }
        }
        return sprintf(gT('Select row %s'), $row + 1);
    }
}
