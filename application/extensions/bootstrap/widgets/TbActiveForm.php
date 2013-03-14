<?php
/**
 * TbActiveForm class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

Yii::import('bootstrap.widgets.input.TbInput');

/**
 * Bootstrap active form widget.
 */
class TbActiveForm extends CActiveForm
{
	// Form types.
	const TYPE_VERTICAL = 'vertical';
	const TYPE_INLINE = 'inline';
	const TYPE_HORIZONTAL = 'horizontal';
	const TYPE_SEARCH = 'search';

	// Input classes.
	const INPUT_HORIZONTAL = 'bootstrap.widgets.input.TbInputHorizontal';
	const INPUT_INLINE = 'bootstrap.widgets.input.TbInputInline';
	const INPUT_SEARCH = 'bootstrap.widgets.input.TbInputSearch';
	const INPUT_VERTICAL = 'bootstrap.widgets.input.TbInputVertical';

	/**
	 * @var string the form type. See class constants.
	 */
	public $type = self::TYPE_VERTICAL;
	/**
	 * @var string input class.
	 */
	public $input;
	/**
	 * @var boolean flag that indicates if the errors should be displayed as blocks.
	 */
	public $inlineErrors;

	/**
	 * Initializes the widget.
	 * This renders the form open tag.
	 */
	public function init()
	{
		if (!isset($this->htmlOptions['class']))
			$this->htmlOptions['class'] = 'form-' . $this->type;
		else
			$this->htmlOptions['class'] .= ' form-' . $this->type;

		if (!isset($this->inlineErrors))
			$this->inlineErrors = $this->type === self::TYPE_HORIZONTAL;

		if ($this->inlineErrors)
			$this->errorMessageCssClass = 'help-inline error';
		else
			$this->errorMessageCssClass = 'help-block error';

		parent::init();
	}

