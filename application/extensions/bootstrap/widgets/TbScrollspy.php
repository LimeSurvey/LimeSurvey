<?php
/**
 * TbScrollspy class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap scrollspy widget.
 * @see http://twitter.github.com/bootstrap/javascript.html#scrollspy
 */
class TbScrollspy extends CWidget
{
    /**
     * @var string the CSS selector for the scrollspy element.
     */
    public $selector = 'body';
    /**
     * @var string the CSS selector for the spying element.
     */
    public $target;
    /**
     * @var integer the scroll offset (in pixels).
     */
    public $offset;
    /**
     * @var string[] $events the JavaScript event configuration (name=>handler).
     */
    public $events = array();

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->attachBehavior('TbWidget', new TbWidget);
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        // todo: think of a better way of doing this.
        $script = "jQuery('{$this->selector}').attr('data-spy', 'scroll');";
        if (isset($this->target)) {
            $script .= "jQuery('{$this->selector}').attr('data-target', '{$this->target}');";
        }
        if (isset($this->offset)) {
            $script .= "jQuery('{$this->selector}').attr('data-offset', '{$this->offset}');";
        }
        Yii::app()->clientScript->registerScript($this->getId(), $script, CClientScript::POS_BEGIN);
        $this->registerEvents($this->selector, $this->events);
    }
}
