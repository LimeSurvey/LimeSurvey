<?php
/**
 * TbAffix class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap affix widget.
 * @see http://twitter.github.com/bootstrap/javascript.html#affix
 */
class TbAffix extends CWidget
{
    /**
     * @var string the HTML tag for the container.
     */
    public $tagName = 'div';
    /**
     * @var mixed pixels to offset from screen when calculating position of scroll.
     */
    public $offset;
    /**
     * @var array the HTML attributes for the container.
     */
    public $htmlOptions = array();

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->attachBehavior('TbWidget', new TbWidget);
        $this->copyId();
        $this->htmlOptions['data-spy'] = 'affix';
        if (isset($this->offset)) {
            if (is_string($this->offset)) {
                $this->offset = array('top', $this->offset);
            }

            if (is_array($this->offset) && count($this->offset) === 2) {
                list($position, $offset) = $this->offset;
                $this->htmlOptions['data-offset-' . $position] = $offset;
            }
        }
        echo TbHtml::openTag($this->tagName, $this->htmlOptions);
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        echo CHtml::closeTag($this->tagName);
    }
}