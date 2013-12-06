<?php
/**
 * TbCheckBoxColumn.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 9/27/12
 * Time: 5:14 PM
 */
Yii::import('zii.widgets.grid.CCheckBoxColumn');
Yii::import('bootstrap.widgets.TbButton');

class TbBulkActions extends CComponent
{
	/**
	 * @var CGridView the grid view object that owns this column.
	 */
	public $grid;

	/**
	 * @var array the configuration for action displays. Each array element specifies a single button
	 * which has the following format:
	 * <pre>
	 * 'actions' => array(
	 *      array(
	 *          'type'=> 'primary', // '', 'primary', 'info', 'success', 'warning', 'danger' or 'inverse'
	 *          'size'=> 'large', // '', 'large', 'small', 'mini'
	 *          'label'=>'...',     // text label of the button or dropdown label
	 *          'click'=> // the js function that will be called
	 *      )
	 * ),
	 * </pre>
	 * For more configuration options please @see TbButton
	 *
	 * Note that in order to display these additional buttons, the {@link template} property needs to
	 * be configured so that the corresponding button IDs appear as tokens in the template.
	 */
	public $actionButtons = array();

	/**
	 * @var array the checkbox column configuration
	 */
	public $checkBoxColumnConfig = array();

	/**
	 * @var string
	 */
	public $align = 'right';

	/**
	 * @var integer the counter for generating implicit IDs.
	 */
	private static $_counter = 0;
	/**
	 * @var string id of the widget.
	 */
	private $_id;

	/**
	 * Returns the ID of the widget or generates a new one if requested.
	 * @param boolean $autoGenerate whether to generate an ID if it is not set previously
	 * @return string id of the widget.
	 */
	public function getId($autoGenerate = true)
	{
		if ($this->_id !== null)
			return $this->_id;
		else if ($autoGenerate)
			return $this->_id = 'egw' . self::$_counter++;
	}

	/**
	 * @var string the column name of the checkbox column
	 */
	protected $columnName;

	/**
	 * @var array the bulk action buttons
	 */
	protected $buttons = array();

	/**
	 * @var array the life events to attach the buttons to
	 */
	protected $events = array();

	/**
	 * Constructor.
	 * @param CGridView $grid the grid view that owns this column.
	 */
	public function __construct($grid)
	{
		$this->grid = $grid;
	}

	/**
	 * Component's initialization method
	 */
	public function init()
	{

		$this->align = $this->align == 'left' ? 'pull-left' : 'pull-right';

		$this->initColumn();
		$this->initButtons();
	}

	/**
	 * @return bool checks whether they are
	 */
	public function initColumn()
	{
		if (!is_array($this->checkBoxColumnConfig))
			$this->checkBoxColumnConfig = array();

		if (empty($this->grid->columns))
		{
			return false;
		}

		$columns = $this->grid->columns;

		foreach ($columns as $idx => $column)
		{
			if (!is_array($column) || !isset($column['class']))
			{
				continue;
			}
			if (preg_match('/ccheckboxcolumn/i', $column['class']))
			{
				if (isset($column['checkBoxHtmlOptions']) && isset($column['checkBoxHtmlOptions']['name']))
					$this->columnName = strtr($column['checkBoxHtmlOptions']['name'], array('[' => "\\[", ']' => "\\]"));
				else
					$this->columnName = $this->grid->id . '_c' . $idx . '\[\]';
				return true; // it has already a CCheckBoxColumn
			}
		}
		// not CCheckBoxColumn, attach one
		$this->attachCheckBoxColumn();
		return true;
	}

	/**
	 * @return bool initializes the buttons to be render
	 */
	public function initButtons()
	{
		if (empty($this->columnName) || empty($this->actionButtons))
		{
			return false;
		}

		foreach ($this->actionButtons as $action)
		{
			// button configuration is a regular TbButton
			$this->buttons[] = array(
				'class' => 'bootstrap.widgets.TbButton',
				'buttonType' => isset($action['buttonType']) ? $action['buttonType'] : TbButton::BUTTON_LINK,
				'type' => isset($action['type']) ? $action['type'] : '',
				'size' => isset($action['size']) ? $action['size'] : TbButton::SIZE_SMALL,
				'icon' => isset($action['icon']) ? $action['icon'] : null,
				'label' => isset($action['label']) ? $action['label'] : null,
				'url' => isset($action['url']) ? $action['url'] : null,
				'active' => isset($action['active']) ? $action['active'] : false,
				'items' => isset($action['items']) ? $action['items'] : array(),
				'ajaxOptions' => isset($action['ajaxOptions']) ? $action['ajaxOptions'] : array(),
				'htmlOptions' => isset($action['htmlOptions']) ? $action['htmlOptions'] : array(),
				'encodeLabel' => isset($action['encodeLabel']) ? $action['encodeLabel'] : true,
				'click' => isset($action['click']) ? $action['click'] : false
			);
		}
	}

