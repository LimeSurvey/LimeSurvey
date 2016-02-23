<?php

/**
 * TbPanel class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap panel widget. Similar to CPortlet.
 * @see http://getbootstrap.com/components/#panels
 * @see http://www.yiiframework.com/doc/api/1.1/CPortlet
 */
class TbPanel extends CWidget
{
    /**
     * @var array the HTML attributes for the panel container.
     */
    public $htmlOptions = array('class' => 'panel panel-default');

    /**
     * @var string the title of the panel. Defaults to null.
     * When this is not set, Decoration will not be displayed.
     * Note that the title will not be HTML-encoded when rendering.
     */
    public $title;

    /**
     * @var string the CSS class for the decoration container tag. Defaults to 'panel-heading'.
     */
    public $decorationCssClass = 'panel-heading';

    /**
     * @var string the CSS class for the content container tag. Defaults to 'panel-body'.
     */
    public $contentCssClass = 'panel-body';

    /**
     * @var boolean whether to render the panel body container or not. Defaults to true.
     */
    public $renderContentContainer = true;

    /**
     * @var string the title of the panel. Defaults to null.
     * When this is not set, Decoration will not be displayed.
     * Note that the title will not be HTML-encoded when rendering.
     */
    public $footer;

    /**
     * @var string the CSS class for the footer container tag. Defaults to 'panel-footer'.
     */
    public $footerCssClass = 'panel-footer';

    /**
     * @var boolean whether to hide the panel when the body content is empty. Defaults to true.
     */
    public $hideOnEmpty = true;

    private $_openTag;

    /**
     * Initializes the widget.
     * This renders the open tags needed by the panel.
     * It also renders the decoration, if any.
     */
    public function init()
    {
        ob_start();
        ob_implicit_flush(false);

        if (isset($this->htmlOptions['id'])) {
            $this->id = $this->htmlOptions['id'];
        } else {
            $this->htmlOptions['id'] = $this->id;
        }
        echo CHtml::openTag('div', $this->htmlOptions) . "\n";
        $this->renderDecoration();
        if ($this->renderContentContainer) {
            echo "<div class=\"{$this->contentCssClass}\">\n";
        }

        $this->_openTag = ob_get_contents();
        ob_clean();
    }

    /**
     * Renders the content of the panel.
     */
    public function run()
    {
        $this->renderContent();
        $content = ob_get_clean();
        if ($this->hideOnEmpty && trim($content) === '') {
            return;
        }
        echo $this->_openTag;
        echo $content;
        if ($this->renderContentContainer) {
            echo "</div>\n";
        }
        $this->renderFooter();
        echo "</div>";
    }

    /**
     * Renders the decoration for the panel.
     * The default implementation will render the title if it is set.
     */
    protected function renderDecoration()
    {
        if ($this->title !== null) {
            echo "<div class=\"{$this->decorationCssClass}\">{$this->title}</div>\n";
        }
    }

    /**
     * Renders the content of the panel.
     * Child classes should override this method to render the actual content.
     */
    protected function renderContent()
    {
    }

    /**
     * Renders the footer for the panel.
     * The default implementation will render the footer attribute if it is set.
     */
    protected function renderFooter()
    {
        if ($this->footer !== null) {
            echo "<div class=\"{$this->footerCssClass}\">{$this->footer}</div>\n";
        }
    }
}
