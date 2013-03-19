<?php
/**
 * TbExtendedGridView class file
 *
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
Yii::import('bootstrap.widgets.TbGridView');

/**
 * TbExtendedGridView is an extended version of TbGridView.
 *
 * Features are:
 *  - Display an extended summary of the records shown. The extended summary can be configured to any of the
 *  {@link TbOperation} type of widgets.
 *  - Automatic chart display (using TbHighCharts widget), where user can 'switch' between views.
 *  - Selectable cells
 *  - Sortable rows
 */
class TbExtendedGridView extends TbGridView
{
	/**
	 * @var bool $fixedHeader if set to true will keep the header fixed  position
	 */
	public $fixedHeader = false;

	/**
	 * @var integer $headerOffset, when $fixedHeader is set to true, headerOffset will position table header top position
	 * at $headerOffset. If you are using bootstrap and has navigation top fixed, its height is 40px, so it is recommended
	 * to use $headerOffset=40;
	 */
	public $headerOffset = 0;
	/**
	 * @var string the template to be used to control the layout of various sections in the view.
	 * These tokens are recognized: {extendedSummary}, {summary}, {items} and {pager}. They will be replaced with the
	 * extended summary, summary text, the items, and the pager.
	 */
	public $template = "{summary}\n{items}\n{pager}\n{extendedSummary}";

	/**
	 * @var array $extendedSummary displays an extended summary version. There are different types of summary types,
	 * please, see {@link TbSumOperation}, {@link TbSumOfTypeOperation},{@link TbPercentOfTypeGooglePieOperation}
	 * {@link TbPercentOfTypeOperation} and {@link TbPercentOfTypeEasyPieOperation}.
	 *
	 * The following is an example, please review the different types of TbOperation classes to find out more about
	 * its configuration parameters.
	 *
	 * <pre>
	 *  'extendedSummary' => array(
	 *      'title' => '',      // the extended summary title
	 *      'columns' => array( // the 'columns' that will be displayed at the extended summary
	 *          'id' => array(  // colum name "id"
	 *              'class' => 'TbSumOperation', // what is the type of TbOperation we are going to display
	 *              'label' => 'Sum of Ids'     // label is name of label of the resulted value (ie Sum of Ids:)
	 *          ),
	 *          'results' => array(   // column name "results"
	 *              'class' => 'TbPercentOfTypeGooglePieOperation', // the type of TbOperation
	 *              'label' => 'How Many Of Each? ', // the label of the operation
	 *              'types' => array(               // TbPercentOfTypeGooglePieOperation "types" attributes
	 *                  '0' => array('label' => 'zeros'),   // a value of "0" will be labelled "zeros"
	 *                  '1' => array('label' => 'ones'),    // a value of "1" will be labelled "ones"
	 *                  '2' => array('label' => 'twos'))    // a value of "2" will be labelled "twos"
	 *          )
	 *      )
	 * ),
	 * </pre>
	 */
	public $extendedSummary = array();

	/**
	 * @var string $extendedSummaryCssClass is the class name of the layer containing the extended summary
	 */
	public $extendedSummaryCssClass = 'extended-summary';

	/**
	 * @var array $extendedSummaryOptions the HTML attributes of the layer containing the extended summary
	 */
	public $extendedSummaryOptions = array();

	/**
	 * @var array $componentsAfterAjaxUpdate has scripts that will be executed after components have updated.
	 * It is used internally to render scripts required for components to work correctly.  You may use it for your own
	 * scripts, just make sure it is of type array.
	 */
	public $componentsAfterAjaxUpdate = array();

	/**
	 * @var array $componentsReadyScripts hold scripts that will be executed on document ready.
	 * It is used internally to render scripts required for components to work correctly. You may use it for your own
	 * scripts, just make sure it is of type array.
	 */
	public $componentsReadyScripts = array();

	/**
	 * @var array $chartOptions if configured, the extended view will display a highcharts chart.
	 */
	public $chartOptions = array();

	/**
	 * @var bool $sortableRows. If true the rows at the table will be sortable.
	 */
	public $sortableRows = false;

	/**
	 * @var string Database field name for row sorting
	 */
	public $sortableAttribute = 'sort_order';

	/**
	 * @var boolean Save sort order by ajax defaults to false
	 * @see bootstrap.action.TbSortableAction for an easy way to use with your controller
	 */
	public $sortableAjaxSave = false;

