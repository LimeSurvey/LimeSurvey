<?php
/**
 * WhTimeAgo class
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.timeago
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('yiistrap_fork.helpers.TbArray');

class WhTimeAgo extends CWidget
{
    /**
     * @var string the HTML tag type
     */
    public $tagName = 'abbr';

    /**
     * @var array additional HTML attributes to the tag
     */
    public $htmlOptions = array();

    /**
     * @var array plugin options
     */
    public $pluginOptions = array();

    /**
     * @var string the language
     * @see js/locales
     */
    public $language = 'en';

    /**
     * @var string the selector to initialize the widget. Defaults to widget id.
     */
    public $selector;

    /**
     * @var string the date to use the timeago against. If null, the widget will not render the tag, assuming that
     * everything will be handled via the $selector.
     */
    public $date;

    /**
     * Widget's initialization method
     */
    public function init()
    {
        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));

        $this->htmlOptions['id'] = TbArray::getValue('id', $this->htmlOptions, $this->getId());

        if (!$this->selector) {
            $this->selector = '#' . TbArray::getValue('id', $this->htmlOptions);
        }

    }

    /**
     * Widget's run method
     */
    public function run()
    {
        if (null !== $this->date) {
            $this->htmlOptions['title'] = $this->date;
            echo CHtml::tag($this->tagName, $this->htmlOptions, '&nbsp;');
        }
        $this->registerClientScript();
    }

    /**
     * Registers required client script for sparklines
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerScriptFile($assetsUrl . '/js/jquery.timeago.js');

        if (null !== $this->language) {
            $cs->registerScriptFile($assetsUrl . '/js/locales/jquery.timeago.' . $this->language . '.js');
        }

        /* initialize plugin */
        $this->getApi()->registerPlugin('timeago', $this->selector, $this->pluginOptions);
    }

}
