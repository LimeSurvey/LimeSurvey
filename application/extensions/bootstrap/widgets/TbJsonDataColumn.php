<?php
/**
 * TbJsonDataColumn class
 *
 * This column works specifically with TbJsonGridView.
 *
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
Yii::import('bootstrap.widgets.TbJsonGridColumn');

class TbJsonDataColumn extends TbJsonGridColumn
{
	/**
	 * Renders a data cell.
	 * @param integer $row the row number (zero-based)
	 */
	public function renderDataCell($row)
	{
		if($this->grid->json)
		{
			$data = $this->grid->dataProvider->data[$row];
			$options = $this->htmlOptions;
			if ($this->cssClassExpression !== null)
			{
				$class = $this->evaluateExpression($this->cssClassExpression, array('row' => $row, 'data' => $data));
				if (isset($options['class']))
					$options['class'] .= ' ' . $class;
				else
					$options['class'] = $class;
			}
			$col = array();
			$col['attrs'] = CHtml::renderAttributes($options);
			$col['content'] = $this->renderDataCellContent($row, $data);
			return $col;
		}
		parent::renderDataCell($row);
	}

	/**
	 * Renders the data cell content.
	 * This method evaluates {@link value} or {@link name} and renders the result.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
	 */
	public function renderDataCellContent($row, $data)
	{
		if($this->grid->json)
		{
			if ($this->value !== null)
				$value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row));
			else if ($this->name !== null)
				$value = CHtml::value($data, $this->name);
			$value = $value === null ? $this->grid->nullDisplay : $this->grid->getFormatter()->format($value, $this->type);

			return $value;
		}
		parent::renderDataCellContent($row, $data);
	}

}
