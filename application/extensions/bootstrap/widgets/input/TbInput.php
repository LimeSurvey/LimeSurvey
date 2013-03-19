<?php
/**
 * TbInput class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets.input
 */

/**
 * Bootstrap input widget.
 * Used for rendering inputs according to Bootstrap standards.
 */
abstract class TbInput extends CInputWidget
{
	// The different input types.
	const TYPE_CHECKBOX = 'checkbox';
	const TYPE_CHECKBOXLIST = 'checkboxlist';
	const TYPE_CHECKBOXLIST_INLINE = 'checkboxlist_inline';
	const TYPE_DROPDOWN = 'dropdownlist';
	const TYPE_FILE = 'filefield';
	const TYPE_PASSWORD = 'password';
	const TYPE_RADIO = 'radiobutton';
	const TYPE_RADIOLIST = 'radiobuttonlist';
	const TYPE_RADIOLIST_INLINE = 'radiobuttonlist_inline';
	const TYPE_RADIOBUTTONGROUPSLIST = 'radiobuttongroupslist';
	const TYPE_TEXTAREA = 'textarea';
	const TYPE_TEXT = 'textfield';
	const TYPE_CAPTCHA = 'captcha';
	const TYPE_UNEDITABLE = 'uneditable';
	const TYPE_DATEPICKER = 'datepicker';
	const TYPE_REDACTOR = 'redactor';
	const TYPE_MARKDOWNEDITOR = 'markdowneditor';
	const TYPE_HTML5EDITOR = 'wysihtml5';
	const TYPE_DATERANGEPICKER = 'daterangepicker';
	const TYPE_TOGGLEBUTTON = 'togglebutton';
	const TYPE_COLORPICKER = 'colorpicker';
	const TYPE_CKEDITOR = 'ckeditor';
	const TYPE_TIMEPICKER = 'timepicker';
	const TYPE_SELECT2 = 'select2';

	/**
	 * @var TbActiveForm the associated form widget.
	 */
	public $form;
	/**
	 * @var string the input label text.
	 */
	public $label;
	/**
	 * @var string the input type.
	 * Following types are supported: checkbox, checkboxlist, dropdownlist, filefield, password,
	 * radiobutton, radiobuttonlist, textarea, textfield, captcha and uneditable.
	 */
	public $type;
	/**
	 * @var array the data for list inputs.
	 */
	public $data = array();
	/**
	 * @var string text to prepend.
	 */
	public $prependText;
	/**
	 * @var string text to append.
	 */
	public $appendText;
	/**
	 * @var string the hint text.
	 */
	public $hintText;
	/**
	 * @var array label html attributes.
	 */
	public $labelOptions = array();
	/**
	 * @var array prepend html attributes.
	 */
	public $prependOptions = array();
	/**
	 * @var array append html attributes.
	 */
	public $appendOptions = array();
	/**
	 * @var array hint html attributes.
	 */
	public $hintOptions = array();
	/**
	 * @var array error html attributes.
	 */
	public $errorOptions = array();
	/**
	 * @var array captcha html attributes.
	 */
	public $captchaOptions = array();

	/**
	 * Initializes the widget.
	 * @throws CException if the widget could not be initialized.
	 */
	public function init()
	{
		if (!isset($this->form))
			throw new CException(__CLASS__ . ': Failed to initialize widget! Form is not set.');

		if (!isset($this->model))
			throw new CException(__CLASS__ . ': Failed to initialize widget! Model is not set.');

		if (!isset($this->type))
			throw new CException(__CLASS__ . ': Failed to initialize widget! Input type is not set.');

		if ($this->type === self::TYPE_UNEDITABLE)
		{
			if (isset($this->htmlOptions['class']))
				$this->htmlOptions['class'] .= ' uneditable-input';
			else
				$this->htmlOptions['class'] = 'uneditable-input';
		}

		$this->processHtmlOptions();
	}

