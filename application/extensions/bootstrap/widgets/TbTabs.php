<?php
/**
 * TbTabs class file.
 * @author Antonio Ramirez <ramirez.cobos@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap tabs widget.
 */
class TbTabs extends CWidget
{
    /**
     * @var string the type of tabs to display. Valid values are 'tabs' and 'pills' (defaults to 'tabs').
     * @see TbHtml::$navStyles
     */
    public $type = TbHtml::NAV_TYPE_TABS;
    /**
     * @var string the placement of the tabs. Valid values are 'right, 'left' and 'below'.
     * @see TbHtml::tabPlacements
     */
    public $placement;
    /**
     * @var array the tab configuration.
     */
    public $tabs = array();
    /**
     * @var array additional data submitted to the views.
     */
    public $viewData;
    /**
     * @var string a javascript function that This event fires on tab show, but before the new tab has been shown.
     * Use `event.target` and `event.relatedTarget` to target the active tab and the previous active tab (if available)
     * respectively.
     */
    public $onShow;
    /**
     * @var string a javascript function that fires on tab show after a tab has been shown. Use `event.target` and
     * `event.relatedTarget` to target the active tab and the previous active tab (if available) respectively.
     */
    public $onShown;
    /**
     * @var array the HTML attributes for the widget container.
     */
    public $htmlOptions = array();
    /**
     * @var string[] the Javascript event handlers.
     */
    protected $events = array();

    /**
     * Widget's initialization method
     */
    public function init()
    {
        $this->attachBehavior('TbWidget', new TbWidget);
        $this->copyId();
        TbArray::defaultValue('placement', $this->placement, $this->htmlOptions);
        $this->initEvents();
    }

    /**
     * Initialize events if any
     */
    public function initEvents()
    {
        foreach (array('onShow', 'onShown') as $event) {
            if ($this->$event !== null) {
                $modalEvent = strtolower(substr($event, 2));
                if ($this->$event instanceof CJavaScriptExpression) {
                    $this->events[$modalEvent] = $this->$event;
                } else {
                    $this->events[$modalEvent] = new CJavaScriptExpression($this->$event);
                }
            }
        }
    }

    /**
     * Widget's run method
     */
    public function run()
    {
        $this->tabs = $this->normalizeTabs($this->tabs);
        echo TbHtml::tabbable($this->type, $this->tabs, $this->htmlOptions);
        $this->registerClientScript();
    }

    /**
     * Normalizes the tab configuration.
     * @param array $tabs a reference to the tabs tab configuration.
     */
    protected function normalizeTabs($tabs)
    {
        $controller = $this->getController();
        if (isset($controller)) {
            foreach ($tabs as &$tabOptions) {
                $items = TbArray::getValue('items', $tabOptions, array());
                if (!empty($items)) {
                    $tabOptions['items'] = $this->normalizeTabs($items);
                } else {
                    if (isset($tabOptions['view'])) {
                        $view = TbArray::popValue('view', $tabOptions);
                        if ($controller->getViewFile($view) !== false) {
                            $tabOptions['content'] = $controller->renderPartial($view, $this->viewData, true);
                        }
                    }
                }
            }
        }
        return $tabs;
    }

    /**
     * Registers necessary client scripts.
     */
    public function registerClientScript()
    {
        $selector = '#' . $this->htmlOptions['id'];
        Yii::app()->clientScript->registerScript(__CLASS__ . $selector, "jQuery('{$selector}').tab('show');");
        $this->registerEvents($selector, $this->events);
    }
}