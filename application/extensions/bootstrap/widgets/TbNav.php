<?php
/**
 * TbNav class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap navigation menu widget.
 * @see http://getbootstrap.com/components/#nav
 */
class TbNav extends CWidget
{
    /**
     * @var string the menu type.
     */
    public $type;
    /**
     * @var boolean whether the menu items should be stacked on top of each other.
     */
    public $stacked = false;
    /**
     * @var string|array the scrollspy target or configuration.
     */
    public $scrollspy;
    /**
     * @var array list of menu items. Each menu item is specified as an array of name-value pairs.
     */
    public $items = array();
    /**
     * @var boolean whether the labels for menu items should be HTML-encoded. Defaults to true.
     */
    public $encodeLabel = true;
    /**
     * @var boolean whether to automatically activate items according to whether their route setting
     * matches the currently requested route. Defaults to true.
     */
    public $activateItems = true;
    /**
     * @var boolean whether to activate parent menu items when one of the corresponding child menu items is active.
     */
    public $activateParents = false;
    /**
     * @var boolean whether to hide empty menu items.
     */
    public $hideEmptyItems = true;
    /**
     * @var array HTML attributes for the menu's root container tag.
     */
    public $htmlOptions = array();

    // todo: consider supporting these.
    //public $submenuHtmlOptions = array();
    //public $linkLabelWrapper;
    //public $linkLabelWrapperHtmlOptions=array();
    //public $itemCssClass;

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->attachBehavior('TbWidget', new TbWidget);
        $this->resolveId();
        $route = $this->controller->getRoute();
        if ($this->stacked) {
            TbHtml::addCssClass('nav-stacked', $this->htmlOptions);
        }
        if (isset($this->scrollspy)) {
            if (is_string($this->scrollspy)) {
                $this->scrollspy = array('target' => $this->scrollspy);
            }
            $this->widget('\TbScrollspy', $this->scrollspy);
        }
        $this->items = $this->normalizeItems($this->items, $route, $hasActiveChild);
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        if (!empty($this->items)) {
            echo TbHtml::nav($this->type, $this->items, $this->htmlOptions);
        }
    }

    /**
     * Normalizes the menu items.
     * @param array $items the items to be normalized.
     * @param string $route the route of the current request.
     * @param boolean $active whether there is an active child menu item.
     * @return array the normalized menu items.
     */
    protected function normalizeItems($items, $route, &$active)
    {
        foreach ($items as $i => $item) {
            // skip dividers
            if (is_string($item)) {
                continue;
            }

            if (isset($item['visible']) && !$item['visible']) {
                unset($items[$i]);
                continue;
            }

            TbArray::defaultValue('label', '', $item);

            if ($this->encodeLabel) {
                $items[$i]['label'] = CHtml::encode($item['label']);
            }

            $hasActiveChild = false;

            if (isset($item['items']) && !empty($item['items'])) {
                $items[$i]['items'] = $this->normalizeItems($item['items'], $route, $hasActiveChild);

                if (empty($items[$i]['items']) && $this->hideEmptyItems) {
                    unset($items[$i]['items']);
                    if (!isset($item['url'])) {
                        unset($items[$i]);
                        continue;
                    }
                }
            }

            if (!isset($item['active'])) {
                if ($this->activateParents && $hasActiveChild || $this->activateItems && $this->isItemActive(
                        $item,
                        $route
                    )
                ) {
                    $active = $items[$i]['active'] = true;
                } else {
                    $items[$i]['active'] = false;
                }
            } else {
                if ($item['active']) {
                    $active = true;
                }
            }
        }

        return array_values($items);
    }

    /**
     * Checks whether a menu item is active.
     * @param array $item the menu item to be checked.
     * @param string $route the route of the current request.
     * @return boolean whether the menu item is active.
     */
    protected function isItemActive($item, $route)
    {
        if (isset($item['url']) && is_array($item['url']) && !strcasecmp(trim($item['url'][0], '/'), $route)) {
            unset($item['url']['#']);
            if (count($item['url']) > 1) {
                foreach (array_splice($item['url'], 1) as $name => $value) {
                    if (!isset($_GET[$name]) || $_GET[$name] != $value) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }
}
