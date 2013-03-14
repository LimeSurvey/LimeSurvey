<?php

/**
 * TbColorPicker widget class
 *
 * @author: yiqing95 <yiqing_95@qq.com>
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 * ------------------------------------------------------------------------
 *   in yii  use this to register the necessary js and css files :
 *   <?php  $this->widget('bootstrap.widgets.TbColorPicker', array( )); ?>
 *   and the rest usage you'd better refer the original plugin
 *
 * @see http://www.eyecon.ro/bootstrap-colorpicker/
 * ------------------------------------------------------------------------
 *
 * - Changelog
 * @since 10/27/12 7:28 PM
 * @author Antonio Ramirez <antonio@clevertech.biz>
 * Total refactor to work as a widget instead of a class and allow the use of TbActiveForm
 *
 */
class TbColorPicker extends CInputWidget
{

	/**
	 * @var TbActiveForm when created via TbActiveForm, this attribute is set to the form that renders the widget
	 * @see TbActionForm->inputRow
	 */
	public $form;

	/**
	 * @var string the color format - hex | rgb | rgba. Defaults to 'hex'
	 */
	public $format = 'hex';
	/**
	 * @var string[] the JavaScript event handlers.
	 * @see http://www.eyecon.ro/bootstrap-colorpicker/ events section
	 *  show    This event fires immediately when the color picker is displayed.
	 *  hide    This event is fired immediately when the color picker is hidden.
	 *  changeColor    This event is fired when the color is changed.
	 *
	 * <pre>
	 *  'events'=>array(
	 *      'changeColor'=>'js:function(ev){
	 *          console.log(ev.color.toHex());
	 *      }',
	 *      'hide'=>'js:function(ev){
	 *    	console.log("I am hidden!");
	 *   }')
	 * </pre>
	 */
	public $events = array();

	/**
	 * Widget's run function
	 */
	public function run()
	{
		list($name, $id) = $this->resolveNameID();

		$this->registerClientScript($id);

		$this->htmlOptions['id'] = $id;

		// Do we have a model?
		if ($this->hasModel())
		{
			if ($this->form)
				echo $this->form->textField($this->model, $this->attribute, $this->htmlOptions);
			else
				echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
		} else
		{
			echo  CHtml::textField($name, $this->value, $this->htmlOptions);
		}
	}

	/**
	 * Registers required
	 * @param $id
	 */
	public function registerClientScript($id)
	{
		Yii::app()->bootstrap->registerAssetJs('bootstrap.colorpicker.js', CClientScript::POS_HEAD);
		Yii::app()->bootstrap->registerAssetCss('bootstrap-colorpicker.css');

		$options = !empty($this->format) ? CJavaScript::encode(array('format' => $this->format)) : '';

		ob_start();
		echo "jQuery('#{$id}').colorpicker({$options})";
		foreach ($this->events as $event => $handler)
			echo ".on('{$event}', " . CJavaScript::encode($handler) . ")";

		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), ob_get_clean() . ';');
	}
}
