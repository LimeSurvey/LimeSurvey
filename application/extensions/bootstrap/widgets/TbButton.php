<?php
/**
 * TbButton class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 * @since 0.9.10
 */

/**
 * Bootstrap button widget.
 * @see http://twitter.github.com/bootstrap/base-css.html#buttons
 */
class TbButton extends CWidget
{
	// Button callback types.
	const BUTTON_LINK = 'link';
	const BUTTON_BUTTON = 'button';
	const BUTTON_SUBMIT = 'submit';
	const BUTTON_SUBMITLINK = 'submitLink';
	const BUTTON_RESET = 'reset';
	const BUTTON_AJAXLINK = 'ajaxLink';
	const BUTTON_AJAXBUTTON = 'ajaxButton';
	const BUTTON_AJAXSUBMIT = 'ajaxSubmit';

	// Button types.
	const TYPE_PRIMARY = 'primary';
	const TYPE_INFO = 'info';
	const TYPE_SUCCESS = 'success';
	const TYPE_WARNING = 'warning';
	const TYPE_DANGER = 'danger';
	const TYPE_INVERSE = 'inverse';
	const TYPE_LINK = 'link';

	// Button sizes.
	const SIZE_MINI = 'mini';
	const SIZE_SMALL = 'small';
	const SIZE_LARGE = 'large';

	/**
	 * @var string the button callback types.
	 * Valid values are 'link', 'button', 'submit', 'submitLink', 'reset', 'ajaxLink', 'ajaxButton' and 'ajaxSubmit'.
	 */
	public $buttonType = self::BUTTON_LINK;
	/**
	 * @var string the button type.
	 * Valid values are 'primary', 'info', 'success', 'warning', 'danger' and 'inverse'.
	 */
	public $type;
	/**
	 * @var string the button size.
	 * Valid values are 'large', 'small' and 'mini'.
	 */
	public $size;
	/**
	 * @var string the button icon, e.g. 'ok' or 'remove white'.
	 */
	public $icon;
	/**
	 * @var string the button label.
	 */
	public $label;
	/**
	 * @var string the button URL.
	 */
	public $url;
	/**
	 * @var boolean indicates whether the button should span the full width of the a parent.
	 */
	public $block = false;
	/**
	 * @var boolean indicates whether the button is active.
	 */
	public $active = false;
	/**
	 * @var boolean indicates whether the button is disabled.
	 */
	public $disabled = false;
	/**
	 * @var boolean indicates whether to encode the label.
	 */
	public $encodeLabel = true;
	/**
	 * @var boolean indicates whether to enable toggle.
	 */
	public $toggle;
	/**
	 * @var string the loading text.
	 */
	public $loadingText;
	/**
	 * @var string the complete text.
	 */
	public $completeText;
	/**
	* @var array the dropdown button items.
	*/
	public $items;
	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();
	/**
	 * @var array the button ajax options (used by 'ajaxLink' and 'ajaxButton').
	 */
	public $ajaxOptions = array();
	/**
	 * @var array the HTML attributes for the dropdown menu.
	 * @since 0.9.11
	 */
	public $dropdownOptions = array();

	/**
	 * @var whether the button is visible or not
	 * @since 0.9.11
	 */
	public $visible = true;

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		$classes = array('btn');

		$validTypes = array(self::TYPE_LINK, self::TYPE_PRIMARY, self::TYPE_INFO, self::TYPE_SUCCESS,
				self::TYPE_WARNING, self::TYPE_DANGER, self::TYPE_INVERSE);

		if (isset($this->type) && in_array($this->type, $validTypes))
			$classes[] = 'btn-'.$this->type;

		$validSizes = array(self::SIZE_LARGE, self::SIZE_SMALL, self::SIZE_MINI);

		if (isset($this->size) && in_array($this->size, $validSizes))
			$classes[] = 'btn-'.$this->size;

		if ($this->block)
			$classes[] = 'btn-block';

		if ($this->active)
			$classes[] = 'active';

		if ($this->disabled)
			$classes[] = 'disabled';

		if ($this->encodeLabel)
			$this->label = CHtml::encode($this->label);

		if ($this->hasDropdown())
		{
			if (!isset($this->url))
				$this->url = '#';

			$classes[] = 'dropdown-toggle';
			$this->label .= ' <span class="caret"></span>';
			$this->htmlOptions['data-toggle'] = 'dropdown';
		}

		if (!empty($classes))
		{
			$classes = implode(' ', $classes);
			if (isset($this->htmlOptions['class']))
				$this->htmlOptions['class'] .= ' '.$classes;
			else
				$this->htmlOptions['class'] = $classes;
		}

		if (isset($this->icon))
		{
			if (strpos($this->icon, 'icon') === false)
				$this->icon = 'icon-'.implode(' icon-', explode(' ', $this->icon));

			$this->label = '<i class="'.$this->icon.'"></i> '.$this->label;
		}

		if(!isset($this->htmlOptions['id']))
		{
			$this->htmlOptions['id'] = $this->getId();
		}

		if (isset($this->toggle))
			$this->htmlOptions['data-toggle'] = 'button';

		if (isset($this->loadingText))
			$this->htmlOptions['data-loading-text'] = $this->loadingText;

		if (isset($this->completeText))
			$this->htmlOptions['data-complete-text'] = $this->completeText;
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		if(!$this->visible)
				return false;
		echo $this->createButton();

		if ($this->hasDropdown())
		{
			$this->controller->widget('bootstrap.widgets.TbDropdown', array(
				'encodeLabel'=>$this->encodeLabel,
				'items'=>$this->items,
				'htmlOptions'=>$this->dropdownOptions,
			));
		}
	}

	/**
	 * Creates the button element.
	 * @return string the created button.
	 */
	protected function createButton()
	{
		switch ($this->buttonType)
		{
			case self::BUTTON_BUTTON:
				return CHtml::htmlButton($this->label, $this->htmlOptions);

			case self::BUTTON_SUBMIT:
				$this->htmlOptions['type'] = 'submit';
				return CHtml::htmlButton($this->label, $this->htmlOptions);

			case self::BUTTON_RESET:
				$this->htmlOptions['type'] = 'reset';
				return CHtml::htmlButton($this->label, $this->htmlOptions);

			case self::BUTTON_SUBMITLINK:
				return CHtml::linkButton($this->label, $this->htmlOptions);

			case self::BUTTON_AJAXLINK:
				return CHtml::ajaxLink($this->label, $this->url, $this->ajaxOptions, $this->htmlOptions);

			case self::BUTTON_AJAXBUTTON:
				$this->ajaxOptions['url'] = $this->url;
				$this->htmlOptions['ajax'] = $this->ajaxOptions;
				return CHtml::htmlButton($this->label, $this->htmlOptions);

			case self::BUTTON_AJAXSUBMIT:
				$this->ajaxOptions['type'] = 'POST';
				$this->ajaxOptions['url'] = $this->url;
				$this->htmlOptions['type'] = 'submit';
				$this->htmlOptions['ajax'] = $this->ajaxOptions;
				return CHtml::htmlButton($this->label, $this->htmlOptions);

			default:
			case self::BUTTON_LINK:
				return CHtml::link($this->label, $this->url, $this->htmlOptions);
		}
	}

	/**
	 * Returns whether the button has a dropdown.
	 * @return bool the result.
	 */
	protected function hasDropdown()
	{
		return isset($this->items) && !empty($this->items);
	}
}
