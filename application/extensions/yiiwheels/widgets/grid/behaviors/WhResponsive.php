<?php
/**
 * WhResponsive class
 * Extends WhGridView to provide responsive Tables
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; Antonio Ramirez 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package yiiwheels.widgets.grid.behaviors
 */
class WhResponsive extends CBehavior
{
	/**
	 * @var bool whether to make the grid responsive
	 */
	public $responsiveTable = false;

	/**
	 * Writes responsiveCSS
	 */
	public function writeResponsiveCss($columns, $gridId)
	{
		$cnt = 1;
		$labels = '';
		foreach ($columns as $column) {
			/** @var WhDataColumn $column */
			ob_start();
			$column->renderHeaderCell();
			$name = strip_tags(ob_get_clean());

			$labels .= "#$gridId td:nth-of-type($cnt):before { content: '{$name}'; }\n";
			$cnt++;
		}

		$css = <<<EOD
@media
	only screen and (max-width: 760px),
	(min-device-width: 768px) and (max-device-width: 1024px)  {

		/* Force table to not be like tables anymore */
		#{$gridId} table,#{$gridId} thead,#{$gridId} tbody,#{$gridId} th,#{$gridId} td,#{$gridId} tr {
			display: block;
		}

		/* Hide table headers (but not display: none;, for accessibility) */
		#{$gridId} thead tr {
			position: absolute;
			top: -9999px;
			left: -9999px;
		}

		#{$gridId} tr { border: 1px solid #ccc; }

		#{$gridId} td {
			/* Behave  like a "row" */
			border: none;
			border-bottom: 1px solid #eee;
			position: relative;
			padding-left: 50%;
		}

		#{$gridId} td:before {
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
		Yii::app()->clientScript->registerCss(__CLASS__ . '#' . $gridId, $css);
	}
}