	/**
	 * @var string Name of the action to call and sort values
	 * @see bootstrap.action.TbSortableAction for an easy way to use with your controller
	 *
	 * <pre>
	 *  'sortableAction'=>'module/controller/sortable' | 'controller/sortable'
	 * </pre>
	 *
	 * The widget will make use of the string to create the URL and then append $sortableAttribute
	 * @see $sortableAttribute
	 */
	public $sortableAction;

	/**
	 * @var string a javascript function that will be invoked after a successful sorting is done.
	 * The function signature is <code>function(id, position)</code> where 'id' refers to the ID of the model id key,
	 * 'position' the new position in the list.
	 */
	public $afterSortableUpdate;

	/**
	 * @var bool whether to allow selecting of cells
	 */
	public $selectableCells = false;

	/**
	 * @var string the filter to use to allow selection. For example, if you set the "htmlOptions" property of a column to have a
	 * "class" of "tobeselected", you could set this property as: "td.tobeselected" in order to allow  selection to
	 * those columns with that class only.
	 */
	public $selectableCellsFilter = 'td';

	/**
	 * @var string a javascript function that will be invoked after a selection is done.
	 * The function signature is <code>function(selected)</code> where 'selected' refers to the selected columns.
	 */
	public $afterSelectableCells;
	/**
	 * @var array the configuration options to display a TbBulkActions widget
	 * @see TbBulkActions widget for its configuration
	 */
	public $bulkActions = array();

	/**
	 * @var string the aligment of the bulk actions. It can be 'left' or 'right'.
	 */
	public $bulkActionAlign = 'right';

	/**
	 * @var TbBulkActions component that will display the bulk actions to the grid
	 */
	protected $bulk;

	/**
	 * @var boolean $displayExtendedSummary a helper property that is set to true if we have to render the
	 * extended summary
	 */
	protected $displayExtendedSummary;
	/**
	 * @var boolean $displayChart a helper property that is set to true if we have to render a chart.
	 */
	protected $displayChart;

	/**
	 * @var array $extendedSummaryTypes hold the current configured TbOperation that will process column values.
	 */
	protected $extendedSummaryTypes = array();

	/**
	 * @var array $extendedSummaryOperations hold the supported operation types
	 */
	protected $extendedSummaryOperations = array(
		'TbSumOperation',
		'TbCountOfTypeOperation',
		'TbPercentOfTypeOperation',
		'TbPercentOfTypeEasyPieOperation',
		'TbPercentOfTypeGooglePieOperation'
	);

	/**
	 * Widget initialization
	 */
	public function init()
	{

		if (preg_match('/extendedsummary/i', $this->template) && !empty($this->extendedSummary) && isset($this->extendedSummary['columns']))
		{
			$this->template .= "\n{extendedSummaryContent}";
			$this->displayExtendedSummary = true;
		}
		if (!empty($this->chartOptions) && @$this->chartOptions['data'] && $this->dataProvider->getItemCount())
		{
			$this->displayChart = true;
		}
		if ($this->bulkActions !== array() && isset($this->bulkActions['actionButtons']))
		{
			if(!isset($this->bulkActions['class']))
				$this->bulkActions['class'] = 'bootstrap.widgets.TbBulkActions';

			$this->bulk = Yii::createComponent($this->bulkActions, $this);
			$this->bulk->init();
		}
		parent::init();
	}

	/**
	 * Renders grid content
	 */
	public function renderContent()
	{
		parent::renderContent();
		$this->registerCustomClientScript();
	}

	/**
	 * Renders the key values of the data in a hidden tag.
	 */
	public function renderKeys()
	{
		$data = $this->dataProvider->getData();
		if (empty($data))
		{
			return false;
		}

		if(!$this->sortableRows || !$this->getAttribute($data[0], $this->sortableAttribute))
		{
			return parent::renderKeys();
		}

		echo CHtml::openTag('div',array(
			'class'=>'keys',
			'style'=>'display:none',
			'title'=>Yii::app()->getRequest()->getUrl(),
		));
		foreach($data as $d)
   			echo CHtml::tag('span',array('data-order' => $this->getAttribute($d, $this->sortableAttribute), ), CHtml::encode($this->getPrimaryKey($d)));
		echo "</div>\n";
	}

