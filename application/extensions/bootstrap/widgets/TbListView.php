<?php
/**
 * TbListView class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

Yii::import('zii.widgets.CListView');

/**
 * Bootstrap Zii list view.
 */
class TbListView extends CListView
{
    /**
     * @var string the CSS class name for the pager container. Defaults to 'list-pager'.
     */
    public $pagerCssClass = 'list-pager';
    /**
     * @var string the CSS class name for the summary container. Defaults to 'list-summary'.
     */
    public $summaryCssClass = 'list-summary';
    /**
     * @var string the CSS class name for the items container. Defaults to 'list-items'.
     */
    public $itemsCssClass = 'list-items';
    /**
     * @var array the configuration for the pager.
     * Defaults to <code>array('class'=>'\TbPager')</code>.
     */
    public $pager = array('class' => '\TbPager');
    /**
     * @var string the URL of the CSS file used by this detail view.
     * Defaults to false, meaning that no CSS will be included.
     */
    public $cssFile = false;
    /**
     * @var string the template to be used to control the layout of various sections in the view.
     */
    public $template = "<div class=\"row\"><div class=\"col-md-offset-6 col-md-6\">{sorter}</div></div>{items}\n<div class=\"row\"><div class=\"col-md-6\">{pager}</div><div class=\"col-md-6\">{summary}</div></div>";

    /**
     * Renders the empty message when there is no data.
     */
    public function renderEmptyText()
    {
        $emptyText = $this->emptyText === null ? Yii::t('zii', 'No results found.') : $this->emptyText;
        echo TbHtml::tag('div', array('class' => 'empty', 'span' => 12), $emptyText);
    }
}
