<?php
/**
 * WhChart class
 * Extends WhGridView to provide chart display (on switch)
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; Antonio Ramirez 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package yiiwheels.widgets.grid.behaviors
 */

class WhChart extends CBehavior
{

	public function getGrid()
	{
		return $this->getOwner();
	}

	/**
	 * Renders grid/chart control buttons to switch between both components
	 */
	public function renderChartControlButtons()
	{
		echo '<div class="row-fluid">';
		echo TbHtml::buttonGroup(array(
			array(
				'label' => Yii::t('zii', 'Display Grid'),
				'url' => '#',
				'htmlOptions' => array('class' => 'active ' . $this->grid->getId() . '-grid-control grid')
			),
			array(
				'label' => Yii::t('zii', 'Display Chart'),
				'url' => '#',
				'htmlOptions' => array('class' => $this->grid->getId() . '-grid-control chart')
			),
		), array('toggle' => TbHtml::BUTTON_TOGGLE_RADIO, 'style' => 'margin-bottom:5px', 'class' => 'pull-right'));
		echo '</div>';

	}

	/**
	 * Registers grid/chart control button script
	 * @returns string the chart id
	 */
	public function registerChartControlButtonsScript()
	{
		// cleaning out most possible characters invalid as javascript variable identifiers.
		$chartId = preg_replace('[-\\ ?]', '_', 'xyzChart' . $this->grid->getId());

		$this->grid->componentsReadyScripts[] = '$(document).on("click",".' . $this->grid->getId() . '-grid-control", function(){
			if ($(this).hasClass("grid") && $("#' . $this->grid->getId() . ' #' . $chartId . '").is(":visible"))
			{
				$("#' . $this->grid->getId() . ' #' . $chartId . '").hide();
				$("#' . $this->grid->getId() . ' table.items").show();
			}
			if ($(this).hasClass("chart") && $("#' . $this->grid->getId() . ' table.items").is(":visible"))
			{
				$("#' . $this->grid->getId() . ' table.items").hide();
				$("#' . $this->grid->getId() . ' #' . $chartId . '").show();
			}
			$(this).addClass("active").siblings().removeClass("active");

			return false;
		});';

		return $chartId;
	}

	/**
	 * Renders a chart based on the data series specified
	 * @throws CException
	 */
	public function renderChart()
	{
		$displayChart = (!empty($this->grid->chartOptions) && @$this->grid->chartOptions['data'] && $this->grid->dataProvider->getItemCount());

		if (!$displayChart || $this->grid->dataProvider->getItemCount() <= 0) {

			return null;
		}

		if (!isset($this->grid->chartOptions['data']['series'])) {
			throw new CException(Yii::t(
				'zii',
				'You need to set the "series" attribute in order to render a chart'
			));
		}

		$configSeries = $this->grid->chartOptions['data']['series'];
		if (!is_array($configSeries)) {
			throw new CException(Yii::t('zii', '"chartOptions.series" is expected to be an array.'));
		}

		if (!isset($this->grid->chartOptions['config'])) {
			$this->grid->chartOptions['config'] = array();
		}

		$this->renderChartControlButtons();
		$chartId = $this->grid->registerChartControlButtonsScript();

		// render Chart
		// chart options
		$data = $this->grid->dataProvider->getData();
		$count = count($data);
		$seriesData = array();
		$cnt = 0;
		foreach ($configSeries as $set) {
			$seriesData[$cnt] = array('name' => isset($set['name']) ? $set['name'] : null, 'data' => array());

			for ($row = 0; $row < $count; ++$row) {
				$column = $this->grid->getColumnByName($set['attribute']);
				if (!is_null($column) && $column->value !== null) {
					$seriesData[$cnt]['data'][] = $this->evaluateExpression(
						$column->value,
						array('data' => $data[$row], 'row' => $row)
					);
				} else {
					$value = CHtml::value($data[$row], $set['attribute']);
					$seriesData[$cnt]['data'][] = is_numeric($value) ? (float)$value : $value;
				}

			}
			++$cnt;
		}

		$options = CMap::mergeArray($this->grid->chartOptions['config'], array('series' => $seriesData));

		$this->grid->chartOptions['htmlOptions'] = isset($this->grid->chartOptions['htmlOptions'])
			? $this->chartOptions['htmlOptions']
			: array();

		// sorry but use a class to provide styles, we need this
		$this->grid->chartOptions['htmlOptions']['style'] = 'display:none';

		// build unique ID
		// important!
		echo '<div class="row-fluid">';
		if ($this->grid->ajaxUpdate !== false) {
			if (isset($options['chart']) && is_array($options['chart'])) {
				$options['chart']['renderTo'] = $chartId;
			} else {
				$options['chart'] = array('renderTo' => $chartId);
			}
			$jsOptions = CJSON::encode($options);

			if (isset($this->grid->chartOptions['htmlOptions']['data-config'])) {
				unset($this->grid->chartOptions['htmlOptions']['data-config']);
			}

			echo "<div id='{$chartId}' " . CHtml::renderAttributes(
					$this->grid->chartOptions['htmlOptions']
				) . " data-config='{$jsOptions}'></div>";

			$this->grid->componentsAfterAjaxUpdate[] = "highchart{$chartId} = new Highcharts.Chart($('#{$chartId}').data('config'));";
		}
		$configChart = array(
			'class' => 'yiiwheels.widgets.highcharts.WhHighCharts',
			'id' => $chartId,
			'pluginOptions' => $options,
			'htmlOptions' => $this->grid->chartOptions['htmlOptions']
		);
		$chart = Yii::createComponent($configChart);
		$chart->init();
		$chart->run();
		echo '</div>';
	}
}