	/**
	 * Helper function to get an attribute from the data
	 *
	 * @param $data
	 * @param $attribute the attribute to get
	 * @return mixed the attribute value null if none found
	 */
	protected function getAttribute($data, $attribute)
	{
		if($this->dataProvider instanceof CActiveDataProvider && $data->hasAttribute($attribute))
		{
			return $data->{$attribute};
		}
		if($this->dataProvider instanceof CArrayDataProvider)
		{
			if (is_object($data) && isset($data->{$attribute}))
			{
				return $data->{$attribute};
			}
			if (isset($data[$attribute]))
			{
				return $data[$attribute];
			}
		}
		return null;
	}
	/**
	 * Helper function to return the primary key of the $data
	 * IMPORTANT: composite keys on CActiveDataProviders will return the keys joined by comma
	 *
	 * @param $data
	 * @return null|string
	 */
	protected function getPrimaryKey($data)
	{
		if($this->dataProvider instanceof CActiveDataProvider)
		{
			$key=$this->dataProvider->keyAttribute===null ? $data->getPrimaryKey() : $data->{$this->keyAttribute};
			return is_array($key) ? implode(',',$key) : $key;
		}
		if($this->dataProvider instanceof CArrayDataProvider)
		{
			return is_object($data) ? $data->{$this->dataProvider->keyField} : $data[$this->dataProvider->keyField];
		}
		return null;
	}

	/**
	 * Renders grid header
	 */
	public function renderTableHeader()
	{
		$this->renderChart();
		parent::renderTableHeader();
	}

	/**
	 * Renders the table footer.
	 */
	public function renderTableFooter()
	{
		$hasFilter = $this->filter !== null && $this->filterPosition === self::FILTER_POS_FOOTER;

		$hasFooter = $this->getHasFooter();
		if ($this->bulk !== null || $hasFilter || $hasFooter)
		{
			echo "<tfoot>\n";
			if ($hasFooter)
			{
				echo "<tr>\n";
				foreach ($this->columns as $column)
					$column->renderFooterCell();
				echo "</tr>\n";
			}
			if ($hasFilter)
				$this->renderFilter();

			if ($this->bulk !== null)
				$this->renderBulkActions();
			echo "</tfoot>\n";
		}
	}

	public function renderBulkActions()
	{
		echo '<tr><td colspan="' . count($this->columns) . '">';
		$this->bulk->renderButtons();
		echo '</td></tr>';
	}


