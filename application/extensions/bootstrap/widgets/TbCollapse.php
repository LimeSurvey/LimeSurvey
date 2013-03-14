<?php
/**
 * TbCollapse class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 * @since 1.0.0
 */

/**
 * Bootstrap collapse widget.
 * @see http://twitter.github.com/bootstrap/javascript.html#collapse
 */
class TbCollapse extends CWidget
{
	/**
	 * @var string the name of the collapse element. Defaults to 'a'.
	 */
	public $tagName = 'div';
	/**
	 * @var boolean the CSS selector for element to collapse. Default to 'false'.
	 */
	public $parent = false;
	/**
	 * @var boolean indicates whether to toggle the collapsible element on invocation.
	 */
	public $toggle = true;
	/**
	 * @var array the options for the Bootstrap Javascript plugin.
	 */
	public $options = array();
	/**
	 * @var string[] the Javascript event handlers.
	 */
	public $events = array();
	/**
	* @var array the HTML attributes for the widget container.
	*/
	public $htmlOptions = array();

	private static $_containerId = 0;

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		if (!isset($this->htmlOptions['id']))
			$this->htmlOptions['id'] = $this->getId();

		if (isset($this->parent) && !isset($this->options['parent']))
			$this->options['parent'] = $this->parent;

		if (isset($this->toggle) && !isset($this->options['toggle']))
			$this->options['toggle'] = $this->toggle;

		echo CHtml::openTag($this->tagName, $this->htmlOptions);
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		$id = $this->htmlOptions['id'];

		echo CHtml::closeTag($this->tagName);

		/** @var CClientScript $cs */
		$cs = Yii::app()->getClientScript();
		$options = !empty($this->options) ? CJavaScript::encode($this->options) : '';
		$cs->registerScript(__CLASS__.'#'.$id, "jQuery('#{$id}').collapse({$options});");

		foreach ($this->events as $name => $handler)
		{
			$handler = CJavaScript::encode($handler);
			$cs->registerScript(__CLASS__.'#'.$id.'_'.$name, "jQuery('#{$id}').on('{$name}', {$handler});");
		}
	}

	/**
	 * Returns the next collapse container ID.
	 * @return string the id
	 * @static
	 */
	public static function getNextContainerId()
	{
		return 'collapse_'.self::$_containerId++;
	}
}

