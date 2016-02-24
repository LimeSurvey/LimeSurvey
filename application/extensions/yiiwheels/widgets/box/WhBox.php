<?php
/**
 *
 * WhBox.php
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.box
 * @uses YiiStrap.helpers.TbHtml
 */
class WhBox extends CWidget
{
    /**
     * @var mixed
     * Box title
     * If set to false, a box with no title is rendered
     */
    public $title = '';

    /**
     * @var string
     * The class icon to display in the header title of the box.
     * @see <http://twitter.github.com/bootstrap/base-css.html#icon>
     */
    public $headerIcon;


    /**
     * @var string
     * Box Content
     * optional, the content of this attribute is echoed as the box content
     */
    public $content = '';

    /**
     * @var array
     * box HTML additional attributes
     */
    public $htmlOptions = array();

    /**
     * @var array
     * box header HTML additional attributes
     */
    public $htmlHeaderOptions = array();

    /**
     * @var array
     * box content HTML additional attributes
     */
    public $htmlContentOptions = array();

    /**
     * @var array the configuration for additional header buttons. Each array element specifies a single button
     * which has the following format:
     * <pre>
     *     array(
     *      TbHtml::button('Primary', array('color' => TbHtml::BUTTON_COLOR_PRIMARY))
     *      ...
     * )
     * </pre>
     */
    public $headerButtons = array();

    /**
     *### .init()
     *
     * Widget initialization
     */
    public function init()
    {
        if (isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] = 'bootstrap-widget ' . $this->htmlOptions['class'];
        } else {
            $this->htmlOptions['class'] = 'bootstrap-widget';
        }

        if (isset($this->htmlContentOptions['class'])) {
            $this->htmlContentOptions['class'] = 'bootstrap-widget-content ' . $this->htmlContentOptions['class'];
        } else {
            $this->htmlContentOptions['class'] = 'bootstrap-widget-content';
        }

        if (!isset($this->htmlContentOptions['id'])) {
            $this->htmlContentOptions['id'] = $this->getId();
        }

        if (isset($this->htmlHeaderOptions['class'])) {
            $this->htmlHeaderOptions['class'] = 'bootstrap-widget-header ' . $this->htmlHeaderOptions['class'];
        } else {
            $this->htmlHeaderOptions['class'] = 'bootstrap-widget-header';
        }

        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));

        echo CHtml::openTag('div', $this->htmlOptions);

        $this->registerClientScript();
        $this->renderHeader();
        $this->renderContentBegin();
    }

    /**
     *### .run()
     *
     * Widget run - used for closing procedures
     */
    public function run()
    {
        $this->renderContentEnd();
        echo CHtml::closeTag('div') . "\n";
    }

    /**
     *### .renderHeader()
     *
     * Renders the header of the box with the header control (button to show/hide the box)
     */
    public function renderHeader()
    {
        if ($this->title !== false) {
            echo CHtml::openTag('div', $this->htmlHeaderOptions);
            if ($this->title) {
                $this->title = '<h3>' . $this->title . '</h3>';

                if ($this->headerIcon) {
                    $this->title = '<i class="' . $this->headerIcon . '"></i>' . $this->title;
                }

                echo $this->title;
                $this->renderButtons();
            }
            echo CHtml::closeTag('div');
        }
    }

    /**
     *### .renderButtons()
     *
     * Renders a header buttons to display the configured actions
     */
    public function renderButtons()
    {
        if (empty($this->headerButtons)) {
            return;
        }

        echo '<div class="bootstrap-toolbar pull-right">';

        if (!empty($this->headerButtons) && is_array($this->headerButtons)) {
            foreach ($this->headerButtons as $button) {
                if (is_string($button)) {
                    echo $button;
                    continue;
                }
                $options = $button;
                $button  = $options['class'];
                unset($options['class']);

                if (strpos($button, 'TbButton') === false) {
                    throw new CException('message');
                }

                if (!isset($options['htmlOptions'])) {
                    $options['htmlOptions'] = array();
                }

                $class                           = isset($options['htmlOptions']['class']) ? $options['htmlOptions']['class'] : '';
                $options['htmlOptions']['class'] = $class . ' pull-right';

                $this->controller->widget($button, $options);
            }
        }

        echo '</div>';
    }

    /*
     *### .renderContentBegin()
     *
     * Renders the opening of the content element and the optional content
     */
    public function renderContentBegin()
    {
        echo CHtml::openTag('div', $this->htmlContentOptions);
        if (!empty($this->content)) {
            echo $this->content;
        }
    }

    /*
     *### .renderContentEnd()
     *
     * Closes the content element
     */
    public function renderContentEnd()
    {
        echo CHtml::closeTag('div');
    }

    /**
     *### .registerClientScript()
     *
     * Registers required script files (CSS in this case)
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerCssFile($assetsUrl . '/css/box.css');

    }
}