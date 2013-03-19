<?php
/**
 * TbPickerColumn
 *
 * The TbPickerColumn works with TbJsonGridView and allows you to create a column that will display a picker element
 * The picker is a special plugin that renders a dropdown on click, which contents can be dynamically updated.
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 9/12/12
 * Time: 3:47 PM
 */
Yii::import('bootstrap.widgets.TbDataColumn');

class TbPickerColumn extends TbDataColumn
{
	/**
	 * @var string $class the class name to use to display picker
	 */
	public $class = 'bootstrap-picker';

	/**
	 * @var array $pickerOptions the javascript options for the picker bootstrap plugin. The picker bootstrap plugin
	 * extends from the tooltip plugin.
	 *
	 * Note that picker has also a 'width' just in case we display AJAX'ed content.
	 *
	 * @see http://twitter.github.com/bootstrap/javascript.html#tooltips
	 */
	public $pickerOptions = array();

	/**
	 * Initialization function
	 */
	public function init()
	{
		if (!$this->class)
			$this->class = 'bootstrap-picker';
		$this->registerClientScript();
	}

	/**
	 * Renders a data cell content, wrapping the value with the link that will activate the picker
	 * @param int $row
	 * @param mixed $data
	 */
	public function renderDataCellContent($row, $data)
	{

		if ($this->value !== null)
			$value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row));
		else if ($this->name !== null)
			$value = CHtml::value($data, $this->name);

		$class = preg_replace('/\s+/', '.', $this->class);
		$value = $value === null ? $this->grid->nullDisplay : $this->grid->getFormatter()->format($value, $this->type);
		$value = CHtml::link($value, '#', array('class' => $class));

		echo $value;
	}

	/**
	 * Registers client script data
	 */
	public function registerClientScript()
	{

		$class = preg_replace('/\s+/', '.', $this->class);

		$cs = Yii::app()->getClientScript();
		$assetsUrl = Yii::app()->bootstrap->getAssetsUrl();

		$cs->registerCssFile($assetsUrl . '/css/bootstrap-picker.css');
		$cs->registerScriptFile($assetsUrl . '/js/bootstrap.picker.js');
		$cs->registerScript(__CLASS__ . '#' . $this->id, "$(document).on('click','#{$this->grid->id} a.{$class}', function(){
			if($(this).hasClass('pickeron'))
			{
				$(this).removeClass('pickeron').picker('toggle');
				return;
			}
			$('#{$this->grid->id} a.pickeron').removeClass('pickeron').picker('toggle');
			$(this).picker(" . CJavaScript::encode($this->pickerOptions) . ").picker('toggle').addClass('pickeron'); return false;
		})");
	}
}