<?php
/**
 * TbDataColumn class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

Yii::import('zii.widgets.grid.CDataColumn');

/**
 * Bootstrap grid data column.
 */
class TbDataColumn extends CDataColumn
{
	/**
	 * @var array HTML options for filter input
	 * @see TbDataColumn::renderFilterCellContent()
	 */
	public $filterInputOptions;

	/**
	 * Renders the header cell content.
	 * This method will render a link that can trigger the sorting if the column is sortable.
	 */
	protected function renderHeaderCellContent()
	{
		if ($this->grid->enableSorting && $this->sortable && $this->name !== null)
		{
			$sort = $this->grid->dataProvider->getSort();
			$label = isset($this->header) ? $this->header : $sort->resolveLabel($this->name);

			if ($sort->resolveAttribute($this->name) !== false)
				$label .= '<span class="caret"></span>';

			echo $sort->link($this->name, $label, array('class'=>'sort-link'));
		}
		else
		{
			if ($this->name !== null && $this->header === null)
			{
				if ($this->grid->dataProvider instanceof CActiveDataProvider)
					echo CHtml::encode($this->grid->dataProvider->model->getAttributeLabel($this->name));
				else
					echo CHtml::encode($this->name);
			}
			else
				parent::renderHeaderCellContent();
		}
	}

	/**
	 * Renders the filter cell.
	 * @author antonio ramirez <antonio@clevertech.biz>
	 * @since 24/09/2012 added filterHtmlOptions
	 */
	public function renderFilterCell()
	{
		echo CHtml::openTag('td',$this->filterHtmlOptions);
		echo '<div class="filter-container">';
		$this->renderFilterCellContent();
		echo '</div>';
		echo CHtml::closeTag('td');
	}

	/**
	 * Renders the filter cell content.
	 * On top of Yii's default, here we can provide HTML options for actual filter input
	 * @author Sergii Gamaiunov <hello@webkadabra.com>
	 */
	protected function renderFilterCellContent()
	{
		if(is_string($this->filter))
			echo $this->filter;
		else if($this->filter!==false && $this->grid->filter!==null && $this->name!==null && strpos($this->name,'.')===false)
		{
			if($this->filterInputOptions) {
				$filterInputOptions = $this->filterInputOptions;
				if(empty($filterInputOptions['id'])) {
					$filterInputOptions['id'] = false;
				}
			} else
				$filterInputOptions=array();
			if(is_array($this->filter)) {
				$filterInputOptions['prompt'] = '';
				echo CHtml::activeDropDownList($this->grid->filter, $this->name, $this->filter, $filterInputOptions);
			} else if($this->filter===null) {
				echo CHtml::activeTextField($this->grid->filter, $this->name, $filterInputOptions);
			}
		}
		else
			parent::renderFilterCellContent();
	}
}
