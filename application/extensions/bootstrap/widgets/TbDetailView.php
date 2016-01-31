<?php

/**
 * TbDetailView class file.
 * @author Sam Stenvall <sam@supportersplace.com>
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */
Yii::import('zii.widgets.CDetailView');

/**
 * Bootstrap Zii detail widget.
 */
class TbDetailView extends CDetailView
{
    /**
     * @var string|array the detail view style.
     * Valid values are TbHtml::DETAIL_STRIPED, TbHtml::DETAIL_BORDERED, TbHtml::DETAIL_CONDENSED and/or TbHtml::DETAIL_HOVER.
     */
    public $type = array(TbHtml::DETAIL_TYPE_STRIPED, TbHtml::DETAIL_TYPE_CONDENSED);
    /**
     * @var string the URL of the CSS file used by this grid view.
     * Defaults to false, meaning that no CSS will be included.
     */
    public $cssFile = false;

    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();
        $classes = array('table');
        if (!empty($this->type)) {
            if (is_string($this->type)) {
                $this->type = explode(' ', $this->type);
            }

            foreach ($this->type as $type) {
                $classes[] = 'table-' . $type;
            }
        }
        TbHtml::addCssClass($classes, $this->htmlOptions);
    }
}
