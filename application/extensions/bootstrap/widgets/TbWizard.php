<?php
/**
 * TbWizard class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @copyright Copyright &copy; Vincent Gabriel 2012
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

Yii::import('bootstrap.widgets.TbMenu');

/**
 * Bootstrap Javascript tabs widget.
 * @see http://twitter.github.com/bootstrap/javascript.html#tabs
 */
class TbWizard extends CWidget
{
	// Tab placements.
	const PLACEMENT_ABOVE = 'above';
	const PLACEMENT_BELOW = 'below';
	const PLACEMENT_LEFT = 'left';
	const PLACEMENT_RIGHT = 'right';

	/**
	 * @var string the type of tabs to display. Defaults to 'tabs'. Valid values are 'tabs' and 'pills'.
	 * Please not that Javascript pills are not fully supported in Bootstrap yet!
	 * @see TbMenu::$type
	 */
	public $type = TbMenu::TYPE_TABS;
	/**
	 * @var string the placement of the tabs.
	 * Valid values are 'above', 'below', 'left' and 'right'.
	 */
	public $placement;
	/**
	 * @var array the tab configuration.
	 */
	public $tabs = array();

	/**
	 * @var boolean indicates whether to stack navigation items.
	 */
	public $stacked = false;
	/**
	/**
	 * @var boolean whether to encode item labels.
	 */
	public $encodeLabel = true;
	/**
	 * @var string[] the Javascript event handlers.
	 */
	public $events = array();
	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();
	/**
	 * @var array the JS options for the bootstrap wizard plugin
	 */
	public $options = array();
	
	/**
	 * @var boolean Add tabs navbar to the main tab navigation
	 */
	public $addTabsNavBar = false;
	
	/**
	 * @var string Pager HTML code
	 */
	public $pagerContent = '<ul class="pager wizard">
				<li class="previous first" style="display:none;"><a href="#">First</a></li>
				<li class="previous"><a href="#">Previous</a></li>
				<li class="next last" style="display:none;"><a href="#">Last</a></li>
			  	<li class="next"><a href="#">Next</a></li>
			</ul>';
	
	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		if (!isset($this->htmlOptions['id']))
			$this->htmlOptions['id'] = $this->getId();

		$classes = array();

		$validPlacements = array(self::PLACEMENT_ABOVE, self::PLACEMENT_BELOW, self::PLACEMENT_LEFT, self::PLACEMENT_RIGHT);

		if (isset($this->placement) && in_array($this->placement, $validPlacements))
			$classes[] = 'tabs-'.$this->placement;

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
	 * Run this widget.
	 */
	public function run()
	{
		$id = $this->id;
		$content = array();
		$items = $this->normalizeTabs($this->tabs, $content);

		ob_start();
		if($this->addTabsNavBar) {
			echo '<div class="navbar"><div class="navbar-inner">';
		}
		$this->controller->widget('bootstrap.widgets.TbMenu', array(
			'stacked'=>$this->stacked,
			'type'=>$this->type,
			'encodeLabel'=>$this->encodeLabel,
			'items'=>$items,
		));
		if($this->addTabsNavBar) {
			echo '</div></div>';
		}
		$tabs = ob_get_clean();

		ob_start();
		echo '<div class="tab-content">';
		echo implode('', $content);
		echo $this->pagerContent;
		echo '</div>';
		$content = ob_get_clean();

		echo CHtml::openTag('div', $this->htmlOptions);
		echo $this->placement === self::PLACEMENT_BELOW ? $content.$tabs : $tabs.$content;
		echo '</div>';

		/** @var CClientScript $cs */
		$cs = Yii::app()->getClientScript();
		Yii::app()->bootstrap->registerAssetJs('jquery.bootstrap.wizard.js');
		// Override options
		if($this->type && !isset($this->options['class'])) {
			$this->options['class'] = $this->type;
		}
		
		$options = CJavaScript::encode($this->options);
		
		$cs->registerScript(__CLASS__.'#'.$id, "jQuery('#{$id}').bootstrapWizard({$options});");

		foreach ($this->events as $name => $handler)
		{
			$handler = CJavaScript::encode($handler);
			$cs->registerScript(__CLASS__.'#'.$id.'_'.$name, "jQuery('#{$id}').on('{$name}', {$handler});");
		}
	}

	/**
	 * Normalizes the tab configuration.
	 * @param array $tabs the tab configuration
	 * @param array $panes a reference to the panes array
	 * @param integer $i the current index
	 * @return array the items
	 */
	protected function normalizeTabs($tabs, &$panes, &$i = 0)
	{
		$id = $this->getId();
		$items = array();

		foreach ($tabs as $tab)
		{
			$item = $tab;

			if (isset($item['visible']) && $item['visible'] === false)
				continue;

			if (!isset($item['itemOptions']))
				$item['itemOptions'] = array();

			$item['linkOptions']['data-toggle'] = 'tab';

			if (isset($tab['items']))
				$item['items'] = $this->normalizeTabs($item['items'], $panes, $i);
			else
			{
				if (!isset($item['id']))
					$item['id'] = $id.'_tab_'.($i + 1);

				$item['url'] = '#'.$item['id'];

				if (!isset($item['content']))
					$item['content'] = '';

				$content = $item['content'];
				unset($item['content']);

				if (!isset($item['paneOptions']))
					$item['paneOptions'] = array();

				$paneOptions = $item['paneOptions'];
				unset($item['paneOptions']);

				$paneOptions['id'] = $item['id'];

				$classes = array('tab-pane fade');

				if (isset($item['active']) && $item['active'])
					$classes[] = 'active in';

				$classes = implode(' ', $classes);
				if (isset($paneOptions['class']))
					$paneOptions['class'] .= ' '.$classes;
				else
					$paneOptions['class'] = $classes;

				$panes[] = CHtml::tag('div', $paneOptions, $content);

				$i++; // increment the tab-index
			}

			$items[] = $item;
		}

		return $items;
	}
}
