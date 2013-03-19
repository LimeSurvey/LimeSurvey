<?php
/**
 * TbDropdown class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

Yii::import('bootstrap.widgets.TbBaseMenu');

/**
 * Bootstrap dropdown menu.
 * @see http://twitter.github.com/bootstrap/javascript.html#dropdowns
 */
class TbDropdown extends TbBaseMenu
{
	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();

		if (isset($this->htmlOptions['class']))
			$this->htmlOptions['class'] .= ' dropdown-menu';
		else
			$this->htmlOptions['class'] = 'dropdown-menu';
	}

	/**
	 * Renders the content of a menu item.
	 * Note that the container and the sub-menus are not rendered here.
	 * @param array $item the menu item to be rendered. Please see {@link items} on what data might be in the item.
	 * @return string the rendered item
	 */
	protected function renderMenuItem($item)
	{
		if (isset($item['icon']))
		{
			if (strpos($item['icon'], 'icon') === false)
			{
				$pieces = explode(' ', $item['icon']);
				$item['icon'] = 'icon-'.implode(' icon-', $pieces);
			}

			$item['label'] = '<i class="'.$item['icon'].'"></i> '.$item['label'];
		}

		if (!isset($item['linkOptions']))
			$item['linkOptions'] = array();

		if (isset($item['items']) && !empty($item['items']))
			$item['url'] = '#';

		$item['linkOptions']['tabindex'] = -1;

		if (isset($item['url']))
			return CHtml::link($item['label'], $item['url'], $item['linkOptions']);
		else
			return $item['label'];
	}

	/**
	 * Returns the divider CSS class.
	 * @return string the class name
	 */
	public function getDividerCssClass()
	{
		return 'divider';
	}

	/**
	 * Returns the dropdown css class.
	 * @return string the class name
	 */
	public function getDropdownCssClass()
	{
		return 'dropdown-submenu';
	}

	/**
	 * Returns whether this is a vertical menu.
	 * @return boolean the result
	 */
	public function isVertical()
	{
		return true;
	}
}
