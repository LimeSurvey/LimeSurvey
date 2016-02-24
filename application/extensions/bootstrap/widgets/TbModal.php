<?php
/**
 * TbModal class file.
 * @author Antonio Ramirez <ramirez.cobos@gmail.com>
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @author Eric Nishio <eric.nishio@nordsoftware.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap modal widget.
 */
class TbModal extends CWidget
{
    /**
     * @var array the HTML options for the view container tag.
     */
    public $htmlOptions = array();

    /**
     * @var array  The additional HTML attributes of the button that will show the modal. If empty array, only
     * the markup of the modal will be rendered on the page, so users can easily call the modal manually with their own
     * scripts. The following special attributes are available:
     * <ul>
     *    <li>label: string, the label of the button</li>
     * </ul>
     *
     * For available options of the button trigger, see http://twitter.github.com/bootstrap/javascript.html#modals.
     */
    public $buttonOptions = array();

    /**
     * @var boolean indicates whether the modal should use transitions. Defaults to 'true'.
     */
    public $fade = true;

    /**
     * @var string sets what size the modal should have based on the modal-sm or modal-lg classes mentioned in bootstrap docs since 3.1.1. Defaults to '', meaning that no new class will be added.
     */
    public $size = TbHtml::MODAL_SIZE_DEFAULT;

    /**
     * @var bool $keyboard, closes the modal when escape key is pressed.
     */
    public $keyboard = true;

    /**
     * @var bool $show, shows the modal when initialized.
     */
    public $show = false;

    /**
     * @var mixed includes a modal-backdrop element. Alternatively, specify `static` for a backdrop which doesn't close
     * the modal on click.
     */
    public $backdrop = true;

    /**
     * @var mixed the remote url. If a remote url is provided, content will be loaded via jQuery's load method and
     * injected into the .modal-body of the modal.
     */
    public $remote;

    /**
     * @var string a javascript function that will be invoked immediately when the `show` instance method is called.
     */
    public $onShow;

    /**
     * @var string a javascript function that will be invoked when the modal has been made visible to the user
     *     (will wait for css transitions to complete).
     */
    public $onShown;

    /**
     * @var string a javascript function that will be invoked immediately when the hide instance method has been called.
     */
    public $onHide;

    /**
     * @var string a javascript function that will be invoked when the modal has finished being hidden from the user
     *     (will wait for css transitions to complete).
     */
    public $onHidden;

    /**
     * @var string[] the Javascript event handlers.
     */
    protected $events = array();

    /**
     * @var array $options the plugin options.
     */
    protected $options = array();

    /**
     * @var string
     */
    public $closeText = TbHtml::CLOSE_TEXT;

    /**
     * @var string header content
     */
    public $header;

    /**
     * @var string body of modal
     */
    public $content;

    /**
     * @var string footer content
     */
    public $footer;

    /**
     * Widget's initialization method
     */
    public function init()
    {
        $this->attachBehavior('TbWidget', new TbWidget);

        TbArray::defaultValue('id', $this->getId(), $this->htmlOptions);
        TbArray::defaultValue('role', 'dialog', $this->htmlOptions);
        TbArray::defaultValue('tabindex', '-1', $this->htmlOptions);

        TbHtml::addCssClass('modal', $this->htmlOptions);
        if ($this->fade) {
            TbHtml::addCssClass('fade', $this->htmlOptions);
        }

        if (is_array($this->footer)) {
            $this->footer = implode('&nbsp;', $this->footer);
        }

        $this->initOptions();
        $this->initEvents();

        echo TbHtml::openTag('div', $this->htmlOptions) . PHP_EOL;
        echo TbHtml::openTag('div', array('class' => 'modal-dialog' . $this->size)) . PHP_EOL;
        echo TbHtml::openTag('div', array('class' => 'modal-content')) . PHP_EOL;
        echo TbHtml::modalHeader($this->header);

        if (!isset($this->content)) {
            ob_start();
        }
    }

    /**
     * Initialize events if any
     */
    public function initEvents()
    {
        foreach (array('onShow', 'onShown', 'onHide', 'onHidden') as $event) {
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
     * Initialize plugin options.
     * ***Important***: The display of the button overrides the initialization of the modal bootstrap widget.
     */
    public function initOptions()
    {
        if ($remote = TbArray::popValue('remote', $this->options)) {
            $this->options['remote'] = CHtml::normalizeUrl($remote);
        }

        TbArray::defaultValue('backdrop', $this->backdrop, $this->options);
        TbArray::defaultValue('keyboard', $this->keyboard, $this->options);
        TbArray::defaultValue('show', $this->show, $this->options);
    }

    /**
     * Widget's run method
     */
    public function run()
    {
        if (!isset($this->content)) {
            $this->content = ob_get_clean();
        }
        echo TbHtml::modalBody($this->content);
        echo TbHtml::modalFooter($this->footer);
        echo '</div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
        $this->renderButton();
        $this->registerClientScript();
    }

    /**
     * Renders the button
     */
    public function renderButton()
    {
        if (!empty($this->buttonOptions) && is_array($this->buttonOptions)) {
            TbArray::defaultValue('data-toggle', 'modal', $this->buttonOptions);

            if ($this->remote !== null) {
                $this->buttonOptions['data-remote'] = CHtml::normalizeUrl($this->remote);
            }

            $selector = '#' . $this->htmlOptions['id'];
            $label = TbArray::popValue('label', $this->buttonOptions, 'button');
            $attr = isset($this->buttonOptions['data-remote']) ? 'data-target' : 'href';
            TbArray::defaultValue($attr, $selector, $this->buttonOptions);
            echo TbHtml::button($label, $this->buttonOptions);
        }
    }

    /**
     * Registers necessary client scripts.
     */
    public function registerClientScript()
    {
        $selector = '#' . $this->htmlOptions['id'];

        // do we render a button? If so, bootstrap will handle its behavior through its
        // mark-up, otherwise, register the plugin.
        if (empty($this->buttonOptions)) {
            $this->registerPlugin(TbApi::PLUGIN_MODAL, $selector, $this->options);
        }

        $this->registerEvents($selector, $this->events);
    }
}