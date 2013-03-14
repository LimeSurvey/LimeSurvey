<?php
/**
 * TbInputVertical class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets.input
 * @since 11/25/12 10:49 AM  updated by Antonio Ramirez <antonio@clevertech.biz>
 */

Yii::import('bootstrap.widgets.input.TbInput');

/**
 * Bootstrap vertical form input widget.
 * @since 0.9.8
 */
class TbInputVertical extends TbInput
{
	/**
	 * Renders a checkbox.
	 * @return string the rendered content
	 */
	protected function checkBox()
	{
		$attribute = $this->attribute;
		echo '<label class="checkbox" for="' . $this->getAttributeId($attribute) . '">';
		echo $this->form->checkBox($this->model, $this->attribute, $this->htmlOptions) . PHP_EOL;
		echo $this->model->getAttributeLabel($attribute);
		echo $this->getError() . $this->getHint();
		echo '</label>';
	}

	/**
	 * Renders a toogle button
	 * @return string the rendered content
	 */
	protected function toggleButton()
	{
		// widget configuration is set on htmlOptions['options']
		$options = array(
			'model' => $this->model,
			'attribute' => $this->attribute
		);
		if (isset($this->htmlOptions['options']))
		{
			$options = CMap::mergeArray($options, $this->htmlOptions['options']);
			unset($this->htmlOptions['options']);
		}
		$options['htmlOptions'] = $this->htmlOptions;

		echo $this->getLabel();
		$this->widget('bootstrap.widgets.TbToggleButton', $options);
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a list of checkboxes.
	 * @return string the rendered content
	 */
	protected function checkBoxList()
	{
		echo $this->getLabel();
		echo $this->form->checkBoxList($this->model, $this->attribute, $this->data, $this->htmlOptions);
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a list of inline checkboxes.
	 * @return string the rendered content
	 */
	protected function checkBoxListInline()
	{
		$this->htmlOptions['inline'] = true;
		$this->checkBoxList();
	}

	/**
	 * Renders a drop down list (select).
	 * @return string the rendered content
	 */
	protected function dropDownList()
	{
		echo $this->getLabel();
		echo $this->form->dropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions);
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a file field.
	 * @return string the rendered content
	 */
	protected function fileField()
	{
		echo $this->getLabel();
		echo $this->form->fileField($this->model, $this->attribute, $this->htmlOptions);
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a password field.
	 * @return string the rendered content
	 */
	protected function passwordField()
	{
		echo $this->getLabel();
		echo $this->getPrepend();
		echo $this->form->passwordField($this->model, $this->attribute, $this->htmlOptions);
		echo $this->getAppend();
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a radio button.
	 * @return string the rendered content
	 */
	protected function radioButton()
	{
		$attribute = $this->attribute;
		echo '<label class="radio" for="' . $this->getAttributeId($attribute) . '">';
		echo $this->form->radioButton($this->model, $this->attribute, $this->htmlOptions) . PHP_EOL;
		echo $this->model->getAttributeLabel($attribute);
		echo $this->getError() . $this->getHint();
		echo '</label>';
	}

	/**
	 * Renders a list of radio buttons.
	 * @return string the rendered content
	 */
	protected function radioButtonList()
	{
		echo $this->getLabel();
		echo $this->form->radioButtonList($this->model, $this->attribute, $this->data, $this->htmlOptions);
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a list of inline radio buttons.
	 * @return string the rendered content
	 */
	protected function radioButtonListInline()
	{
		$this->htmlOptions['inline'] = true;
		$this->radioButtonList();
	}

	/**
	 * Renders a list of radio buttons using Button Groups.
	 * @return string the rendered content
	 */
	protected function radioButtonGroupsList()
	{
		if (isset($this->htmlOptions['for']) && !empty($this->htmlOptions['for'])) {
			$label_for = $this->htmlOptions['for'];
			unset($this->htmlOptions['for']);
		} else if (isset($this->data) && !empty($this->data)) {
			$label_for = CHtml::getIdByName(get_class($this->model) . '[' . $this->attribute . '][' . key($this->data) . ']');
		}

		if (isset($label_for)) {
			$this->labelOptions = array('for' => $label_for);
		}

		echo $this->getLabel();
		echo $this->form->radioButtonGroupsList($this->model, $this->attribute, $this->data, $this->htmlOptions);
		echo $this->getError().$this->getHint();
	}

	/**
	 * Renders a textarea.
	 * @return string the rendered content
	 */
	protected function textArea()
	{
		echo $this->getLabel();
		echo $this->form->textArea($this->model, $this->attribute, $this->htmlOptions);
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a text field.
	 * @return string the rendered content
	 */
	protected function textField()
	{
		echo $this->getLabel();
		echo $this->getPrepend();
		echo $this->form->textField($this->model, $this->attribute, $this->htmlOptions);
		echo $this->getAppend();
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a CAPTCHA.
	 * @return string the rendered content
	 */
	protected function captcha()
	{
		echo $this->getLabel() . '<div class="captcha">';
		echo '<div class="widget">' . $this->widget('CCaptcha', $this->captchaOptions, true) . '</div>';
		echo $this->form->textField($this->model, $this->attribute, $this->htmlOptions);
		echo $this->getError() . $this->getHint();
		echo '</div>';
	}

	/**
	 * Renders an uneditable field.
	 * @return string the rendered content
	 */
	protected function uneditableField()
	{
		echo $this->getLabel();
		echo CHtml::tag('span', $this->htmlOptions, $this->model->{$this->attribute});
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a datepicker field.
	 * @return string the rendered content
	 * @author antonio ramirez <antonio@clevertech.biz>
	 */
	protected function datepickerField()
	{
		if (isset($this->htmlOptions['options']))
		{
			$options = $this->htmlOptions['options'];
			unset($this->htmlOptions['options']);
		}

		if (isset($this->htmlOptions['events']))
		{
			$events = $this->htmlOptions['events'];
			unset($this->htmlOptions['events']);
		}

		echo $this->getLabel();
		echo $this->getPrepend();
		$this->widget('bootstrap.widgets.TbDatePicker', array(
			'model' => $this->model,
			'attribute' => $this->attribute,
			'options' => isset($options) ? $options : array(),
			'events' => isset($events) ? $events : array(),
			'htmlOptions' => $this->htmlOptions,
		));
		echo $this->getAppend();
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a colorpicker field.
	 * @return string the rendered content
	 * @author antonio ramirez <antonio@clevertech.biz>
	 */
	protected function colorpickerField()
	{
		$format = 'hex';
		if (isset($this->htmlOptions['format']))
		{
			$format = $this->htmlOptions['format'];
			unset($this->htmlOptions['format']);
		}

		if (isset($this->htmlOptions['events']))
		{
			$events = $this->htmlOptions['events'];
			unset($this->htmlOptions['events']);
		}

		echo $this->getLabel();
		echo $this->getPrepend();
		$this->widget('bootstrap.widgets.TbColorPicker', array(
			'model' => $this->model,
			'attribute' => $this->attribute,
			'format' => $format,
			'events' => isset($events) ? $events : array(),
			'htmlOptions' => $this->htmlOptions,
		));
		echo $this->getAppend();
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a redactorJs.
	 * @return string the rendered content
	 */
	protected function redactorJs()
	{
		if (isset($this->htmlOptions['options']))
		{
			$options = $this->htmlOptions['options'];
			unset($this->htmlOptions['options']);
		}
		if (isset($this->htmlOptions['width']))
		{
			$width = $this->htmlOptions['width'];
			unset($this->htmlOptions['width']);
		}
		if (isset($this->htmlOptions['height']))
		{
			$height = $this->htmlOptions['height'];
			unset($this->htmlOptions['height']);
		}
		echo $this->getLabel();
		$this->widget('bootstrap.widgets.TbRedactorJs', array(
			'model' => $this->model,
			'attribute' => $this->attribute,
			'editorOptions' => isset($options) ? $options : array(),
			'width' => isset($width) ? $width : '100%',
			'height' => isset($height) ? $height : '400px',
			'htmlOptions' => $this->htmlOptions
		));
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a Markdown Editor.
	 * @return string the rendered content
	 */
	protected function markdownEditorJs()
	{

		if (isset($this->htmlOptions['width']))
		{
			$width = $this->htmlOptions['width'];
			unset($this->htmlOptions['width']);
		}
		if (isset($this->htmlOptions['height']))
		{
			$height = $this->htmlOptions['height'];
			unset($this->htmlOptions['height']);
		}
		echo $this->getLabel();
		echo '<div class="wmd-panel">';
		echo '<div id="wmd-button-bar" class="btn-toolbar"></div>';
		$this->widget('bootstrap.widgets.TbMarkdownEditorJs', array(
			'model' => $this->model,
			'attribute' => $this->attribute,
			'width' => isset($width) ? $width : '100%',
			'height' => isset($height) ? $height : '400px',
			'htmlOptions' => $this->htmlOptions
		));
		echo $this->getError() . $this->getHint();
		echo '<div id="wmd-preview" class="wmd-panel wmd-preview" style="width:' . (isset($width) ? $width : '100%') . '"></div>';
		echo '</div>'; // wmd-panel
	}

	/**
	 * Renders a ckEditor.
	 * @return string the rendered content
	 */
	protected function ckEditor()
	{
		if (isset($this->htmlOptions['options']))
		{
			$options = $this->htmlOptions['options'];
			unset($this->htmlOptions['options']);
		}

		echo $this->getLabel();
		$this->widget('bootstrap.widgets.TbCKEditor', array(
			'model' => $this->model,
			'attribute' => $this->attribute,
			'editorOptions' => isset($options) ? $options : array(),
			'htmlOptions' => $this->htmlOptions
		));
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a bootstrap wysihtml5 editor.
	 * @return string the rendered content
	 */
	protected function html5Editor()
	{
		if (isset($this->htmlOptions['options']))
		{
			$options = $this->htmlOptions['options'];
			unset($this->htmlOptions['options']);
		}
		if (isset($this->htmlOptions['width']))
		{
			$width = $this->htmlOptions['width'];
			unset($this->htmlOptions['width']);
		}
		if (isset($this->htmlOptions['height']))
		{
			$height = $this->htmlOptions['height'];
			unset($this->htmlOptions['height']);
		}
		echo $this->getLabel();
		$this->widget('bootstrap.widgets.TbHtml5Editor', array(
			'model' => $this->model,
			'attribute' => $this->attribute,
			'editorOptions' => isset($options) ? $options : array(),
			'width' => isset($width) ? $width : '100%',
			'height' => isset($height) ? $height : '400px',
			'htmlOptions' => $this->htmlOptions
		));
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a daterange field.
	 * @return string the rendered content
	 * @author antonio ramirez <antonio@clevertech.biz>
	 */
	protected function dateRangeField()
	{
		if (isset($this->htmlOptions['options']))
		{
			$options = $this->htmlOptions['options'];
			unset($this->htmlOptions['options']);
		}

		if (isset($options['callback']))
		{
			$callback = $options['callback'];
			unset($options['callback']);
		}

		echo $this->getLabel();
		echo $this->getPrepend();
		$this->widget('bootstrap.widgets.TbDateRangePicker', array(
			'model' => $this->model,
			'attribute' => $this->attribute,
			'options' => isset($options) ? $options : array(),
			'callback' => isset($callback) ? $callback : array(),
			'htmlOptions' => $this->htmlOptions,
		));
		echo $this->getAppend();
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a timepicker field.
	 * @return string the rendered content
	 * @author Sergii Gamaiunov <hello@webkadabra.com>
	 */
	protected function timepickerField()
	{
		if (isset($this->htmlOptions['options']))
		{
			$options = $this->htmlOptions['options'];
			unset($this->htmlOptions['options']);
		}

		if (isset($this->htmlOptions['events']))
		{
			$events = $this->htmlOptions['events'];
			unset($this->htmlOptions['events']);
		}

		echo $this->getLabel();
		echo $this->getPrepend();
		$this->widget('bootstrap.widgets.TbTimePicker', array(
			'model' => $this->model,
			'attribute' => $this->attribute,
			'options' => isset($options) ? $options : array(),
			'events' => isset($events) ? $events : array(),
			'htmlOptions' => $this->htmlOptions,
			'form' => $this->form
		));
		echo $this->getAppend();
		echo $this->getError() . $this->getHint();
	}

	/**
	 * Renders a select2Field
	 * @return mixed|void
	 */
	protected function select2Field()
	{
		if (isset($this->htmlOptions['options']))
		{
			$options = $this->htmlOptions['options'];
			unset($this->htmlOptions['options']);
		}

		if (isset($this->htmlOptions['events']))
		{
			$events = $this->htmlOptions['events'];
			unset($this->htmlOptions['events']);
		}

		if (isset($this->htmlOptions['data']))
		{
			$data = $this->htmlOptions['data'];
			unset($this->htmlOptions['data']);
		}

		if (isset($this->htmlOptions['asDropDownList']))
		{
			$asDropDownList = $this->htmlOptions['asDropDownList'];
			unset($this->htmlOptions['asDropDownList']);
		}

		echo $this->getLabel();
		echo $this->getPrepend();
		$this->widget('bootstrap.widgets.TbSelect2', array(
			'model' => $this->model,
			'attribute' => $this->attribute,
			'options' => isset($options) ? $options : array(),
			'events' => isset($events) ? $events : array(),
			'data' => isset($data) ? $data : array(),
			'asDropDownList' => isset($asDropDownList) ? $asDropDownList : true,
			'htmlOptions' => $this->htmlOptions,
			'form' => $this->form
		));
		echo $this->getAppend();
		echo $this->getError() . $this->getHint();
	}

}