	/**
	 * Renders chart
	 * @throws CException
	 */
	public function renderChart()
	{
		if (!$this->displayChart || $this->dataProvider->getItemCount() <= 0)
			return;

		if (!isset($this->chartOptions['data']['series']))
			throw new CException(Yii::t('zii', 'You need to set the "series" attribute in order to render a chart'));

		$configSeries = $this->chartOptions['data']['series'];
		if (!is_array($configSeries))
			throw new CException(Yii::t('zii', '"chartOptions.series" is expected to be an array.'));

		$chartId = 'exgvwChart' . $this->getId();

		if (!isset($this->chartOptions['config']))
			$this->chartOptions['config'] = array();

		// ****************************************
		// render switch buttons
		$buttons = Yii::createComponent(array('class' => 'bootstrap.widgets.TbButtonGroup',
			'toggle' => 'radio',
			'buttons' => array(
				array('label' => Yii::t('zii', 'Grid'), 'url' => '#', 'htmlOptions' => array('class' => 'active ' . $this->getId() . '-grid-control grid')),
				array('label' => Yii::t('zii', 'Chart'), 'url' => '#', 'htmlOptions' => array('class' => $this->getId() . '-grid-control chart')),
			),
			'htmlOptions' => array('style' => 'margin-bottom:5px')
		));
		echo '<div span="row">';
		$buttons->init();
		$buttons->run();
		echo '</div>';

		$this->componentsReadyScripts[] = '$(document).on("click",".' . $this->getId() . '-grid-control", function(){
			if($(this).hasClass("grid") && $("#' . $this->getId() . ' #' . $chartId . '").is(":visible"))
			{
				$("#' . $this->getId() . ' #' . $chartId . '").hide();
				$("#' . $this->getId() . ' table.items").show();
			}
			if($(this).hasClass("chart") && $("#' . $this->getId() . ' table.items").is(":visible"))
			{
				$("#' . $this->getId() . ' table.items").hide();
				$("#' . $this->getId() . ' #' . $chartId . '").show();
			}
			return false;
		});';
		// end switch buttons
		// ****************************************

		// render Chart
		// chart options
		$data = $this->dataProvider->getData();
		$count = count($data);
		$seriesData = array();
		$cnt = 0;
		foreach ($configSeries as $set)
		{
			$seriesData[$cnt] = array('name' => isset($set['name']) ? $set['name'] : null, 'data' => array());

			for ($row = 0; $row < $count; ++$row)
			{
				$column = $this->getColumnByName($set['attribute']);
				if (!is_null($column) && $column->value !== null)
				{
					$seriesData[$cnt]['data'][] = $this->evaluateExpression($column->value, array('data' => $data[$row], 'row' => $row));
				} else
				{
					$value = CHtml::value($data[$row], $set['attribute']);
					$seriesData[$cnt]['data'][] = is_numeric($value) ? (float)$value : $value;
				}

			}
			++$cnt;
		}

		// ****************************************
		// render chart
		$options = CMap::mergeArray(
			$this->chartOptions['config'],
			array('series' => $seriesData)
		);
		$this->chartOptions['htmlOptions'] = isset($this->chartOptions['htmlOptions']) ? $this->chartOptions['htmlOptions'] : array();
		$this->chartOptions['htmlOptions']['style'] = 'display:none'; // sorry but use a class to provide styles, we need this
		// build unique ID
		// important!
		echo '<div span="row">';
		if ($this->ajaxUpdate !== false)
		{
			if (isset($options['chart']) && is_array($options['chart']))
			{
				$options['chart']['renderTo'] = $chartId;
			} else
			{
				$options['chart'] = array('renderTo' => $chartId);
			}
			$jsOptions = CJSON::encode($options);

			if (isset($this->chartOptions['htmlOptions']['data-config']))
				unset($this->chartOptions['htmlOptions']['data-config']);

			echo "<div id='{$chartId}' " . CHtml::renderAttributes($this->chartOptions['htmlOptions']) . " data-config='{$jsOptions}'></div>";

			$this->componentsAfterAjaxUpdate[] = "highchart{$chartId} = new Highcharts.Chart($('#{$chartId}').data('config'));";
		}
		$configChart = array(
			'class' => 'bootstrap.widgets.TbHighCharts',
			'id' => $chartId,
			'options' => $options,
			'htmlOptions' => $this->chartOptions['htmlOptions']
		);
		$chart = Yii::createComponent($configChart);
		$chart->init();
		$chart->run();
		echo '</div>';
		// end chart display
		// ****************************************


	}


	/**
	 * Renders a table body row.
	 * @param integer $row the row number (zero-based).
	 */
	public function renderTableRow($row)
	{
		if ($this->rowCssClassExpression !== null)
		{
			$data = $this->dataProvider->data[$row];
			echo '<tr class="' . $this->evaluateExpression($this->rowCssClassExpression, array('row' => $row, 'data' => $data)) . '">';
		} else if (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0)
			echo '<tr class="' . $this->rowCssClass[$row % $n] . '">';
		else
			echo '<tr>';
		foreach ($this->columns as $column)
		{
			echo $this->displayExtendedSummary && !empty($this->extendedSummary['columns']) ? $this->parseColumnValue($column, $row) : $column->renderDataCell($row);
		}
		echo "</tr>\n";
	}

	/**
	 * Renders summary
	 */
	public function renderExtendedSummary()
	{
		if (!isset($this->extendedSummaryOptions['class']))
			$this->extendedSummaryOptions['class'] = $this->extendedSummaryCssClass;
		else
			$this->extendedSummaryOptions['class'] .= ' ' . $this->extendedSummaryCssClass;

		echo '<div ' . CHtml::renderAttributes($this->extendedSummaryOptions) . '></div>';
	}

	/**
	 * Renders summary content. Will be appended to
	 */
	public function renderExtendedSummaryContent()
	{
		if (($count = $this->dataProvider->getItemCount()) <= 0)
			return;

		if (!empty($this->extendedSummaryTypes))
		{
			echo '<div id="' . $this->id . '-extended-summary" style="display:none">';
			if (isset($this->extendedSummary['title']))
			{
				echo '<h3>' . $this->extendedSummary['title'] . '</h3>';
			}
			foreach ($this->extendedSummaryTypes as $summaryType)
			{
				$summaryType->run();
				echo '<br/>';
			}
			echo '</div>';
		}
	}

	/**
	 * This script must be run at the end of content rendering not at the beginning as it is common with normal CGridViews
	 */
	public function registerCustomClientScript()
	{

		$cs = Yii::app()->getClientScript();

		$fixedHeaderJs = '';
		if ($this->fixedHeader)
		{
			Yii::app()->bootstrap->registerAssetJs('jquery.stickytableheaders.js');
			$fixedHeaderJs = "$('#{$this->id} table.items').stickyTableHeaders({fixedOffset:{$this->headerOffset}});";
			$this->componentsAfterAjaxUpdate[] = $fixedHeaderJs;
		}

		if ($this->sortableRows)
		{
			if ($this->afterSortableUpdate !== null)
			{
				if (!($this->afterSortableUpdate instanceof CJavaScriptExpression) && strpos($this->afterSortableUpdate, 'js:') !== 0)
				{
					$afterSortableUpdate = new CJavaScriptExpression($this->afterSortableUpdate);
				} else
				{
					$afterSortableUpdate = $this->afterSortableUpdate;
				}
			}

			$this->selectableRows = 1;
			$cs->registerCoreScript('jquery.ui');
			Yii::app()->bootstrap->registerAssetJs('jquery.sortable.gridview.js');

			if($this->sortableAjaxSave && $this->sortableAction !== null)
			{
				$sortableAction = Yii::app()->createUrl($this->sortableAction, array('sortableAttribute' => $this->sortableAttribute));
			}
			else
				$sortableAction = '';

			$afterSortableUpdate = CJavaScript::encode($afterSortableUpdate);
			$this->componentsReadyScripts[] = "$.fn.yiiGridView.sortable('{$this->id}', '{$sortableAction}', {$afterSortableUpdate});";
			$this->componentsAfterAjaxUpdate[] = "$.fn.yiiGridView.sortable('{$this->id}', '{$sortableAction}', {$afterSortableUpdate});";
		}

		if($this->selectableCells)
		{
			if($this->afterSelectableCells !== null)
			{
				echo strpos($this->afterSelectableCells, 'js:');
				if (!($this->afterSelectableCells instanceof CJavaScriptExpression) && strpos($this->afterSelectableCells, 'js:') !== 0)
				{
					$afterSelectableCells = new CJavaScriptExpression($this->afterSelectableCells);
				} else
				{
					$afterSelectableCells = $this->afterSelectableCells;
				}
			}
			$cs->registerCoreScript('jquery.ui');
			Yii::app()->bootstrap->registerAssetJs('jquery.selectable.gridview.js');
			$afterSelectableCells = CJavaScript::encode($afterSelectableCells);
			$this->componentsReadyScripts[] = "$.fn.yiiGridView.selectable('{$this->id}','{$this->selectableCellsFilter}',{$afterSelectableCells});";
			$this->componentsAfterAjaxUpdate[] = "$.fn.yiiGridView.selectable('{$this->id}','{$this->selectableCellsFilter}', {$afterSelectableCells});";
		}

		$cs->registerScript(__CLASS__ . '#' . $this->id . 'Ex', '
			$grid = $("#' . $this->id . '");
			' . $fixedHeaderJs . '
			if($(".' . $this->extendedSummaryCssClass . '", $grid).length)
			{
				$(".' . $this->extendedSummaryCssClass . '", $grid).html($("#' . $this->id . '-extended-summary", $grid).html());
			}
			' . (count($this->componentsReadyScripts) ? implode(PHP_EOL, $this->componentsReadyScripts) : '') . '
			$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
				var qs = $.deparam.querystring(options.url);
				if(qs.hasOwnProperty("ajax") && qs.ajax == "' . $this->id . '")
				{
					options.realsuccess = options.success;
					options.success = function(data)
					{
						if(options.realsuccess) {
							options.realsuccess(data);
							var $data = $("<div>" + data + "</div>");
							// we need to get the grid again... as it has been updated
							if($(".' . $this->extendedSummaryCssClass . '", $("#' . $this->id . '")))
							{
								$(".' . $this->extendedSummaryCssClass . '", $("#' . $this->id . '")).html($("#' . $this->id . '-extended-summary", $data).html());
							}
							' . (count($this->componentsAfterAjaxUpdate) ? implode(PHP_EOL, $this->componentsAfterAjaxUpdate) : '') . '
						}
					}
				}
			});');
	}

	/**
	 * @param CDataColumn $column
	 * @param $row the current row  numbeer
	 */
	protected function parseColumnValue($column, $row)
	{
		ob_start();
		$column->renderDataCell($row);
		$value = ob_get_clean();

		if ($column instanceof CDataColumn && array_key_exists($column->name, $this->extendedSummary['columns']))
		{
			// lets get the configuration
			$config = $this->extendedSummary['columns'][$column->name];
			// add the required column object in
			$config['column'] = $column;
			// build the summary operation object
			$op = $this->getSummaryOperationInstance($column->name, $config);
			// process the value
			$op->processValue($value);
		}
		return $value;
	}

	/**
	 * Each type of 'extended' summary
	 * @param $name the name of the column
	 * @param $config the configuration of the column at the extendedSummary
	 * @return mixed
	 * @throws CException
	 */
	protected function getSummaryOperationInstance($name, $config)
	{
		if (!isset($config['class']))
			throw new CException(Yii::t('zii', 'Column summary configuration must be an array containing a "type" element.'));

		if (!in_array($config['class'], $this->extendedSummaryOperations))
			throw new CException(Yii::t('zii', '"{operation}" is an unsupported class operation.', array('{operation}' => $config['class'])));

		// name of the column should be unique
		if (!isset($this->extendedSummaryTypes[$name]))
		{
			$this->extendedSummaryTypes[$name] = Yii::createComponent($config);
			$this->extendedSummaryTypes[$name]->init();
		}
		return $this->extendedSummaryTypes[$name];
	}

	/**
	 * Helper function to get a column by its name
	 * @param $name
	 * @return null
	 */
	protected function getColumnByName($name)
	{
		foreach ($this->columns as $column)
		{
			if (strcmp($column->name, $name) === 0)
				return $column;
		}
		return null;
	}

}

