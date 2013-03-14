<?php
/**
 * TbBadge class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright  Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap badge widget.
 * @see http://twitter.github.com/bootstrap/components.html#badges
 */
class TbBadge extends CWidget
{
	// Badge types.
	const TYPE_SUCCESS = 'success';
	const TYPE_WARNING = 'warning';
	const TYPE_IMPORTANT = 'important';
	const TYPE_INFO = 'info';
	const TYPE_INVERSE = 'inverse';

	/**
	 * @var string the badge type.
	 * Valid types are 'success', 'warning', 'important', 'info' and 'inverse'.
	 */
	public $type;
	/**
	 * @var string the badge text.
	 */
	public $label;
	/**
	 * @var boolean whether to encode the label.
	 */
	public $encodeLabel = true;
	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		$classes = array('badge');

		$validTypes = array(self::TYPE_SUCCESS, self::TYPE_WARNING, self::TYPE_IMPORTANT, self::TYPE_INFO, self::TYPE_INVERSE);

		if (isset($this->type) && in_array($this->type, $validTypes))
			$classes[] = 'badge-'.$this->type;

		if (!empty($classes))
		{
			$classes = implode(' ', $classes);
			if (isset($this->htmlOptions['class']))
				$this->htmlOptions['class'] .= ' '.$classes;
			else
				$this->htmlOptions['class'] = $classes;
		}

		if ($this->encodeLabel === true)
			$this->label = CHtml::encode($this->label);
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		echo CHtml::tag('span', $this->htmlOptions, $this->label);
	}
}
