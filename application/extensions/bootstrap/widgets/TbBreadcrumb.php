<?php
/**
 * TbBreadcrumbs class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Bootstrap breadcrumb widget.
 * @see http://getbootstrap.com/components/#breadcrumbs
 * @package bootstrap.widgets
 */
class TbBreadcrumb extends CWidget
{
    /**
     * @var boolean whether to HTML encode the link labels.
     */
    public $encodeLabel = true;
    /**
     * @var string the label for the first link in the breadcrumb.
     */
    public $homeLabel;
    /**
     * @var array the url for the first link in the breadcrumb
     */
    public $homeUrl;
    /**
     * @var array the HTML attributes for the breadcrumbs.
     */
    public $htmlOptions = array();
    /**
     * @var array list of links to appear in the breadcrumbs.
     */
    public $links = array();

    /**
     * Runs the widget.
     */
    public function run()
    {
        // todo: consider adding control property for displaying breadcrumbs even when empty.
        if (!empty($this->links)) {
            $links = array();
            if ($this->homeLabel !== false) {
                $label = $this->homeLabel !== null ? $this->homeLabel : TbHtml::icon('home');
                $links[$label] = $this->homeUrl !== null ? $this->homeUrl : Yii::app()->homeUrl;
            }
            foreach ($this->links as $label => $url) {
                if (is_string($label)) {
                    if ($this->encodeLabel) {
                        $label = CHtml::encode($label);
                    }
                    $links[$label] = $url;
                } else {
                    $links[] = $this->encodeLabel ? CHtml::encode($url) : $url;
                }
            }
            echo TbHtml::breadcrumbs($links, $this->htmlOptions);
        }
    }
}
