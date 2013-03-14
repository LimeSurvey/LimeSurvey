<?php
/**
 * TbJsonButtonColumn class
 * Works in conjunction with TbJsonGridView. Renders HTML or returns JSON according to the request to the Grid.
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
Yii::import('bootstrap.widgets.TbButtonColumn');

class TbJsonButtonColumn extends TbButtonColumn
{
	/**
	 * Renders|returns the header cell.
	 */
	public function renderHeaderCell()
	{
		if($this->grid->json)
		{
			ob_start();
			$this->renderHeaderCellContent();
			$content = ob_get_contents();
			ob_end_clean();

			return array('id'=>$this->id, 'content'=>$content);
		}
		parent::renderHeaderCell();
	}

	/**
	 * Renders|returns the data cell
	 * @param int $row
	 * @return array|void
	 */
	public function renderDataCell($row)
	{
		if($this->grid->json)
		{
			$data = $this->grid->dataProvider->data[$row];
			$col = array();
			ob_start();
			$this->renderDataCellContent($row, $data);
			$col['content'] = ob_get_contents();
			ob_end_clean();
			$col['attrs'] = '';
			return $col;
		}

		parent::renderDataCell($row);
	}

	/**
	 * Initializes the default buttons (view, update and delete).
	 */
	protected function initDefaultButtons()
	{
		parent::initDefaultButtons();
		/**
		 * add custom with msgbox instead
		 */
		$this->buttons['delete']['click'] = strtr($this->buttons['delete']['click'],array('yiiGridView'=>'yiiJsonGridView'));

	}
}