	/**
	 * Renders a checkbox input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function checkBoxRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_CHECKBOX, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a toggle input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes (options key sets the options for the toggle component)
	 * @return string the generated row
	 */
	public function toggleButtonRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_TOGGLEBUTTON, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a checkbox list input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data the list data
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function checkBoxListRow($model, $attribute, $data = array(), $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_CHECKBOXLIST, $model, $attribute, $data, $htmlOptions);
	}

	/**
	 * Renders a checkbox list inline input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data the list data
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function checkBoxListInlineRow($model, $attribute, $data = array(), $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_CHECKBOXLIST_INLINE, $model, $attribute, $data, $htmlOptions);
	}

	/**
	 * Renders a drop-down list input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data the list data
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function dropDownListRow($model, $attribute, $data = array(), $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_DROPDOWN, $model, $attribute, $data, $htmlOptions);
	}

	/**
	 * Renders a file field input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function fileFieldRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_FILE, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a password field input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function passwordFieldRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_PASSWORD, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a radio button input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function radioButtonRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_RADIO, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a radio button list input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data the list data
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function radioButtonListRow($model, $attribute, $data = array(), $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_RADIOLIST, $model, $attribute, $data, $htmlOptions);
	}

	/**
	 * Renders a radio button list inline input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data the list data
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function radioButtonListInlineRow($model, $attribute, $data = array(), $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_RADIOLIST_INLINE, $model, $attribute, $data, $htmlOptions);
	}

	/**
	 * Renders a radio button list input row using Button Groups.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data the list data
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function radioButtonGroupsListRow($model, $attribute, $data = array(), $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_RADIOBUTTONGROUPSLIST, $model, $attribute, $data, $htmlOptions);
	}

	/**
	 * Renders a text field input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function textFieldRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_TEXT, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a text area input row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function textAreaRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_TEXTAREA, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a WYSIWYG redactor editor
	 * @param $model
	 * @param $attribute
	 * @param array $htmlOptions
	 * @return string
	 */
	public function redactorRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_REDACTOR, $model, $attribute, null, $htmlOptions);
	}

	/**
	* Renders a WYSIWYG Markdown editor
	* @param $model
	* @param $attribute
	* @param array $htmlOptions
	* @return string
	*/
	public function markdownEditorRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_MARKDOWNEDITOR, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a WYSIWYG bootstrap editor
	 * @param $model
	 * @param $attribute
	 * @param array $htmlOptions
	 * @return string
	 */
	public function html5EditorRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_HTML5EDITOR, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a WYSIWYG  ckeditor
	 * @param $model
	 * @param $attribute
	 * @param array $htmlOptions
	 * @return string
	 */
	public function ckEditorRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_CKEDITOR, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a captcha row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 * @since 0.9.3
	 */
	public function captchaRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_CAPTCHA, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders an uneditable text field row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 * @since 0.9.5
	 */
	public function uneditableRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_UNEDITABLE, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a datepicker field row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. 'events' and 'options' key specify the events
	 * and configuration options of datepicker respectively.
	 * @return string the generated row
	 * @since 1.0.2 Booster
	 */
	public function datepickerRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_DATEPICKER, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a colorpicker field row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. 'events' and 'options' key specify the events
	 * and configuration options of colorpicker respectively.
	 * @return string the generated row
	 * @since 1.0.3 Booster
	 */
	public function colorpickerRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_COLORPICKER, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * @param $model
	 * @param $attribute
	 * @param array $htmlOptions addition HTML attributes. In order to pass initialization parameters to dateRange, you
	 * need to set the HTML 'options' key with an array of configuration options. 'options' also has a
	 */
	public function dateRangeRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_DATERANGEPICKER, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a timepicker field row.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 * @since 0.10.0
	 */
	public function timepickerRow($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_TIMEPICKER, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a select2 field row
	 * @param $model
	 * @param $attribute
	 * @param array $htmlOptions
	 * @return string
	 */
	public function select2Row($model, $attribute, $htmlOptions = array())
	{
		return $this->inputRow(TbInput::TYPE_SELECT2, $model, $attribute, null, $htmlOptions);
	}

	/**
	 * Renders a checkbox list for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeCheckBoxList}.
	 * Please check {@link CHtml::activeCheckBoxList} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the check box list.
	 * @param array $htmlOptions additional HTML options.
	 * @return string the generated check box list
	 * @since 0.9.5
	 */
	public function checkBoxList($model, $attribute, $data, $htmlOptions = array())
	{
		return $this->inputsList(true, $model, $attribute, $data, $htmlOptions);
	}

	/**
	 * Renders a radio button list for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeRadioButtonList}.
	 * Please check {@link CHtml::activeRadioButtonList} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the radio button list.
	 * @param array $htmlOptions additional HTML options.
	 * @return string the generated radio button list
	 * @since 0.9.5
	 */
	public function radioButtonList($model, $attribute, $data, $htmlOptions = array())
	{
		return $this->inputsList(false, $model, $attribute, $data, $htmlOptions);
	}

	/**
	 * Renders a radio button list for a model attribute using Button Groups.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the radio button list.
	 * @param array $htmlOptions additional HTML options.
	 * @return string the generated radio button list
	 * @since 0.9.5
	 */
	public function radioButtonGroupsList($model, $attribute, $data, $htmlOptions = array())
	{
		$buttons = array();
		$scripts = array();

		$hiddenFieldId = CHtml::getIdByName(get_class($model) . '[' . $attribute . ']');
		$buttonType = isset($htmlOptions['type']) ? $htmlOptions['type'] : null;

		foreach ($data as $key => $value) {
			$btnId = CHtml::getIdByName(get_class($model) . '[' . $attribute . '][' . $key . ']');

			$button = array();
			$button['label'] = $value;
			$button['htmlOptions'] = array(
				'value' => $key,
				'id' => $btnId,
				'class' => (isset($model->$attribute) && $model->$attribute == $key ? 'active': ''),
			);
			$buttons[] = $button;

			// event as ordinary input
			$scripts[] = "\$('#" . $btnId . "').click(function(){
                \$('#" . $hiddenFieldId . "').val('" . $key . "').trigger('change');
            });";
		}

		Yii::app()->controller->widget('bootstrap.widgets.TbButtonGroup', array(
			'buttonType' => 'button',
			'toggle' => 'radio',
			'htmlOptions' => $htmlOptions,
			'buttons' => $buttons,
			'type' => $buttonType,
		));

		echo $this->hiddenField($model, $attribute);

		Yii::app()->clientScript->registerScript('radiobuttongrouplist-' . $attribute, implode("\n", $scripts));
	}

	/**
	 * Renders an input list.
	 * @param boolean $checkbox flag that indicates if the list is a checkbox-list.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the input list.
	 * @param array $htmlOptions additional HTML options.
	 * @return string the generated input list.
	 * @since 0.9.5
	 */
	protected function inputsList($checkbox, $model, $attribute, $data, $htmlOptions = array())
	{
		CHtml::resolveNameID($model, $attribute, $htmlOptions);
		$select = CHtml::resolveValue($model, $attribute);

		if ($model->hasErrors($attribute))
		{
			if (isset($htmlOptions['class']))
				$htmlOptions['class'] .= ' ' . CHtml::$errorCss;
			else
				$htmlOptions['class'] = CHtml::$errorCss;
		}

		$name = $htmlOptions['name'];
		unset($htmlOptions['name']);

		if (array_key_exists('uncheckValue', $htmlOptions))
		{
			$uncheck = $htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		} else
			$uncheck = '';

		$hiddenOptions = isset($htmlOptions['id']) ? array('id' => CHtml::ID_PREFIX . $htmlOptions['id']) : array('id' => false);
		$hidden = $uncheck !== null ? CHtml::hiddenField($name, $uncheck, $hiddenOptions) : '';

		if (isset($htmlOptions['template']))
			$template = $htmlOptions['template'];
		else
			$template = '<label class="{labelCssClass}">{input}{label}</label>';

		unset($htmlOptions['template'], $htmlOptions['separator'], $htmlOptions['hint']);

		if ($checkbox && substr($name, -2) !== '[]')
			$name .= '[]';

		unset($htmlOptions['checkAll'], $htmlOptions['checkAllLast']);

		$labelOptions = isset($htmlOptions['labelOptions']) ? $htmlOptions['labelOptions'] : array();
		unset($htmlOptions['labelOptions']);

		$items = array();
		$baseID = CHtml::getIdByName($name);
		$id = 0;
		$method = $checkbox ? 'checkBox' : 'radioButton';
		$labelCssClass = $checkbox ? 'checkbox' : 'radio';

		if (isset($htmlOptions['inline']))
		{
			$labelCssClass .= ' inline';
			unset($htmlOptions['inline']);
		}

		foreach ($data as $value => $label)
		{
			$checked = !is_array($select) && !strcmp($value, $select) || is_array($select) && in_array($value, $select);
			$htmlOptions['value'] = $value;
			$htmlOptions['id'] = $baseID . '_' . $id++;
			$option = CHtml::$method($name, $checked, $htmlOptions);
			$label = CHtml::label($label, $htmlOptions['id'], $labelOptions);
			$items[] = strtr($template, array(
				'{labelCssClass}' => $labelCssClass,
				'{input}' => $option,
				'{label}' => $label,
			));
		}

		return $hidden . implode('', $items);
	}

	/**
	 * Displays a summary of validation errors for one or several models.
	 * This method is very similar to {@link CHtml::errorSummary} except that it also works
	 * when AJAX validation is performed.
	 * @param mixed $models the models whose input errors are to be displayed. This can be either
	 * a single model or an array of models.
	 * @param string $header a piece of HTML code that appears in front of the errors
	 * @param string $footer a piece of HTML code that appears at the end of the errors
	 * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
	 * @return string the error summary. Empty if no errors are found.
	 * @see CHtml::errorSummary
	 */
	public function errorSummary($models, $header = null, $footer = null, $htmlOptions = array())
	{
		if (!isset($htmlOptions['class']))
			$htmlOptions['class'] = 'alert alert-block alert-error'; // Bootstrap error class as default

		return parent::errorSummary($models, $header, $footer, $htmlOptions);
	}

	/**
	 * Displays the first validation error for a model attribute.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute name
	 * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
	 * @param boolean $enableAjaxValidation whether to enable AJAX validation for the specified attribute.
	 * @param boolean $enableClientValidation whether to enable client-side validation for the specified attribute.
	 * @return string the validation result (error display or success message).
	 */
	public function error($model, $attribute, $htmlOptions = array(), $enableAjaxValidation = true, $enableClientValidation = true)
	{
		if (!$this->enableAjaxValidation)
			$enableAjaxValidation = false;

		if (!$this->enableClientValidation)
			$enableClientValidation = false;

		if (!isset($htmlOptions['class']))
			$htmlOptions['class'] = $this->errorMessageCssClass;

		if (!$enableAjaxValidation && !$enableClientValidation)
			return $this->renderError($model, $attribute, $htmlOptions);

		$id = CHtml::activeId($model, $attribute);
		$inputID = isset($htmlOptions['inputID']) ? $htmlOptions['inputID'] : $id;
		unset($htmlOptions['inputID']);
		if (!isset($htmlOptions['id']))
			$htmlOptions['id'] = $inputID . '_em_';

		$option = array(
			'id' => $id,
			'inputID' => $inputID,
			'errorID' => $htmlOptions['id'],
			'model' => get_class($model),
			'name' => CHtml::resolveName($model, $attribute),
			'enableAjaxValidation' => $enableAjaxValidation,
			'inputContainer' => 'div.control-group', // Bootstrap requires this
		);

		$optionNames = array(
			'validationDelay',
			'validateOnChange',
			'validateOnType',
			'hideErrorMessage',
			'inputContainer',
			'errorCssClass',
			'successCssClass',
			'validatingCssClass',
			'beforeValidateAttribute',
			'afterValidateAttribute',
		);

		foreach ($optionNames as $name)
		{
			if (isset($htmlOptions[$name]))
			{
				$option[$name] = $htmlOptions[$name];
				unset($htmlOptions[$name]);
			}
		}

		if ($model instanceof CActiveRecord && !$model->isNewRecord)
			$option['status'] = 1;

		if ($enableClientValidation)
		{
			$validators = isset($htmlOptions['clientValidation']) ? array($htmlOptions['clientValidation']) : array();

			$attributeName = $attribute;
			if (($pos = strrpos($attribute, ']')) !== false && $pos !== strlen($attribute) - 1) // e.g. [a]name
				$attributeName = substr($attribute, $pos + 1);

			foreach ($model->getValidators($attributeName) as $validator)
			{
				if ($validator->enableClientValidation)
					if (($js = $validator->clientValidateAttribute($model, $attributeName)) != '')
						$validators[] = $js;
			}

			if ($validators !== array())
				$option['clientValidation'] = "js:function(value, messages, attribute) {\n" . implode("\n", $validators) . "\n}";
		}

		$html = $this->renderError($model, $attribute, $htmlOptions);

		if ($html === '')
		{
			if (isset($htmlOptions['style']))
				$htmlOptions['style'] = rtrim($htmlOptions['style'], ';') . '; display: none';
			else
				$htmlOptions['style'] = 'display: none';

			$html = CHtml::tag('span', $htmlOptions, '');
		}

		$this->attributes[$inputID] = $option;

		return $html;
	}

	/**
	 * Displays the first validation error for a model attribute.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute name
	 * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
	 * @return string the error display. Empty if no errors are found.
	 * @see CModel::getErrors
	 * @see errorMessageCss
	 */
	protected static function renderError($model, $attribute, $htmlOptions = array())
	{
		CHtml::resolveName($model, $attribute); // turn [a][b]attr into attr
		$error = $model->getError($attribute);
		return $error != '' ? CHtml::tag('span', $htmlOptions, $error) : '';
	}

	/**
	 * Creates an input row of a specific type.
	 * @param string $type the input type
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data the data for list inputs
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated row
	 */
	public function inputRow($type, $model, $attribute, $data = null, $htmlOptions = array())
	{
		ob_start();
		Yii::app()->controller->widget($this->getInputClassName(), array(
			'type' => $type,
			'form' => $this,
			'model' => $model,
			'attribute' => $attribute,
			'data' => $data,
			'htmlOptions' => $htmlOptions,
		));
		return ob_get_clean();
	}

	/**
	 * Returns the input widget class name suitable for the form.
	 * @return string the class name
	 */
	protected function getInputClassName()
	{
		if (isset($this->input))
			return $this->input;
		else
		{
			switch ($this->type)
			{
				case self::TYPE_HORIZONTAL:
					return self::INPUT_HORIZONTAL;
					break;

				case self::TYPE_INLINE:
					return self::INPUT_INLINE;
					break;

				case self::TYPE_SEARCH:
					return self::INPUT_SEARCH;
					break;

				case self::TYPE_VERTICAL:
				default:
					return self::INPUT_VERTICAL;
					break;
			}
		}
	}
}
