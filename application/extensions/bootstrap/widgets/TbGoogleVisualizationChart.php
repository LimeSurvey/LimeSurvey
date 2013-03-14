<?php
/**
 * TbGoogleVisualizationChart widget
 *
 * Makes use of the Google Visualization service to render charts
 *
 * @see https://developers.google.com/chart/interactive/docs/gallery
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
class TbGoogleVisualizationChart extends CWidget
{
	/**
	 * @var string $containerId the container Id to render the visualization to
	 */
	public $containerId;
	/**
	 * @var string $visualization the type of visualization -ie PieChart
	 * @see https://google-developers.appspot.com/chart/interactive/docs/gallery
	 */
	public $visualization;
	/**
	 * @var array $data the data to configure visualization
	 * @see https://google-developers.appspot.com/chart/interactive/docs/datatables_dataviews#arraytodatatable
	 */
	public $data = array();

	/**
	 * @var array $options additional configuration options
	 * @see https://google-developers.appspot.com/chart/interactive/docs/customizing_charts
	 */
	public $options = array();

	/**
	 * @var array $htmlOption the HTML tag attributes configuration
	 */
	public $htmlOptions = array();

	/**
	 * Widget's run method
	 */
	public function run() {
		$id = $this->getId();
		$this->htmlOptions['id'] = $id;
		// if no container is set, it will create one
		if($this->containerId==null)
		{
			$this->containerId = 'div-chart'.$id;
			echo '<div '.CHtml::renderAttributes($this->htmlOptions).'></div>';
		}

		$this->registerClientScript();
	}

	/**
	 * Registers required scripts
	 */
	public function registerClientScript()
	{
		$id = $this->getId();
		$jsData = CJavaScript::jsonEncode($this->data);
		$jsOptions = CJavaScript::jsonEncode($this->options);


		$script = '
            google.setOnLoadCallback(drawChart'.$id.');
            var '.$id.'=null;
            function drawChart'.$id.'() {
                var data = google.visualization.arrayToDataTable('.$jsData.');

                var options = '.$jsOptions.';

                '.$id.' = new google.visualization.'.$this->visualization.'(document.getElementById("'.$this->containerId.'"));
                '.$id.'.draw(data, options);
            }';


		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile("https://www.google.com/jsapi");
		$cs->registerScript(__CLASS__.'#'.$id, 'google.load("visualization", "1", {packages:["corechart"]});', CClientScript::POS_HEAD);
		$cs->registerScript($id, $script, CClientScript::POS_HEAD );
	}
}