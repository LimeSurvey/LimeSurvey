<?php
/**
 * TbExtendedFilter widget
 *
 * This widget displays an extra row to the grid is attached to and renders a visual feedback of the filter values used
 * plus an option to save them for later use.
 *
 * @uses JSONStorage component
 * @package bootstrap.widgets
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 10/15/12
 * Time: 12:12 PM
 */
Yii::import('bootstrap.components.JSONStorage', true);

class TbExtendedFilter extends CWidget
{
	/**
	 * @var CActiveRecord the model that works as a filter to the grid
	 */
	public $model;

	/**
	 * @var CGridView the grid view the widget is going to be used with
	 */
	public $grid;

	/**
	 * @var string the name of the json registry to save the value
	 */
	protected $registry = 'extended-filter';

	/**
	 * @var string the ajax variable that is used to check whether a filter is to be saved
	 */
	public $saveFilterVar = 'saveFilterAs';

	/**
	 * @var string the ajax variable that is used to check whether to delete a filter from the registry
	 */
	public $removeFilterVar = 'removeFilter';

	/**
	 * @var array the cleaned filter attributes
	 */
	public $filteredBy;

	/**
	 * @var array the route to redirect when saving/removing a filter
	 */
	public $redirectRoute;

	/**
	 * @var JSONStorage Component
	 */
	protected $jsonStorage;

	/**
	 * Widget initialization
	 * @throws CException
	 */
	public function init()
	{
		if (!$this->model instanceof CActiveRecord)
			throw new CException(Yii::t('zii', '"model" attribute must be an CActiveRecord type of component'));

		if (!$this->grid instanceof CGridView)
			throw new CException(Yii::t('zii', '"grid" attribute must be an CGridView type of component'));

		if (!$this->redirectRoute === null)
			throw new CException(Yii::t('zii', '"redirectRoute" cannot be empty'));

		$this->registry .= '-'.$this->grid->id;

		$this->jsonStorage = new JSONStorage();
		$this->jsonStorage->addRegistry($this->registry);

		$this->filteredBy = array_filter($this->model->getAttributes(), function($i){ return $i != null;});

		$this->checkRequestRemovalFilter();
		$this->checkRequestFilters();

		$this->registerClientScript();
	}

	/**
	 * Checks whether there has been send the command to remove a filter from the registry and redirects to
	 * specified route
	 */
	protected function checkRequestRemovalFilter()
	{
		if($key = Yii::app()->getRequest()->getParam($this->removeFilterVar))
		{
			if($this->jsonStorage->removeData($key, $this->registry))
			{
				Yii::app()->getController()->redirect($this->redirectRoute);
			}
		}
	}

	/**
	 * Checkes whether there has been send the command to save a filter to the registry and redirects to
	 * specified route
	 * @return bool
	 */
	protected function checkRequestFilters()
	{
		if ($filterName = Yii::app()->getRequest()->getParam($this->saveFilterVar))
		{

			if (!count($this->filteredBy))
				return false;

			$key = $this->generateRegistryItemKey();

			if ($this->jsonStorage->getData($key, $this->registry))
				return false;

			$data = array('name' => $filterName);

			$data['options'] = array(get_class($this->model) => $this->filteredBy);

			$this->jsonStorage->setData($key, $data, $this->registry);

			Yii::app()->getController()->redirect($this->redirectRoute);
		}
	}

	/**
	 * Widget's run method
	 */
	public function run()
	{

		$registryKey = $this->generateRegistryItemKey($this->filteredBy);

		if (!count($this->filteredBy) && !$this->jsonStorage->getLength($this->registry))
			return;

		echo "<tr>\n";
		$cols = count($this->grid->columns);
		echo "<td colspan='{$cols}'>\n";
		echo "<div id='{$this->getId()}'>\n";
		if(count($this->filteredBy))
			echo '<p><span class="label label-success">Filtered by</span> ' . $this->displayExtendedFilterValues($this->filteredBy) . '</p>';

		$this->displaySaveButton($registryKey);

		$this->displaySavedFilters($registryKey);

		echo "</div>\n";
		echo "</td>\n";
		echo "</tr>\n";
	}