	/**
	 * Processes the html options.
	 */
	protected function processHtmlOptions()
	{
		if (isset($this->htmlOptions['prepend']))
		{
			$this->prependText = $this->htmlOptions['prepend'];
			unset($this->htmlOptions['prepend']);
		}

		if (isset($this->htmlOptions['append']))
		{
			$this->appendText = $this->htmlOptions['append'];
			unset($this->htmlOptions['append']);
		}

		if (isset($this->htmlOptions['hint']))
		{
			$this->hintText = $this->htmlOptions['hint'];
			unset($this->htmlOptions['hint']);
		}

		if (isset($this->htmlOptions['labelOptions']))
		{
			$this->labelOptions = $this->htmlOptions['labelOptions'];
			unset($this->htmlOptions['labelOptions']);
		}

		if (isset($this->htmlOptions['prependOptions']))
		{
			$this->prependOptions = $this->htmlOptions['prependOptions'];
			unset($this->htmlOptions['prependOptions']);
		}

		if (isset($this->htmlOptions['appendOptions']))
		{
			$this->appendOptions = $this->htmlOptions['appendOptions'];
			unset($this->htmlOptions['appendOptions']);
		}

		if (isset($this->htmlOptions['hintOptions']))
		{
			$this->hintOptions = $this->htmlOptions['hintOptions'];
			unset($this->htmlOptions['hintOptions']);

		}

		if (isset($this->htmlOptions['errorOptions']))
		{
			$this->errorOptions = $this->htmlOptions['errorOptions'];
			unset($this->htmlOptions['errorOptions']);
		}

		if (isset($this->htmlOptions['captchaOptions']))
		{
			$this->captchaOptions = $this->htmlOptions['captchaOptions'];
			unset($this->htmlOptions['captchaOptions']);
		}
	}

	/**
	 * Runs the widget.
	 * @throws CException if the widget type is invalid.
	 */
	public function run()
	{
		switch ($this->type)
		{
			case self::TYPE_CHECKBOX:
				$this->checkBox();
				break;

			case self::TYPE_CHECKBOXLIST:
				$this->checkBoxList();
				break;

			case self::TYPE_CHECKBOXLIST_INLINE:
				$this->checkBoxListInline();
				break;

			case self::TYPE_DROPDOWN:
				$this->dropDownList();
				break;

			case self::TYPE_FILE:
				$this->fileField();
				break;

			case self::TYPE_PASSWORD:
				$this->passwordField();
				break;

			case self::TYPE_RADIO:
				$this->radioButton();
				break;

			case self::TYPE_RADIOLIST:
				$this->radioButtonList();
				break;

			case self::TYPE_RADIOLIST_INLINE:
				$this->radioButtonListInline();
				break;

			case self::TYPE_RADIOBUTTONGROUPSLIST:
				$this->radioButtonGroupsList();
				break;

			case self::TYPE_TEXTAREA:
				$this->textArea();
				break;

			case self::TYPE_TEXT:
				$this->textField();
				break;

			case self::TYPE_CAPTCHA:
				$this->captcha();
				break;

			case self::TYPE_UNEDITABLE:
				$this->uneditableField();
				break;

			case self::TYPE_DATEPICKER:
				$this->datepickerField();
				break;

			case self::TYPE_REDACTOR:
				$this->redactorJs();
				break;

			case self::TYPE_MARKDOWNEDITOR:
				$this->markdownEditorJs();
				break;

			case self::TYPE_HTML5EDITOR:
				$this->html5Editor();
				break;

			case self::TYPE_DATERANGEPICKER:
				$this->dateRangeField();
				break;

			case self::TYPE_TOGGLEBUTTON:
				$this->toggleButton();
				break;

			case self::TYPE_COLORPICKER:
				$this->colorpickerField();
				break;

			case self::TYPE_CKEDITOR:
				$this->ckEditor();
				break;

			// Adding timepicker (Sergii)
			case self::TYPE_TIMEPICKER:
				$this->timepickerField();
				break;

			case self::TYPE_SELECT2:
				$this->select2Field();
				break;

			default:
				throw new CException(__CLASS__ . ': Failed to run widget! Type is invalid.');
		}
	}

	/**
	 * Returns the label for the input.
	 * @return string the label
	 */
	protected function getLabel()
	{
		if ($this->label !== false && !in_array($this->type, array('checkbox', 'radio')) && $this->hasModel())
			return $this->form->labelEx($this->model, $this->attribute, $this->labelOptions);
		else if ($this->label !== null)
			return $this->label;
		else
			return '';
	}

	/**
	 * Returns the prepend element for the input.
	 * @return string the element
	 */
	protected function getPrepend()
	{
		if ($this->hasAddOn())
		{
			$htmlOptions = $this->prependOptions;

			if (isset($htmlOptions['class']))
				$htmlOptions['class'] .= ' add-on';
			else
				$htmlOptions['class'] = 'add-on';

			ob_start();
			echo '<div class="' . $this->getAddonCssClass() . '">';
			if (isset($this->prependText))
				echo CHtml::tag('span', $htmlOptions, $this->prependText);

			return ob_get_clean();
		} else
			return '';
	}

