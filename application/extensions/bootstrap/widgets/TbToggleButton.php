<?php
/**
 * TbToggleButton.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 10/19/12
 * Time: 7:00 PM
 */
class TbToggleButton extends CInputWidget
{
	/**
	 * @var TbActiveForm when created via TbActiveForm, this attribute is set to the form that renders the widget
	 * @see TbActionForm->inputRow
	 */
	public $form;
	/**
	 * @var string the javascript function
	 *
	 * The function signature is <code>function($el, status, e)</code>
	 * <ul>
	 * <li><code>$el</code> the toggle element changed. </li>
	 * <li><code>status</code> the status of the element (true=on | false=off) </li>
	 * <li><code>e</code> the event object </li>
	 * </ul>
	 *
	 * Example:
	 * <pre>
	 *  array(
	 *     class'=>'TbToggleColumn',
	 *     'onChange'=>'js:function($el, status, e){ console.log($el, status, e); }',
	 *  ),
	 * </pre>
	 */
	public $onChange;

	/**
	 * @var int the width of the toggle button
	 */
	public $width = 100;

	/**
	 * @var int the height of the toggle button
	 */
	public $height = 25;

	/**
	 * @var bool whether to use animation or not
	 */
	public $animated = true;

	/**
	 * @var mixed the transition speed (toggle movement)
	 */
	public $transitionSpeed; //accepted values: float or percent [1, 0.5, '150%']

	/**
	 * @var string the label to display on the enabled side
	 */
	public $enabledLabel = 'ON';

	/**
	 * @var string the label to display on the disabled side
	 */
	public $disabledLabel = 'OFF';
	/**
	 * @var string the style of the toggle button enable style
	 * Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
	 */
	public $enabledStyle = 'primary';

	/**
	 * @var string the style of the toggle button disabled style
	 * Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
	 */
	public $disabledStyle = null;

	/**
	 * @var array a custom style for the enabled option. Format
	 * <pre>
	 *  ...
	 *  'customEnabledStyle'=>array(
	 *      'background'=>'#FF00FF',
	 *      'gradient'=>'#D300D3',
	 *      'color'=>'#FFFFFF'
	 *  ),
	 *  ...
	 * </pre>
	 */
	public $customEnabledStyle = array();

	/**
	 * @var array a custom style for the disabled option. Format
	 * <pre>
	 *  ...
	 *  'customDisabledStyle'=>array(
	 *      'background'=>'#FF00FF',
	 *      'gradient'=>'#D300D3',
	 *      'color'=>'#FFFFFF'
	 *  ),
	 *  ...
	 * </pre>
	 */
	public $customDisabledStyle = array();

	/**
	 * Widget's run function
	 */
	public function run()
	{
		list($name, $id) = $this->resolveNameID();

		echo CHtml::openTag('div', array('id'=>'wrapper-'.$id));

		if ($this->hasModel())
		{
			if($this->form)
				echo $this->form->checkBox($this->model, $this->attribute, $this->htmlOptions);
			else
				echo CHtml::activeCheckBox($this->model, $this->attribute, $this->htmlOptions);

		} else
			echo CHtml::checkBox($name, $this->value, $this->htmlOptions);

		echo '</div>';

		$this->registerClientScript($id);
	}

	/**
	 * Registers required css and js files
	 * @param $id the id of the toggle button
	 */
	protected function registerClientScript($id)
	{
		$cs = Yii::app()->clientScript;
		$cs->registerCoreScript('jquery');
		Yii::app()->bootstrap->registerAssetCss('bootstrap-toggle-buttons.css');
		Yii::app()->bootstrap->registerAssetJs('jquery.toggle.buttons.js');

		$config = CJavaScript::encode($this->getConfiguration());

		$cs->registerScript(__CLASS__.'#'.$this->getId(), "$('#wrapper-{$id}').toggleButtons({$config});");

	}

	/**
	 * @return array the configuration of the plugin
	 */
	protected function getConfiguration()
	{
		if($this->onChange!==null)
		{
			if((!$this->onChange instanceof CJavaScriptExpression) && strpos($this->onChange,'js:')!==0)
			{
				$onChange=new CJavaScriptExpression($this->onChange);
			}
			else
			{
				$onChange=$this->onChange;
			}
		}
		else
		{
			$onChange = 'js:$.noop';
		}

		$config = array(
		   'onChange' => $onChange,
			'width' => $this->width,
			'height' => $this->height,
			'animated' => $this->animated,
			'transitionSpeed' => $this->transitionSpeed,
			'label' => array(
				'enabled' => $this->enabledLabel,
				'disabled' => $this->disabledLabel
			),
			'style' => array()
		);
		if(!empty($this->enabledStyle))
		{
			$config['style']['enabled'] = $this->enabledStyle;
		}
		if(!empty($this->disabledStyle))
		{
			$config['style']['disabled'] = $this->disabledStyle;
		}
		if(!empty($this->customEnabledStyle))
		{
			$config['style']['custom']= array('enabled'=>$this->customEnabledStyle);
		}
		if(!empty($this->customDisabledStyle))
		{
			if(isset($config['style']['custom']))
				$config['style']['custom']['disabled'] = $this->customDisabledStyle;
			else
				$config['style']['custom'] = array('disabled'=>$this->customDisabledStyle);
		}
		foreach($config as $key=>$element)
		{
			if(empty($element))
				unset($config[$key]);
		}
		return $config;
	}
}