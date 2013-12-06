<?php
/**
 * TbScrollSpy class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 * @since 1.0.0
 */

/**
 * Bootstrap scrollspy widget.
 * @see http://twitter.github.com/bootstrap/javascript.html#scrollspy
 */
class TbScrollSpy extends CWidget
{
	/**
	 * @var string the CSS selector for the scrollspy element. Defaults to 'body'.
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
	 * @var array string[] the Javascript event handlers.
	 */
	public $events = array();

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		$script = "jQuery('{$this->selector}').attr('data-spy', 'scroll');";

		if (isset($this->target))
			$script .= "jQuery('{$this->selector}').attr('data-target', '{$this->target}');";

		if (isset($this->offset))
			$script .= "jQuery('{$this->selector}').attr('data-offset', '{$this->offset}');";

		/** @var CClientScript $cs */
		$cs = Yii::app()->getClientScript();
		$cs->registerScript(__CLASS__.'#'.$this->selector, $script, CClientScript::POS_BEGIN);

		foreach ($this->events as $name => $handler)
		{
			$handler = CJavaScript::encode($handler);
			$cs->registerScript(__CLASS__.'#'.$this->selector.'_'.$name, "jQuery('{$this->selector}').on('{$name}', {$handler});");
		}
	}
}