	/**
	 * Registers the required
	 */
	public function registerClientScript()
	{
		$url = CHtml::normalizeUrl($this->redirectRoute);

		Yii::app()->clientScript->registerScript(__CLASS__ . '#extended-filter' . $this->grid->id, <<<EOD
        $(document).on('click', '#{$this->grid->id} .btn-extended-filter-save', function(e){
            e.preventDefault();
            bootbox.prompt("How do you wish to save this filter?", "Cancel", "Ok", function(result){
                if($.trim(result).length > 0)
                {
                    $('#{$this->grid->id}').yiiGridView('update',{data:{{$this->saveFilterVar}:result}});
                }
            });
        });
        $(document).on('click', '#{$this->grid->id} .btn-extended-filter-apply', function(e) {
            e.preventDefault();
            var option = $('#{$this->getId()} select.select-extended-filter option:selected');
            if(!option.length || !option.data('filter'))
            {
                return false;
            }
            var data ={data:option.data('filter')};
            if(option.val()==-1)
            {
                data.url = "{$url}";
            }
            $('#{$this->grid->id}').yiiGridView('update',data);
        });

        $(document).on('click', '#{$this->grid->id} .btn-extended-filter-delete', function(e) {
            e.preventDefault();
            var option = $('#{$this->grid->id} select.select-extended-filter option:selected');
            if(!option.length || !option.data('key') || option.val()==-1)
            {
                return false;
            }
            bootbox.confirm('Delete "'+option.text()+'" filter?', function(confirmed){
                if(confirmed)
                {
                    $('#{$this->grid->id}').yiiGridView('update',{data:{{$this->removeFilterVar}:option.data('key')}});
                }
            });
        });
EOD
		);
	}

	/**
	 * Displays the save filter button
	 * @param $registryKey
	 * @return bool
	 */
	protected function displaySaveButton($registryKey)
	{

		if (null == $registryKey || $this->jsonStorage->getData($registryKey, $this->registry))
			return false;

		echo '<p>' . CHtml::link('save filter', '#', array('class' => 'btn btn-success btn-extended-filter-save')) . '</p>';
	}

	/**
	 * displays the saved filters as a dropdown list
	 * @param $registryKey
	 */
	protected function displaySavedFilters($registryKey)
	{

		if ($this->jsonStorage->getLength($this->registry))
		{
			$registry = $this->jsonStorage->getRegistry($this->registry);

			echo '<p><span class="span6" >';
			echo '<label class="label label-info">Saved Filters [select and click ok sign button]</label><br/>';
			echo '<select class="select-extended-filter">';
			echo '<option value="-1" data-filter="{}" '.(!$registryKey?'selected':'').'>No Filters</option>';
			foreach ($registry as $key=>$filter)
			{
				echo CHtml::openTag('option', array('data-filter'=>CJSON::encode($filter['options']), 'data-key'=>$key, 'selected'=>($key==$registryKey?'selected':null)));
				echo $filter['name'];
				echo '</option>';
			}
			echo '</select>&nbsp;';

			echo CHtml::link('<i class="icon-ok icon-white"></i>', '#', array('class'=>'btn btn-primary btn-extended-filter-apply', 'style'=>'margin-bottom:9px'));
			echo '&nbsp;';
			echo CHtml::link('<i class="icon-trash"></i>', '#', array('class'=>'btn btn-warning btn-extended-filter-delete', 'style'=>'margin-bottom:9px'));
			echo '</span></p>';
		}

	}

	/**
	 * Generates a registry item key with the filtered attributes + the grid id
	 * @return null|string
	 */
	protected function generateRegistryItemKey()
	{
		if (!count($this->filteredBy))
			return null;


		return md5($this->grid->id . CJSON::encode($this->filteredBy));
	}

	/**
	 * Displays the filtered options
	 * @param $filteredBy
	 * @return string
	 */
	protected function displayExtendedFilterValues($filteredBy)
	{
		$values = array();
		foreach ($filteredBy as $key => $value)
			$values[] = '<span class="label label-info">' . $key . '</span> ' . $value;
		return implode(', ', $values);
	}
}