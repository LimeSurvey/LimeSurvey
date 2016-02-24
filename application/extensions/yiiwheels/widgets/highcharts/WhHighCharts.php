<?php
/**
 * WhHighCharts widget class
 *
 * WhHighCharts is a layer of the amazing {@link http://www.highcharts.com/ Highcharts}
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('yiiwheels.widgets.WhHighCharts', array(
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
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.highcharts
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('bootstrap.helpers.TbArray');

class WhHighCharts extends CWidget
{
    /**
     * @var array $options the highcharts js configuration options
     * @see http://api.highcharts.com/highcharts
     */
    public $pluginOptions = array();

    /**
     * @var array $htmlOptions the HTML tag attributes
     */
    public $htmlOptions = array();

    /**
     * Widget's initialization method
     */
    public function init()
    {
        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));
        $this->htmlOptions['id'] = TbArray::getValue('id', $this->htmlOptions, $this->getId());
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        // if there is no renderTo id, build the layer with current id and initialize renderTo option
        if (!isset($this->pluginOptions['chart']) || !isset($this->pluginOptions['chart']['renderTo'])) {
            echo CHtml::openTag('div', $this->htmlOptions);
            echo CHtml::closeTag('div');

            if (isset($this->pluginOptions['chart']) && is_array($this->pluginOptions['chart'])) {
                $this->pluginOptions['chart']['renderTo'] = $this->htmlOptions['id'];
            } else {
                $this->pluginOptions['chart'] = array('renderTo' => $this->htmlOptions['id']);
            }

        }
        $this->registerClientScript();
    }

    /**
     * Publishes and registers the necessary script files.
     */
    protected function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerScriptFile($assetsUrl . '/js/highcharts.js');

        /* register required files */
        $defaultOptions = array('exporting' => array('enabled' => true));

        $this->pluginOptions = CMap::mergeArray($defaultOptions, $this->pluginOptions);

        if (isset($this->pluginOptions['exporting']) && @$this->pluginOptions['exporting']['enabled']) {
            $cs->registerScriptFile($assetsUrl . '/js/modules/exporting.js');
        }

        if ($theme = TbArray::getValue('theme', $this->pluginOptions)) {
            $cs->registerScriptFile($assetsUrl . '/js/themes/' . $theme . '.js');
        }

        $options = CJavaScript::encode($this->pluginOptions);

        $cs->registerScript(
            __CLASS__ . '#' . $this->getId(),
            "var highchart{$this->getId()} = new Highcharts.Chart({$options});"
        );
    }
}