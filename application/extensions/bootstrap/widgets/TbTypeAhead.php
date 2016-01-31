<?php
/**
 * TbModal class file.
 * @author Antonio Ramirez <ramirez.cobos@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap typeahead widget.
 */
class TbTypeAhead extends CInputWidget
{
    /**
     * @var mixed the data source to query against. May be an array of strings or a function. The function is passed
     * two arguments, the query value in the input field and the process callback. The function may be used synchronously
     * by returning the data source directly or asynchronously via the process callback's single argument.
     */
    public $source = array();

    /**
     * @var int the max number of items to display in the dropdown. Defaults to 8.
     */
    public $items = 8;

    /**
     * @var int the minimum character length needed before triggering autocomplete suggestions
     */
    public $minLength = 1;

    /**
     * @var string javascript function the method used to determine if a query matches an item. Accepts a single argument, the item
     * against which to test the query. Access the current query with this.query. Return a boolean true if query is a
     * match. Case insensitive.
     */
    public $matcher;

    /**
     * @var string javascript function method used to sort autocomplete results. Accepts a single argument items and has
     * the scope of the typeahead instance. Reference the current query with this.query. Exact match, case sensitive,
     * case insensitive
     */
    public $sorter;

    /**
     * @var string javascript the method used to return selected item. Accepts a single argument, the item and has the
     * scope of the typeahead instance. Returns selected item.
     */
    public $updater;

    /**
     * @var string javascript method used to highlight autocomplete results. Accepts a single argument item and has the
     * scope of the typeahead instance. Should return html. Highlights all default matches
     */
    public $highlighter;

    /**
     * @var array the plugin options
     */
    protected $pluginOptions = array();

    /**
     * Widget's initialization method.
     */
    public function init()
    {
        $this->attachBehavior('TbWidget', new TbWidget);
        $this->initOptions();
    }

    /**
     * Initializes the plugin options
     */
    public function initOptions()
    {
        $options = array();
        foreach (array('matcher', 'sorter', 'updater', 'highlighter') as $fn) {
            if ($this->$fn !== null) {
                if ($this->$fn instanceof CJavaScriptExpression) {
                    $options[$fn] = $this->$fn;
                } else {
                    $options[$fn] = new CJavaScriptExpression($this->$fn);
                }
            }
        }

        $this->pluginOptions = TbArray::merge(
            array(
                'source' => $this->source,
                'items' => $this->items,
                'minLength' => $this->minLength
            ),
            $options
        );
    }

    /**
     * Widget's run method.
     */
    public function run()
    {
        $this->renderField();
        $this->registerClientScript();
    }

    /**
     * Renders field
     */
    public function renderField()
    {
        list($name, $id) = $this->resolveNameID();

        TbArray::defaultValue('id', $id, $this->htmlOptions);
        TbArray::defaultValue('name', $name, $this->htmlOptions);

        // by using TbHtml we support all bootstrap options
        if ($this->hasModel()) {
            echo TbHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo TbHtml::textField($name, $this->value, $this->htmlOptions);
        }
    }

    /**
     * Register required scripts.
     */
    public function registerClientScript()
    {
        /** @var TbApi $api */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());
        $this->registerPlugin(TbApi::PLUGIN_TYPEAHEAD, $selector, $this->pluginOptions);
    }
}