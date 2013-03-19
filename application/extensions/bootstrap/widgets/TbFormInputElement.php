<?php
/**
 * TbFormInputElement class file.
 *
 * The inputElementClass for TbForm
 *
 * Support for Yii formbuilder
 *
 * @author Joe Blocher <yii@myticket.at>
 * @copyright Copyright &copy; Joe Blocher 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

class TbFormInputElement extends CFormInputElement
{

	/**
	 * Wrap control-group/controls tags around custom types (CInputWidget or CJuiInputWidget)
	 *
	 * @var bool
	 */
	public $wrapBootstrapTags = true;

	/**
	 * Map element->type to TbActiveForm method
	 * @var array this->type => TbActiveForm::method
	 */
	public static $tbActiveFormMethods = array(
		'text' => 'textFieldRow',
		'password' => 'passwordFieldRow',
		'textarea' => 'textAreaRow',
		'file' => 'fileFieldRow',
		'radio' => 'radioButtonRow',
		'checkbox' => 'checkBoxRow',
		'listbox' => 'dropDownListRow',
		'dropdownlist' => 'dropDownListRow',
		'checkboxlist' => 'checkBoxListRow',
		'radiolist' => 'radioButtonListRow',

		//HTML5 types not supported in YiiBooster yet: render as textField
		'url' => 'textFieldRow',
		'email' => 'textFieldRow',
		'number' => 'textFieldRow',

		//'range'=>'activeRangeField', not supported yet
		'date' => 'datepickerRow',

		//new YiiBooster types
		'captcha' => 'captchaRow',
		'daterange' => 'dateRangeRow',
		'redactor' => 'redactorRow',
		'markdowneditor' => 'markdownEditorRow',
		'uneditable' => 'uneditableRow',
		'radiolistinline' => 'radioButtonListInlineRow',
		'checkboxlistinline' => 'checkBoxListInlineRow',
		'select2' => 'select2Row'
	);

	/**
	 * @var array map the htmlOptions input type: not supported by YiiBooster yet
	 */
	public static $htmlOptionTypes = array(
		'url' => 'url',
		'email' => 'email',
		'number' => 'number',
	);

	/**
	 * Get the TbActiveForm instance
	 * @return bool
	 */
	protected function getActiveFormWidget()
	{
		return $this->getParent()->getActiveFormWidget();
	}

	/**
	 * Prepare the htmlOptions before calling the TbActiveForm method
	 *
	 * @param $options
	 * @return mixed
	 */
	protected function prepareHtmlOptions($options)
	{
		if (!empty($this->hint)) //restore hint from config as attribute
			$options['hint'] = $this->hint;

		//HTML5 types not supported in YiiBooster yet
		//should be possible to set type="email", ... in the htmlOptions
		if (array_key_exists($this->type, self::$htmlOptionTypes))
			$options['type'] = self::$htmlOptionTypes[$this->type];

		return $options;
	}

	/**
	 * Render this element using the mapped method from $tbActiveFormMethods
	 */
	public function render()
	{
		if (!empty(self::$tbActiveFormMethods[$this->type]))
		{
			$method = self::$tbActiveFormMethods[$this->type];
			$model = $this->getParent()->getModel();
			$attribute = $this->name;
			$htmlOptions = $this->prepareHtmlOptions($this->attributes);

			switch ($method)
			{
				case 'checkBoxListRow':
				case 'radioButtonListRow':
				case 'dropDownListRow':
				case 'radioButtonListInlineRow':
				case 'checkBoxListInlineRow':
					return $this->getActiveFormWidget()->$method($model, $attribute, $this->items, $htmlOptions);

				default:
					return $this->getActiveFormWidget()->$method($model, $attribute, $htmlOptions);
			}
		} else
			if ($this->wrapBootstrapTags) //wrap tags controls/control-group
			{
				$error = $this->getParent()->showErrorSummary ? '' : $this->renderError();
				$output = array(
					'{label}' => $this->renderControlLabel(),
					'{input}' => "<div class=\"controls\">\n" . $this->renderInput() . $error . $this->renderHint() . '</div>',
					'{hint}' => '',
					'{error}' => '',
				);

				return "<div class=\"control-group\">\n" . strtr($this->layout, $output) . '</div>';
			}

		return parent::render();
	}

	/**
	 * Render the label with class="control-label" for custom types
	 */
	public function renderControlLabel()
	{
		$options = array(
			'label' => $this->getLabel(),
			'required' => $this->getRequired(),
			'class' => 'control-label'
		);

		if (!empty($this->attributes['id']))
		{
			$options['for'] = $this->attributes['id'];
		}

		return CHtml::activeLabel($this->getParent()->getModel(), $this->name, $options);
	}

}