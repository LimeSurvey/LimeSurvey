<?php
/**
 * TbHighCharts widget class
 *
 * TbHighCharts is a layer of the amazing {@link http://www.highcharts.com/ Highcharts}
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('bootstrap.widgets.TbHighCharts', array(
 *    'options'=>array(
 *       'title' => array('text' => 'Fruit Consumption'),
 *       'xAxis' => array(
 *          'categories' => array('Apples', 'Bananas', 'Oranges')
 *       ),
 *       'yAxis' => array(
 *          'title' => array('text' => 'Fruit eaten')
 *       ),
 *       'series' => array(
 *          array('name' => 'Jane', 'data' => array(1, 0, 4)),
 *          array('name' => 'John', 'data' => array(5, 7, 3))
 *       )
 *    )
 * ));
 * </pre>
 *
 * To find out more about the possible {@link $options} attribute please refer to
 * {@link http://www.hightcharts.com/ Highcharts site}
 *
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
class TbHighCharts extends CWidget
{
	/**
	 * @var array $options the highcharts js configuration options
	 */
	public $options = array();

	/**
	 * @var array $htmlOptions the HTML tag attributes
	 */
	public $htmlOptions = array();

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		$id = $this->getId();

		// if there is no renderTo id, build the layer with current id and initialize renderTo option
		if (!isset($this->options['chart']) || !isset($this->options['chart']['renderTo']))
		{
			$this->htmlOptions['id'] = $id;

			echo '<div ' . CHtml::renderAttributes($this->htmlOptions) . ' ></div>';

			if (isset($this->options['chart']) && is_array($this->options['chart']))
			{
				$this->options['chart']['renderTo'] = $id;
			} else
			{
				$this->options['chart'] = array('renderTo' => $id);
			}
		}
		$this->registerClientScript();
	}

	/**
	 * Publishes and registers the necessary script files.
	 *
	 * @param string the id of the script to be inserted into the page
	 * @param string the embedded script to be inserted into the page
	 */
	protected function registerClientScript()
	{

		Yii::app()->bootstrap->registerAssetJs('highcharts/highcharts.js');

		$defaultOptions = array('exporting' => array('enabled' => true));

		$this->options = CMap::mergeArray($defaultOptions, $this->options);

		if (isset($this->options['exporting']) && @$this->options['exporting']['enabled'])
		{
			Yii::app()->bootstrap->registerAssetJs('highcharts/modules/exporting.js');
		}
		if (isset($this->options['theme']))
		{
			Yii::app()->bootstrap->registerAssetJs('highcharts/themes/' . $this->options['theme'] . '.js');
		}

		$options = CJavaScript::encode($this->options);

		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), "var highchart{$this->getId()} = new Highcharts.Chart({$options});");
	}
}