/**
 * TbOperation class
 *
 * Abstract class where all types of operations extend from
 */
abstract class TbOperation extends CWidget
{
	/**
	 * @var string $template the template to display label and value of the operation at the summary
	 */
	public $template = '{label}: {value}';

	/**
	 * @var int $value the resulted value of operation
	 */
	public $value = 0;

	/**
	 * @var string $label the label of the calculated value
	 */
	public $label;

	/**
	 * @var TbDataColumn $column
	 */
	public $column;

	/**
	 * Widget initialization
	 * @throws CException
	 */
	public function init()
	{
		if (null == $this->column)
			throw new CException(Yii::t('zii', '"{attribute}" attribute must be defined', array('{attribute}' => 'column')));
	}

	/**
	 * Widget's run method
	 */
	public function run()
	{
		$this->displaySummary();
	}

	/**
	 * Process the row data value
	 * @param $value
	 * @return mixed
	 */
	abstract public function processValue($value);

	/**
	 * Displays the resulting summary
	 * @return mixed
	 */
	abstract public function displaySummary();

}

/**
 * TbSumOperation class
 *
 * Displays a total of specified column name.
 *
 */
class TbSumOperation extends TbOperation
{
	/**
	 * @var float $total the total sum
	 */
	protected $total;

	/**
	 * @var array $supportedTypes the supported type of values
	 */
	protected $supportedTypes = array('raw', 'text', 'ntext', 'number');

