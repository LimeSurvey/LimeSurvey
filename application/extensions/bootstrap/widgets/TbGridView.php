<?php
/**
 * TbGridView class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

Yii::import('zii.widgets.grid.CGridView');
Yii::import('bootstrap.widgets.TbDataColumn');

/**
 * Bootstrap Zii grid view.
 */
class TbGridView extends CGridView
{
	// Table types.
	const TYPE_STRIPED = 'striped';
	const TYPE_BORDERED = 'bordered';
	const TYPE_CONDENSED = 'condensed';
	const TYPE_HOVER = 'hover';

	/**
	 * @var string|array the table type.
	 * Valid values are 'striped', 'bordered', ' condensed' and/or 'hover'.
	 */
	public $type;
	/**
	 * @var string the CSS class name for the pager container. Defaults to 'pagination'.
	 */
	public $pagerCssClass = 'pagination';
	/**
	 * @var array the configuration for the pager.
	 * Defaults to <code>array('class'=>'ext.bootstrap.widgets.TbPager')</code>.
	 */
	public $pager = array('class'=>'bootstrap.widgets.TbPager');
	/**
	 * @var string the URL of the CSS file used by this grid view.
	 * Defaults to false, meaning that no CSS will be included.
	 */
	public $cssFile = false;

	/**
	 * @var bool whether to make the grid responsive
	 */
	public $responsiveTable = false;

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();

		$classes = array('table');

		if (isset($this->type))
		{
			if (is_string($this->type))
				$this->type = explode(' ', $this->type);

			$validTypes = array(self::TYPE_STRIPED, self::TYPE_BORDERED, self::TYPE_CONDENSED, self::TYPE_HOVER);

			if (!empty($this->type))
			{
				foreach ($this->type as $type)
				{
					if (in_array($type, $validTypes))
						$classes[] = 'table-'.$type;
				}
			}
		}

		if (!empty($classes))
		{
			$classes = implode(' ', $classes);
			if (isset($this->itemsCssClass))
				$this->itemsCssClass .= ' '.$classes;
			else
				$this->itemsCssClass = $classes;
		}

		$popover = Yii::app()->bootstrap->popoverSelector;
		$tooltip = Yii::app()->bootstrap->tooltipSelector;
		
		$afterAjaxUpdate = "js:function() {
			jQuery('.popover').remove();
			jQuery('{$popover}').popover();
			jQuery('.tooltip').remove();
			jQuery('{$tooltip}').tooltip();
		}";

		if (!isset($this->afterAjaxUpdate))
			$this->afterAjaxUpdate = $afterAjaxUpdate;
	}

	/**
	 * Creates column objects and initializes them.
	 */
	protected function initColumns()
	{
		foreach ($this->columns as $i => $column)
		{
			if (is_array($column) && !isset($column['class']))
				$this->columns[$i]['class'] = 'bootstrap.widgets.TbDataColumn';
		}

		parent::initColumns();

		if($this->responsiveTable)
			$this->writeResponsiveCss();
	}

	/**
	 * Creates a column based on a shortcut column specification string.
	 * @param mixed $text the column specification string
	 * @return \TbDataColumn|\CDataColumn the column instance
	 * @throws CException if the column format is incorrect
	 */
	protected function createDataColumn($text)
	{
		if (!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/', $text, $matches))
			throw new CException(Yii::t('zii', 'The column must be specified in the format of "Name:Type:Label", where "Type" and "Label" are optional.'));

		$column = new TbDataColumn($this);
		$column->name = $matches[1];

		if (isset($matches[3]) && $matches[3] !== '')
			$column->type = $matches[3];

		if (isset($matches[5]))
			$column->header = $matches[5];

		return $column;
	}

	/**
	 * Writes responsiveCSS
	 */
	protected function writeResponsiveCss()
	{
		$cnt = 1; $labels='';
		foreach($this->columns as $column)
		{
			ob_start();
			$column->renderHeaderCell();
			$name = strip_tags(ob_get_clean());

			$labels .= "td:nth-of-type($cnt):before { content: '{$name}'; }\n";
			$cnt++;
		}

		$css = <<<EOD
@media
	only screen and (max-width: 760px),
	(min-device-width: 768px) and (max-device-width: 1024px)  {

		/* Force table to not be like tables anymore */
		#{$this->id} table,#{$this->id} thead,#{$this->id} tbody,#{$this->id} th,#{$this->id} td,#{$this->id} tr {
			display: block;
		}

		/* Hide table headers (but not display: none;, for accessibility) */
		#{$this->id} thead tr {
			position: absolute;
			top: -9999px;
			left: -9999px;
		}

		#{$this->id} tr { border: 1px solid #ccc; }

		#{$this->id} td {
			/* Behave  like a "row" */
			border: none;
			border-bottom: 1px solid #eee;
			position: relative;
			padding-left: 50%;
		}

		#{$this->id} td:before {
			/* Now like a table header */
			position: absolute;
			/* Top/left values mimic padding */
			top: 6px;
			left: 6px;
			width: 45%;
			padding-right: 10px;
			white-space: nowrap;
		}
		.grid-view .button-column {
			text-align: left;
			width:auto;
		}
		/*
		Label the data
		*/
		{$labels}
	}
EOD;
		Yii::app()->clientScript->registerCss(__CLASS__.'#'.$this->id, $css);
	}
}