	/**
	 * Returns the append element for the input.
	 * @return string the element
	 */
	protected function getAppend()
	{
		if ($this->hasAddOn())
		{
			$htmlOptions = $this->appendOptions;

			if (isset($htmlOptions['class']))
				$htmlOptions['class'] .= ' add-on';
			else
				$htmlOptions['class'] = 'add-on';

			ob_start();
			if (isset($this->appendText))
				echo CHtml::tag('span', $htmlOptions, $this->appendText);

			echo '</div>';
			return ob_get_clean();
		} else
			return '';
	}

	/**
	 * Returns the id that should be used for the specified attribute
	 * @param string $attribute the attribute
	 * @return string the id
	 */
	protected function getAttributeId($attribute)
	{
		return isset($this->htmlOptions['id'])
			? $this->htmlOptions['id']
			: CHtml::getIdByName(CHtml::resolveName($this->model, $attribute));
	}

	/**
	 * Returns the error text for the input.
	 * @return string the error text
	 */
	protected function getError()
	{
		return $this->form->error($this->model, $this->attribute, $this->errorOptions);
	}

	/**
	 * Returns the hint text for the input.
	 * @return string the hint text
	 */
	protected function getHint()
	{
		if (isset($this->hintText))
		{
			$htmlOptions = $this->hintOptions;

			if (isset($htmlOptions['class']))
				$htmlOptions['class'] .= ' help-block';
			else
				$htmlOptions['class'] = 'help-block';

			return CHtml::tag('p', $htmlOptions, $this->hintText);
		} else
			return '';
	}

	/**
	 * Returns the container CSS class for the input.
	 * @return string the CSS class
	 */
	protected function getContainerCssClass()
	{
		$attribute = $this->attribute;
		return $this->model->hasErrors(CHtml::resolveName($this->model, $attribute)) ? CHtml::$errorCss : '';
	}

	/**
	 * Returns the input container CSS classes.
	 * @return string the CSS class
	 */
	protected function getAddonCssClass()
	{
		$classes = array();
		if (isset($this->prependText))
			$classes[] = 'input-prepend';
		if (isset($this->appendText))
			$classes[] = 'input-append';

		return implode(' ', $classes);
	}

	/**
	 * Returns whether the input has an add-on (prepend and/or append).
	 * @return boolean the result
	 */
	protected function hasAddOn()
	{
		return isset($this->prependText) || isset($this->appendText);
	}

	/**
	 * Renders a checkbox.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function checkBox();

	/**
	 * Renders a toggle button.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function toggleButton();

	/**
	 * Renders a list of checkboxes.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function checkBoxList();

	/**
	 * Renders a list of inline checkboxes.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function checkBoxListInline();

	/**
	 * Renders a drop down list (select).
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function dropDownList();

	/**
	 * Renders a file field.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function fileField();

	/**
	 * Renders a password field.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function passwordField();

	/**
	 * Renders a radio button.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function radioButton();

	/**
	 * Renders a list of radio buttons.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function radioButtonList();

	/**
	 * Renders a list of inline radio buttons.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function radioButtonListInline();

	/**
	 * Renders a list of radio buttons using Button Groups.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function radioButtonGroupsList();

	/**
	 * Renders a textarea.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function textArea();

	/**
	 * Renders a text field.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function textField();

	/**
	 * Renders a CAPTCHA.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function captcha();

	/**
	 * Renders an uneditable field.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function uneditableField();

	/**
	 * Renders a datepicker field.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function datepickerField();

	/**
	 * Renders a redactorJS wysiwyg field.
	 * @abstract
	 * @return mixed
	 */
	abstract protected function redactorJs();


	/**
	 * Renders a markdownEditorJS wysiwyg field.
	 * @abstract
	 * @return mixed
	 */
	abstract protected function markdownEditorJs();

	/**
	 * Renders a bootstrap CKEditor wysiwyg editor.
	 * @abstract
	 * @return mixed
	 */
	abstract protected function ckEditor();

	/**
	 * Renders a bootstrap wysihtml5 editor.
	 * @abstract
	 * @return mixed
	 */
	abstract protected function html5Editor();

	/**
	 * Renders a daterange picker field
	 * @abstract
	 * @return mixed
	 */
	abstract protected function dateRangeField();

	/**
	 * Renders a colorpicker field.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function colorpickerField();

	/**
	 * Renders a timepicker field.
	 * @return string the rendered content
	 * @abstract
	 */
	abstract protected function timepickerField();

	/**
	 * Renders a select2 field.
	 * @return mixed
	 */
	abstract protected function select2Field();
}
