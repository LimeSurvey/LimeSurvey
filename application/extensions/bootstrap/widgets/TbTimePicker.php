<?php
/**
 * TbTimePicker class file.
 * @since 1.0.3
 * @see http://jdewit.github.com/bootstrap-timepicker/
 */

/**
 * TbTimePicker widget.
 */
class TbTimePicker extends CInputWidget
{
	public $form;

	/**
	 * @var array the options for the Bootstrap JavaScript plugin.
	 * Available options:
	 * template	string
	 *      'dropdown' (default), Show picker in a dropdown
	 *      'modal', Show picker in a modal
	 *      false, Don't show a widget
	 * minuteStep	integer	15	Specify a step for the minute field.
	 * showSeconds	boolean	false	Show the seconds field.
	 * secondStep	integer	15	Specify a step for the second field.
	 * defaultTime	string
	 *      'current' (default) Set to the current time.
	 *      'value' Set to inputs current value
	 *      false	Do not set a default time
	 * showMeridian	boolean
	 *      true (default)  12hr mode
	 *      false24hr mode
	 * showInputs	boolean
	 *      true (default )Shows the text inputs in the widget.
	 *      false Hide the text inputs in the widget
	 * disableFocus	boolean	false	Disables the input from focusing. This is useful for touch screen devices that
	 *          display a keyboard on input focus.
	 * modalBackdrop	boolean	false	Show modal backdrop.
	 */
	public $options = array();

	/**
	 * @var string[] the JavaScript event handlers.
	 */
	public $events = array();

	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		list($name, $id) = $this->resolveNameID();

		if ($this->hasModel())
		{
			if($this->form)
				echo $this->form->textField($this->model, $this->attribute, $this->htmlOptions);
			else
				echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
		} else
			echo CHtml::textField($name, $this->value, $this->htmlOptions);

		$this->registerClientScript($id);

	}

	/**
	 * Registers required javascript files
	 * @param $id
	 */
	public function registerClientScript($id)
	{
		Yii::app()->bootstrap->registerAssetCss('bootstrap-timepicker.css');
		Yii::app()->bootstrap->registerAssetJs('bootstrap.timepicker.js');

		$options = !empty($this->options) ? CJavaScript::encode($this->options) : '';

		ob_start();

		echo "jQuery('#{$id}').timepicker({$options})";
		foreach ($this->events as $event => $handler)
			echo ".on('{$event}', " . CJavaScript::encode($handler) . ")";

		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, ob_get_clean() . ';');
	}
}