<?php
/**
 *
 * WhPercentOfTypeGooglePieOperation class
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

class WhPercentOfTypeGooglePieOperation extends WhPercentOfTypeOperation
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

		foreach ($this->types as $type) {
			if (!isset($type['value'])) {
				$type['value'] = 0;
			}

			$this->data[] = $this->getTotal() ? array(
				$type['label'],
				(float)number_format(($type['value'] / $this->getTotal()) * 100, 1)
			) : 0;
		}
		$data = CJavaScript::jsonEncode($this->data);
		$options = CJavaScript::jsonEncode($this->chartOptions);
		echo "<div id='{$this->id}' class='{$this->chartCssClass}' data-data='{$data}' data-options='{$options}'></div>";

		$this->registerClientScript();
	}

	/**
	 * Registers required scripts
	 * @see WhVisualizationChart
	 */
	public function registerClientScript()
	{
		// Run chart
		$chart = Yii::createComponent(
			array(
				'class' => 'yiiwheels.widgets.google.WhVisualizationChart',
				'visualization' => 'PieChart',
				'containerId' => $this->getId(),
				'data' => $this->data,
				'options' => $this->chartOptions
			)
		);
		$chart->init();
		$chart->run();

		// create custom chart update by using the global chart variable
		$this->column->grid->componentsAfterAjaxUpdate[__CLASS__] =
			'var $el = $("#' . $this->getId() . '");var data = $el.data("data");var opts = $el.data("options");
			data = google.visualization.arrayToDataTable(data);
			' . $chart->getId() . '=new google.visualization.PieChart(document.getElementById("' . $this->getId() . '"));
			' . $chart->getId() . '.draw(data,opts);';
	}

}
