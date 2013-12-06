<?php
/**
 * TbJsonGridColumn class
 *
 * This column works specifically with TbJsonGridView
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */

Yii::import('bootstrap.widgets.TbDataColumn');

class TbJsonGridColumn extends TbDataColumn {

	/**
	 * Renders the header cell.
	 */
	public function renderHeaderCell()
	{
		if($this->grid->json)
		{
			$header = array('id'=>$this->id);
			$content = array();
			if($this->grid->enableSorting && $this->sortable && $this->name !== null)
			{
				$sort = $this->grid->dataProvider->getSort();
				$label = isset($this->header) ? $this->header : $sort->resolveLabel($this->name);

				if ($sort->resolveAttribute($this->name) !== false)
					$label .= '<span class="caret"></span>';
				$content['content'] = $sort->link($this->name, $label, array('class'=>'sort-link'));
			}
			else
			{
				if ($this->name !== null && $this->header === null)
				{
					if ($this->grid->dataProvider instanceof CActiveDataProvider)
						$content['content'] = CHtml::encode($this->grid->dataProvider->model->getAttributeLabel($this->name));
					else
						$content['content'] = CHtml::encode($this->name);
				}
				else
					$content['content'] = trim($this->header)!=='' ? $this->header : $this->grid->blankDisplay;
			}
			return CMap::mergeArray($header, $content);
		}
		parent::renderHeaderCell();
	}
}