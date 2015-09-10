<?php
/**
 *
 * WhPercentOfTypeEasyPieOperation class
 *
 * Displays an chart based on jquery.easy.pie plugin
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.grid.operations
 * @uses Yiistrap.widgets.WhPercentOfTypeOperation
 */
Yii::import('yiiwheels.widgets.grid.operations.WhPercentOfTypeOperation');

class WhPercentOfTypeEasyPieOperation extends WhPercentOfTypeOperation
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

	/**
	 * @var array the easy-pie-chart plugin configuration options
	 * @see https://github.com/rendro/easy-pie-chart#configuration-parameter
	 */
	public $chartOptions = array(
		'barColor' => '#ef1e25',
		// The color of the curcular bar. You can pass either a css valid color string like rgb,
		// rgba hex or string colors. But you can also pass a function that accepts the current
		// percentage as a value to return a dynamically generated color.
		'trackColor' => '#f2f2f2',
		// The color of the track for the bar, false to disable rendering.
		'scaleColor' => '#dfe0e0',
		// The color of the scale lines, false to disable rendering.
		'lineCap' => 'round',
		// Defines how the ending of the bar line looks like. Possible values are: butt, round and square.
		'lineWidth' => 5,
		// Width of the bar line in px.
		'size' => 80,
		// Size of the pie chart in px. It will always be a square.
		'animate' => false,
		// Time in milliseconds for a eased animation of the bar growing, or false to deactivate.
		'onStart' => 'js:$.noop',
		// Callback function that is called at the start of any animation (only if animate is not false).
		'onStop' => 'js:$.noop'
		// Callback function that is called at the end of any animation (only if animate is not false).
	);

	/**
	 * Widget's initialization widget
	 */
	public function init()
	{
		$this->typeTemplate = strtr($this->typeTemplate, array('{class}' => $this->chartCssClass));
		parent::init();
	}


	/**
	 * @see WhOperation
	 * @return mixed|void
	 */
	public function displaySummary()
	{
		parent::displaySummary();
		$this->registerClientScripts();
	}

	/**
	 * Register required scripts
	 */
	protected function registerClientScripts()
	{
		/* publish assets dir */
		$path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets';

		$assetsUrl = $this->getAssetsUrl($path);

		/* @var $cs CClientScript */
		$cs = Yii::app()->getClientScript();

		$cs->registerCssFile($assetsUrl . '/css/easy-pie-chart.css');
		$cs->registerScriptFile($assetsUrl . '/js/jquery.easy.pie.chart.js');

		$options = CJavaScript::encode($this->chartOptions);
		Yii::app()->getClientScript()->registerScript(
			__CLASS__ . '#percent-of-type-operation-simple-pie',
			'$("#' . $this->column->grid->id . ' .' . $this->column->grid->extendedSummaryCssClass . ' .' . $this->chartCssClass . '")
			.easyPieChart(' . $options . ');');

		$this->column->grid->componentsReadyScripts[__CLASS__] =
		$this->column->grid->componentsAfterAjaxUpdate[__CLASS__] =
			'$("#' . $this->column->grid->id . ' .' . $this->column->grid->extendedSummaryCssClass . ' .' . $this->chartCssClass . '")
				.easyPieChart(' . $options . ');';
	}
}