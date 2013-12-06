<?php
/**
 * TbTotalSumColumn widget class
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
Yii::import('bootstrap.widgets.TbDataColumn');

class TbTotalSumColumn extends TbDataColumn
{
	public $totalExpression;

	public $totalValue;

	protected $total=0;

	public function init()
	{
		parent::init();

		if (!is_null($this->totalExpression))
		{
			$this->total = is_numeric($this->totalExpression) ? $this->totalExpression : $this->evaluateExpression($this->totalExpression);
		}
		$this->footer = true;
	}

	protected function renderDataCellContent($row, $data)
	{
		ob_start();
		parent::renderDataCellContent($row, $data);
		$value = ob_get_clean();

		if(is_numeric($value))
		{
			$this->total += $value;
		}
		echo $value;
	}

	protected function renderFooterCellContent()
	{
		if(is_null($this->total))
			return parent::renderFooterCellContent();

		echo $this->totalValue? $this->evaluateExpression($this->totalValue, array('total'=>$this->total)) : $this->grid->getFormatter()->format($this->total, $this->type);
	}
}