	/**
	 * Widget's initialization method
	 * @throws CException
	 */
	public function init()
	{
		parent::init();

		if (!in_array($this->column->type, $this->supportedTypes))
		{
			throw new CException(Yii::t('zii', 'Unsupported column type. Supported column types are: "{types}"', array(
				'{types}' => implode(', ', $this->supportedTypes))));
		}
	}

	/**
	 * Extracts the digital part of the calculated value.
	 * @param $value
	 * @return bool
	 */
	protected function extractNumber($value)
	{
		preg_match_all('/([0-9]+[,\.]?)+/', $value, $matches);
		return !empty($matches[0]) && @$matches[0][0] ? $matches[0][0] : 0;
	}

	/**
	 * Process the value to calculate
	 * @param $value
	 * @return mixed|void
	 */
	public function processValue($value)
	{

		// remove html tags as we cannot access renderDataCellContent from the column
		$clean = strip_tags($value);
		$this->total += ((float)$this->extractNumber($clean));
	}

	/**
	 * Displays the summary
	 * @return mixed|void
	 */
	public function displaySummary()
	{
		echo strtr($this->template, array('{label}' => $this->label, '{value}' => $this->total === null ? '' : Yii::app()->format->format($this->total, $this->column->type)));
	}
}

/**
 * TbCountOfTypeOperation class
 *
 * Renders a summary based on the count of specified types. For example, if a value has a type 'blue', this class will
 * count the number of times the value 'blue' has on that column.
 */
class TbCountOfTypeOperation extends TbOperation
{
	/**
	 * @var string $template
	 * @see parent class
	 */
	public $template = '{label}: {types}';