	/**
	 * @return bool renders all initialized buttons
	 */
	public function renderButtons()
	{
		if ($this->buttons === array())
		{
			return false;
		}
		echo CHtml::openTag('div', array('id' => $this->id, 'style' => 'position:relative', 'class' => $this->align));

		foreach ($this->buttons as $actionButton)
			$this->renderButton($actionButton);
		echo '<div style="position:absolute;top:0;left:0;height:100%;width:100%;display:block;" class="bulk-actions-blocker"></div>';
		echo '</div>';

		$this->registerClientScript();
	}

	/**
	 * Registers client script
	 */
	public function registerClientScript()
	{

		$js = <<<EOD
$(document).on("click", "#{$this->grid->id} input[type=checkbox]", function(){
	var grid = $("#{$this->grid->id}");
	if($("input[name='{$this->columnName}']:checked", grid).length)
	{

		$(".bulk-actions-btn", grid).removeClass("disabled");
		$("div.bulk-actions-blocker",grid).hide();
	}
	else
	{
		$(".bulk-actions-btn", grid).addClass("disabled");
		$("div.bulk-actions-blocker",grid).show();
	}
});
EOD;
		foreach ($this->events as $buttonId => $handler)
		{
			$js .= "\n$(document).on('click','#{$buttonId}', function(){var checked = $('input[name=\"{$this->columnName}\"]:checked');\n
			var fn = $handler; if($.isFunction(fn)){fn(checked);}\nreturn false;});\n";
		}
		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id, $js);
	}

	/**
	 * Creates a TbButton and renders it
	 * @param $actionButton the configuration to create the TbButton
	 */
	protected function renderButton($actionButton)
	{
		// create widget and display
		if (isset($actionButton['htmlOptions']['class']))
			$actionButton['htmlOptions']['class'] .= ' disabled bulk-actions-btn';
		else $actionButton['htmlOptions']['class'] = 'disabled bulk-actions-btn';

		$action = null;

		if (isset($actionButton['click']))
		{
			$action = CJavaScript::encode($actionButton['click']);
			unset($actionButton['click']);
		}

		$button = Yii::createComponent($actionButton);
		$button->init();
		echo '&nbsp;';
		$button->run();
		echo '&nbsp;';
		if ($action !== null)
		{
			$this->events[$button->id] = $action;
		}
	}

	/**
	 * Adds a checkbox column to the grid. It is called when
	 */
	protected function attachCheckBoxColumn()
	{
		$dataProvider = $this->grid->dataProvider;
		$columnName = null;

		if (!isset($this->checkBoxColumnConfig['name']))
		{
			// supports two types of DataProviders
			if ($dataProvider instanceof CActiveDataProvider)
			{
				// we need to get the name of the key field 'by default'
				if (is_string($dataProvider->modelClass))
				{
					$modelClass = $dataProvider->modelClass;
					$model = CActiveRecord::model($modelClass);
				} elseif ($dataProvider->modelClass instanceof CActiveRecord)
				{
					$model = $dataProvider->modelClass;
				}
				$table = $model->tableSchema;
				if (is_string($table->primaryKey))
					$columnName = $this->{$table->primaryKey};
				else if (is_array($table->primaryKey))
				{
					$columnName = $table->primaryKey[0]; // just get the first one
				}
			}
			if ($dataProvider instanceof CArrayDataProvider)
			{
				$columnName = $dataProvider->keyField; // key Field
			}
		}
		// create CCheckBoxColumn and attach to columns at its beginning
		$column = CMap::mergeArray(array(
			'class' => 'CCheckBoxColumn',
			'name' => $columnName,
			'selectableRows' => 2
		), $this->checkBoxColumnConfig);


		array_unshift($this->grid->columns, $column);
		$this->columnName = $this->grid->id . '_c0\[\]'; //
	}

}