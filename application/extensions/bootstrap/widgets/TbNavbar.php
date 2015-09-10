<?php
/**
 * TbNavbar class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap navbar widget.
 * @see http://twitter.github.com/bootstrap/components.html#navbar
 */
class TbNavbar extends CWidget
{
    /**
     * @var string the navbar color.
     */
    public $color;
    /**
     * @var string the brand label text.
     */
    public $brandLabel;
    /**
     * @var mixed the brand url.
     */
    public $brandUrl;
    /**
     * @var array the HTML attributes for the brand link.
     */
    public $brandOptions = array();
    /**
     * @var string nanvbar display type.
     */
    public $display = TbHtml::NAVBAR_DISPLAY_FIXEDTOP;
    /**
     * @var boolean whether the navbar spans over the whole page.
     */
    public $fluid = false;
    /**
     * @var boolean whether to enable collapsing of the navbar on narrow screens.
     * @deprecated Bootstrap 3 is built to always be responsive
     */
    public $collapse = false;
    /**
     * @var array additional HTML attributes for the collapse widget.
     */
    public $collapseOptions = array();
    /**
     * @var array list of navbar item.
     */
    public $items = array();
    /**
     * @var array the HTML attributes for the navbar.
     */
    public $htmlOptions = array();

    /**
     * Initializes the widget.
     */
    public function init()
    {
        if ($this->brandLabel !== false) {
            if (!isset($this->brandLabel)) {
                $this->brandLabel = CHtml::encode(Yii::app()->name);
            }

            if (!isset($this->brandUrl)) {
                $this->brandUrl = Yii::app()->homeUrl;
            }
        }
        if (isset($this->color)) {
            TbArray::defaultValue('color', $this->color, $this->htmlOptions);
        }
        if (isset($this->display) && $this->display !== TbHtml::NAVBAR_DISPLAY_NONE) {
            TbArray::defaultValue('display', $this->display, $this->htmlOptions);
        }
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        $brand = $this->brandLabel !== false
            ? TbHtml::navbarBrandLink($this->brandLabel, $this->brandUrl, $this->brandOptions)
            : '';
        ob_start();
        foreach ($this->items as $item) {
            if (is_string($item)) {
                echo $item;
            } else {
                $widgetClassName = TbArray::popValue('class', $item);
                if ($widgetClassName !== null) {
                    $this->controller->widget($widgetClassName, $item);
                }
            }
        }
        $items = ob_get_clean();
        ob_start();
        if ($this->collapse !== false) {
            TbHtml::addCssClass('navbar-collapse', $this->collapseOptions);
            ob_start();
            /* @var TbCollapse $collapseWidget */
            $collapseWidget = $this->controller->widget(
                '\TbCollapse',
                array(
                    'toggle' => false, // navbars are collapsed by default
                    'content' => $items,
                    'htmlOptions' => $this->collapseOptions,
                )
            );
            $collapseContent = ob_get_clean();
            echo TbHtml::tag(
                'div', array('class' => 'navbar-header'),
                TbHtml::navbarCollapseLink('#' . $collapseWidget->getId()) . $brand
            );
            echo $collapseContent;

        } else {
            echo TbHtml::tag('div', array('class' => 'navbar-header'), $brand);
            echo $items;
        }
        $containerContent = ob_get_clean();
        $containerOptions = TbArray::popValue('containerOptions', $this->htmlOptions, array());
        TbHtml::addCssClass($this->fluid ? 'container-fluid' : 'container', $containerOptions);
        ob_start();
        echo TbHtml::openTag('div', $containerOptions);
        echo $containerContent;
        echo '</div>';
        $content = ob_get_clean();
        echo TbHtml::navbar($content, $this->htmlOptions);
    }
}