	/**
	 * @var string $typeTemplate holds the template of each calculated type
	 */
	public $typeTemplate = '{label}({value})';

	/**
	 * @var array $types hold the configuration of types to calculate. The configuration is set by an array which keys
	 * are the value types to count. You can set their 'label' independently.
	 *
	 * <pre>
	 *  'types' => array(
	 *      '0' => array('label' => 'zeros'),
	 *      '1' => array('label' => 'ones'),
	 *      '2' => array('label' => 'twos')
	 * </pre>
	 */
	public $types = array();

	/**
	 * Widget's initialization
	 * @throws CException
	 */
	public function init()
	{
		if (empty($this->types))
			throw new CException(Yii::t('zii', '"{attribute}" attribute must be defined', array('{attribute}' => 'types')));
		foreach ($this->types as $type)
		{
			if (!isset($type['label']))
				throw new CException(Yii::t('zii', 'The "label" of a type must be defined.'));
		}
		parent::init();
	}

	/**
	 * (no phpDoc)
	 * @see TbOperation
	 * @param $value
	 * @return mixed|void
	 */
	public function processValue($value)
	{
		$clean = strip_tags($value);

		if (array_key_exists($clean, $this->types))
		{
			if (!isset($this->types[$clean]['value']))
				$this->types[$clean]['value'] = 0;
			$this->types[$clean]['value'] += 1;
		}
	}

	/**
	 * (no phpDoc)
	 * @see TbOperation
	 * @return mixed|void
	 */
	public function displaySummary()
	{
		$typesResults = array();
		foreach ($this->types as $type)
		{
			if (!isset($type['value']))
			{
				$type['value'] = 0;
			}
			$typesResults[] = strtr($this->typeTemplate, array('{label}' => $type['label'], '{value}' => $type['value']));

		}
		echo strtr($this->template, array('{label}' => $this->label, '{types}' => implode(' ', $typesResults)));
	}
}

/**
 * TbPercentOfTypeOperation class
 * Renders a summary based on the percent count of specified types. For example, if a value has a type 'blue', this class will
 * count the percentage number of times the value 'blue' has on that column.
 */
class TbPercentOfTypeOperation extends TbCountOfTypeOperation
{
	/**
	 * @var string $typeTemplate
	 * @see TbCountOfTypeOperation
	 */
	public $typeTemplate = '{label}({value}%)';

	/**
	 * @var $_total holds the total sum of the values. Required to get the percentage.
	 */
	protected $_total;

	/**
	 * @return mixed|void
	 * @see TbOperation
	 */
	public function displaySummary()
	{
		$typesResults = array();

		foreach ($this->types as $type)
		{
			if (!isset($type['value']))
			{
				$type['value'] = 0;
			}
			$type['value'] = $this->getTotal() ? number_format((float)($type['value'] / $this->getTotal()) * 100, 1) : 0;
			$typesResults[] = strtr($this->typeTemplate, array('{label}' => $type['label'], '{value}' => $type['value']));
		}

		echo strtr($this->template, array('{label}' => $this->label, '{types}' => implode(' ', $typesResults)));
	}

	/**
	 * Returns the total of types
	 * @return holds|int
	 */
	protected function getTotal()
	{
		if (null == $this->_total)
		{
			$this->_total = 0;
			foreach ($this->types as $type)
			{
				if (isset($type['value']))
				{
					$this->_total += $type['value'];
				}
			}
		}
		return $this->_total;
	}
}

/**
 * TbPercentOfTypeGooglePieOperation class
 *
 * Displays a Google visualization  pie chart based on the percentage count of type.
 */
class TbPercentOfTypeGooglePieOperation extends TbPercentOfTypeOperation
{
	/**
	 * @var string $chartCssClass the class name of the layer holding the chart
	 */
	public $chartCssClass = 'bootstrap-operation-google-pie-chart';

	/**
	 * The options
	 * @var array $chartOptions
	 * @see https://google-developers.appspot.com/chart/interactive/docs/gallery/piechart
	 */
	public $chartOptions = array(
		'title' => 'Google Pie Chart'
	);

	/**
	 * @var array $data the configuration data of the chart
	 */
	protected $data = array();

