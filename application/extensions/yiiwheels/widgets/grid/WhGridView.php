<?php
/**
 * WhGridView class
 *
 * This grid is an extended version of TbGridView.
 *
 * Features are:
 *  - Display an extended summary of the records shown. The extended summary can be configured to any of the
 *  WhOperation type of widgets.
 *  - Automatic chart display (using WhHighCharts widget), where user can 'switch' between views.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.grid
 * @uses Yiistrap.widgets.TbHtml
 * @uses Yiistrap.widgets.TbGridView
 */
Yii::import('yiistrap_fork.helpers.TbHtml');
Yii::import('yiistrap_fork.widgets.TbGridView');

class WhGridView extends TbGridView
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
	 * @var array $extendedSummary displays an extended summary version.
	 * There are different types of summary types,
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
	 *          'id' => array(  // column name "id"
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
	 * @var bool whether to make the grid responsive
	 */
	public $responsiveTable = false;

	/**
	 * @var boolean $displayExtendedSummary a helper property that is set to true if we have to render the
	 * extended summary
	 */
	protected $displayExtendedSummary;

	/**
	 * @var WhOperation[] $extendedSummaryTypes hold the current configured TbOperation that will process column values.
	 */
	protected $extendedSummaryTypes = array();

	/**
	 * @var array $extendedSummaryOperations hold the supported operation types
	 */
	protected $extendedSummaryOperations = array(
		'yiiwheels.widgets.grid.operations.WhSumOperation',
		'yiiwheels.widgets.grid.operations.WhCountOfTypeOperation',
		'yiiwheels.widgets.grid.operations.WhPercentOfTypeOperation',
		'yiiwheels.widgets.grid.operations.WhPercentOfTypeEasyPieOperation',
		'yiiwheels.widgets.grid.operations.WhPercentOfTypeGooglePieOperation'
	);

	/**
	 * Widget initialization
	 */
	public function init()
	{
		if (preg_match(
				'/extendedsummary/i',
				$this->template
			) && !empty($this->extendedSummary) && isset($this->extendedSummary['columns'])
		) {
			$this->template .= "\n{extendedSummaryContent}";
			$this->displayExtendedSummary = true;
		}

		$this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));
		$this->attachBehavior('ywchart', array('class' => 'yiiwheels.widgets.grid.behaviors.WhChart'));

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
	 * Helper function to get an attribute from the data
	 * @param CActiveRecord $data
	 * @param string $attribute the attribute to get
	 * @return mixed the attribute value null if none found
	 */
	protected function getAttribute($data, $attribute)
	{
		if ($this->dataProvider instanceof CActiveDataProvider && $data->hasAttribute($attribute)) {
			return $data->{$attribute};
		}

		if ($this->dataProvider instanceof CArrayDataProvider || $this->dataProvider instanceof CSqlDataProvider) {
			if (is_object($data) && isset($data->{$attribute})) {
				return $data->{$attribute};
			}
			if (isset($data[$attribute])) {
				return $data[$attribute];
			}
		}
		return null;
	}

	/**
	 * Helper function to return the primary key of the $data
	 * IMPORTANT: composite keys on CActiveDataProviders will return the keys joined by comma
	 * @param CActiveRecord $data
	 * @return null|string
	 */
	protected function getPrimaryKey($data)
	{
		if ($this->dataProvider instanceof CActiveDataProvider) {
			$key = $this->dataProvider->keyAttribute === null ? $data->getPrimaryKey() : $data->{$this->dataProvider->keyAttribute};
			return is_array($key) ? implode(',', $key) : $key;
		}
		if ($this->dataProvider instanceof CArrayDataProvider || $this->dataProvider instanceof CSqlDataProvider) {
			return is_object($data) ? $data->{$this->dataProvider->keyField}
				: $data[$this->dataProvider->keyField];
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
		if ($hasFilter || $hasFooter) {
			echo "<tfoot>\n";
			if ($hasFooter) {
				echo "<tr>\n";
				/** @var $column CDataColumn */
				foreach ($this->columns as $column) {
					$column->renderFooterCell();
				}
				echo "</tr>\n";
			}
			if ($hasFilter) {
				$this->renderFilter();
			}
			echo "</tfoot>\n";
		}
	}

	/**
	 * Renders a table body row.
	 * @param integer $row the row number (zero-based).
	 */
	public function renderTableRow($row)
	{
		$htmlOptions = array();
		if ($this->rowHtmlOptionsExpression !== null) {
			$data = $this->dataProvider->data[$row];
			$options = $this->evaluateExpression(
				$this->rowHtmlOptionsExpression,
				array('row' => $row, 'data' => $data)
			);
			if (is_array($options)) {
				$htmlOptions = $options;
			}
		}

		if ($this->rowCssClassExpression !== null) {
			$data = $this->dataProvider->data[$row];
			$class = $this->evaluateExpression($this->rowCssClassExpression, array('row' => $row, 'data' => $data));
		} elseif (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0) {
			$class = $this->rowCssClass[$row % $n];
		}

		if (!empty($class)) {
			if (isset($htmlOptions['class'])) {
				$htmlOptions['class'] .= ' ' . $class;
			} else {
				$htmlOptions['class'] = $class;
			}
		}

		echo TbHtml::openTag('tr', $htmlOptions);
		foreach ($this->columns as $column) {
			echo $this->displayExtendedSummary && !empty($this->extendedSummary['columns']) ? $this->parseColumnValue(
				$column,
				$row
			) : $column->renderDataCell($row);
		}
		echo TbHtml::closeTag('tr');
	}

	/**
	 * Renders summary
	 */
	public function renderExtendedSummary()
	{
		if (!isset($this->extendedSummaryOptions['class'])) {
			$this->extendedSummaryOptions['class'] = $this->extendedSummaryCssClass;
		} else {
			$this->extendedSummaryOptions['class'] .= ' ' . $this->extendedSummaryCssClass;
		}
		echo '<div ' . TbHtml::renderAttributes($this->extendedSummaryOptions) . '></div>';
	}

	/**
	 * Renders summary content. Will be appended to
	 */
	public function renderExtendedSummaryContent()
	{
		if (($count = $this->dataProvider->getItemCount()) <= 0) {
			return;
		}

		if (!empty($this->extendedSummaryTypes)) {
			echo '<div id="' . $this->id . '-extended-summary" style="display:none">';
			if (isset($this->extendedSummary['title'])) {
				echo '<h3>' . $this->extendedSummary['title'] . '</h3>';
			}
			foreach ($this->extendedSummaryTypes as $summaryType) {
				/** @var $summaryType TbOperation */
				$summaryType->run();
				echo '<br/>';
			}
			echo '</div>';
		}
	}

	/**
	 * Registers required css, js and scripts
	 * Note: This script must be run at the end of content rendering not at the beginning as it is common with normal
	 * CGridViews
	 */
	public function registerCustomClientScript()
	{
		/* publish assets dir */
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'assets';
		$assetsUrl = $this->getAssetsUrl($path);

		/** @var $cs CClientScript */
		$cs = Yii::app()->getClientScript();

		$fixedHeaderJs = '';
		if ($this->fixedHeader) {
			$cs->registerScriptFile(
				$assetsUrl . '/js/jquery.stickytableheaders' . (!YII_DEBUG ? '.min' : '') . '.js',
				CClientScript::POS_END);
			$fixedHeaderJs = "$('#{$this->id} table.items').stickyTableHeaders({fixedOffset:{$this->headerOffset}});";
			$this->componentsAfterAjaxUpdate[] = $fixedHeaderJs;
		}

		$cs->registerScript(__CLASS__ . '#Wh' . $this->id,
			'$grid = $("#' . $this->id . '");' .
			$fixedHeaderJs . '
			if ($(".' . $this->extendedSummaryCssClass . '", $grid).length)
			{
				$(".' . $this->extendedSummaryCssClass . '", $grid).html($("#' . $this->id . '-extended-summary", $grid).html());
			}
			' . (count($this->componentsReadyScripts) ? implode("\n", $this->componentsReadyScripts) : '') . '
			$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
				var qs = $.deparam.querystring(options.url);
				if (qs.hasOwnProperty("ajax") && qs.ajax == "' . $this->id . '")
				{
					options.realsuccess = options.success;
					options.success = function(data)
					{
						if (options.realsuccess) {
							options.realsuccess(data);
							var $data = $("<div>" + data + "</div>");
							// we need to get the grid again... as it has been updated
							if ($(".' . $this->extendedSummaryCssClass . '", $("#' . $this->id . '")))
							{
								$(".' .
			$this->extendedSummaryCssClass .
			'", $("#' . $this->id .
			'")).html($("#' . $this->id .
			'-extended-summary", $data).html());
		}
		' .
			(count($this->componentsAfterAjaxUpdate)
				? implode("\n", $this->componentsAfterAjaxUpdate)
				: '') .
			'
		}
	}
}
});'
		);
	}

	/**
	 * Helper function to get a column by its name
	 * @param string $name
	 * @return CDataColumn|null
	 */
	public function getColumnByName($name)
	{
		foreach ($this->columns as $column) {
			if (strcmp((string) $column->name, $name) === 0) {
				return $column;
			}
		}
		return null;
	}

	/**
	 * Creates column objects and initializes them.
	 */
	protected function initColumns()
	{
		parent::initColumns();
		if($this->responsiveTable) {
			$this->attachBehavior('ywresponsive', array('class' => 'yiiwheels.widgets.grid.behaviors.WhResponsive'));
			$this->writeResponsiveCss($this->columns, $this->id);
		}
	}

	/**
	 * Parses the value of a column by an operation
	 * @param CDataColumn $column
	 * @param integer $row the current row number
	 * @return string
	 */
	protected function parseColumnValue($column, $row)
	{
		ob_start();
		$column->renderDataCell($row);
		$value = ob_get_clean();

		if ($column instanceof CDataColumn && array_key_exists($column->name, $this->extendedSummary['columns'])) {
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
	 * @param string $name the name of the column
	 * @param array $config the configuration of the column at the extendedSummary
	 * @return mixed
	 * @throws CException
	 */
	protected function getSummaryOperationInstance($name, $config)
	{
		if (!isset($config['class'])) {
			throw new CException(Yii::t(
				'zii',
				'Column summary configuration must be an array containing a "type" element.'
			));
		}

		if (!in_array($config['class'], $this->extendedSummaryOperations)) {
			throw new CException(Yii::t(
				'zii',
				'"{operation}" is an unsupported class operation.',
				array('{operation}' => $config['class'])
			));
		}

		// name of the column should be unique
		if (!isset($this->extendedSummaryTypes[$name])) {
			$this->extendedSummaryTypes[$name] = Yii::createComponent($config);
			$this->extendedSummaryTypes[$name]->init();
		}
		return $this->extendedSummaryTypes[$name];
	}

}
