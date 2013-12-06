<?php
/**
 * TbNavbar class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 * @since 0.9.7
 */

Yii::import('bootstrap.widgets.TbCollapse');

/**
 * Bootstrap navigation bar widget.
 */
class TbNavbar extends CWidget
{
	// Navbar types.
	const TYPE_INVERSE = 'inverse';

	// Navbar fix locations.
	const FIXED_TOP = 'top';
	const FIXED_BOTTOM = 'bottom';

	/**
	 * @var string the navbar type. Valid values are 'inverse'.
	 * @since 1.0.0
	 */
	public $type;
	/**
	 * @var string the text for the brand.
	 */
	public $brand;
	/**
	 * @var string the URL for the brand link.
	 */
	public $brandUrl;
	/**
	 * @var array the HTML attributes for the brand link.
	 */
	public $brandOptions = array();
	/**
	 * @var array navigation items.
	 * @since 0.9.8
	 */
	public $items = array();
	/**
	 * @var mixed fix location of the navbar if applicable.
	 * Valid values are 'top' and 'bottom'. Defaults to 'top'.
	 * Setting the value to false will make the navbar static.
	 * @since 0.9.8
	 */
	public $fixed = self::FIXED_TOP;
	/**
	* @var boolean whether the nav span over the full width. Defaults to false.
	* @since 0.9.8
	*/
	public $fluid = false;
	/**
	 * @var boolean whether to enable collapsing on narrow screens. Default to false.
	 */
	public $collapse = false;
	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		if ($this->brand !== false)
		{
			if (!isset($this->brand))
				$this->brand = CHtml::encode(Yii::app()->name);

			if (!isset($this->brandUrl))
				$this->brandUrl = Yii::app()->homeUrl;

			$this->brandOptions['href'] = CHtml::normalizeUrl($this->brandUrl);

			if (isset($this->brandOptions['class']))
				$this->brandOptions['class'] .= ' brand';
			else
				$this->brandOptions['class'] = 'brand';
		}

		$classes = array('navbar');

		if (isset($this->type) && in_array($this->type, array(self::TYPE_INVERSE)))
			$classes[] = 'navbar-'.$this->type;

		if ($this->fixed !== false && in_array($this->fixed, array(self::FIXED_TOP, self::FIXED_BOTTOM)))
			$classes[] = 'navbar-fixed-'.$this->fixed;

		if (!empty($classes))
		{
			$classes = implode(' ', $classes);
			if (isset($this->htmlOptions['class']))
				$this->htmlOptions['class'] .= ' '.$classes;
			else
				$this->htmlOptions['class'] = $classes;
		}
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		echo CHtml::openTag('div', $this->htmlOptions);
		echo '<div class="navbar-inner"><div class="'.$this->getContainerCssClass().'">';

		$collapseId = TbCollapse::getNextContainerId();

		if ($this->collapse !== false)
		{
			echo '<a class="btn btn-navbar" data-toggle="collapse" data-target="#'.$collapseId.'">';
			echo '<span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>';
			echo '</a>';
		}

		if ($this->brand !== false)
			echo CHtml::openTag('a', $this->brandOptions).$this->brand.'</a>';

		if ($this->collapse !== false)
		{
			$this->controller->beginWidget('bootstrap.widgets.TbCollapse', array(
				'id'=>$collapseId,
				'toggle'=>false, // navbars should be collapsed by default
				'htmlOptions'=>array('class'=>'nav-collapse'),
			));
		}

		foreach ($this->items as $item)
		{
			if (is_string($item))
				echo $item;
			else
			{
				if (isset($item['class']))
				{
					$className = $item['class'];
					unset($item['class']);

					$this->controller->widget($className, $item);
				}
			}
		}

		if ($this->collapse !== false)
			$this->controller->endWidget();

		echo '</div></div></div>';
	}

	/**
	 * Returns the navbar container CSS class.
	 * @return string the class
	 */
	protected function getContainerCssClass()
	{
		return $this->fluid ? 'container-fluid' : 'container';
	}
}