	/**
	 * @see TbOperation
	 * @return mixed|void
	 */
	public function displaySummary()
	{
		$this->data[] = array('Label', 'Percent');

		foreach ($this->types as $type)
		{
			if (!isset($type['value']))
			{
				$type['value'] = 0;
			}
			$this->data[] = $this->getTotal() ? array($type['label'], (float)number_format(($type['value'] / $this->getTotal()) * 100, 1)) : 0;
		}
		$data = CJavaScript::jsonEncode($this->data);
		$options = CJavaScript::jsonEncode($this->chartOptions);
		echo "<div id='{$this->id}' class='{$this->chartCssClass}' data-data='{$data}' data-options='{$options}'></div>";

		$this->registerClientScript();
	}

	/**
	 * Registers required scripts
	 */
	public function registerClientScript()
	{
		$chart = Yii::createComponent(array('class' => 'bootstrap.widgets.TbGoogleVisualizationChart',
			'visualization' => 'PieChart',
			'containerId' => $this->getId(),
			'data' => $this->data,
			'options' => $this->chartOptions
		));
		$chart->init();
		$chart->run();

		/**
		 * create custom chart update by using the global chart variable
		 * @see TbGoogleVisualizationChart
		 */
		$this->column->grid->componentsAfterAjaxUpdate[__CLASS__] =
			'var $el = $("#' . $this->getId() . '");var data = $el.data("data");var opts = $el.data("options");
			data = google.visualization.arrayToDataTable(data);
			' . $chart->getId() . '=new google.visualization.PieChart(document.getElementById("' . $this->getId() . '"));
			' . $chart->getId() . '.draw(data,opts);';
	}

}

/**
 * TbPercentOfTypeEasyPieOperation class
 *
 * Displays an chart based on jquery.easy.pie plugin
 */
class TbPercentOfTypeEasyPieOperation extends TbPercentOfTypeOperation
{
	/**
	 * @var string $chartCssClass the class of the layer containing the class
	 */
	public $chartCssClass = 'bootstrap-operation-easy-pie-chart';

	/**
	 * @var string $template
	 * @see TbOperation
	 */
	public $template = '<div style="clear:both">{label}: </div>{types}';

	/**
	 * @var string $typeTemplate
	 * @see parent class
	 */
	public $typeTemplate = '<div style="float:left;text-align:center;margin:2px"><div class="{class}" data-percent="{value}">{value}%</div><div>{label}</div></div>';

	// easy-pie-chart plugin options
	// @see https://github.com/rendro/easy-pie-chart#configuration-parameter
	public $chartOptions = array(
		'barColor' => '#ef1e25', // The color of the curcular bar. You can pass either a css valid color string like rgb,
		// rgba hex or string colors. But you can also pass a function that accepts the current
		// percentage as a value to return a dynamically generated color.
		'trackColor' => '#f2f2f2', // The color of the track for the bar, false to disable rendering.
		'scaleColor' => '#dfe0e0', // The color of the scale lines, false to disable rendering.
		'lineCap' => 'round', // Defines how the ending of the bar line looks like. Possible values are: butt, round and square.
		'lineWidth' => 5, // Width of the bar line in px.
		'size' => 80, // Size of the pie chart in px. It will always be a square.
		'animate' => false, // Time in milliseconds for a eased animation of the bar growing, or false to deactivate.
		'onStart' => 'js:$.noop', // Callback function that is called at the start of any animation (only if animate is not false).
		'onStop' => 'js:$.noop' // Callback function that is called at the end of any animation (only if animate is not false).
	);

	/**
	 * @see TbOperation
	 * @return mixed|void
	 */
	public function displaySummary()
	{
		$this->typeTemplate = strtr($this->typeTemplate, array('{class}' => $this->chartCssClass));

		parent::displaySummary();
		$this->registerClientScripts();
	}

	/**
	 * Register required scripts
	 */
	protected function registerClientScripts()
	{
		Yii::app()->bootstrap->registerAssetCss('easy-pie-chart.css');
		Yii::app()->bootstrap->registerAssetJs('jquery.easy.pie.chart.js');


		$options = CJavaScript::encode($this->chartOptions);
		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#percent-of-type-operation-simple-pie', '
			$("#' . $this->column->grid->id . ' .' . $this->column->grid->extendedSummaryCssClass . ' .' . $this->chartCssClass . '")
				.easyPieChart(' . $options . ');
		');
		$this->column->grid->componentsReadyScripts[__CLASS__] =
		$this->column->grid->componentsAfterAjaxUpdate[__CLASS__] =
			'$("#' . $this->column->grid->id . ' .' . $this->column->grid->extendedSummaryCssClass . ' .' . $this->chartCssClass . '")
				.easyPieChart(' . $options . ');';
	}
}