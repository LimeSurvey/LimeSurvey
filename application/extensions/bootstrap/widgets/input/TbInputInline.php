<?php
/**
 * TbInputInline class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets.input
 */

Yii::import('bootstrap.widgets.input.TbInputVertical');

/**
 * Bootstrap vertical form input widget.
 * @since 0.9.8
 */
class TbInputInline extends TbInputVertical
{
	/**
	 * Renders a drop down list (select).
	 * @return string the rendered content
	 */
	protected function dropDownList()
	{
		echo $this->getLabel();
		echo $this->form->dropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions);
	}

	/**
	 * Renders a password field.
	 * @return string the rendered content
	 */
	protected function passwordField()
	{
		$this->htmlOptions['placeholder'] = $this->model->getAttributeLabel($this->attribute);
		echo $this->getPrepend();
		echo $this->form->passwordField($this->model, $this->attribute, $this->htmlOptions);
		echo $this->getAppend();
	}

	/**
	 * Renders a textarea.
	 * @return string the rendered content
	 */
	protected function textArea()
	{
		$this->htmlOptions['placeholder'] = $this->model->getAttributeLabel($this->attribute);
		echo $this->form->textArea($this->model, $this->attribute, $this->htmlOptions);
	}

	/**
	 * Renders a text field.
	 * @return string the rendered content
	 */
	protected function textField()
	{
		$this->htmlOptions['placeholder'] = $this->model->getAttributeLabel($this->attribute);
		echo $this->getPrepend();
		echo $this->form->textField($this->model, $this->attribute, $this->htmlOptions);
		echo $this->getAppend();
	}
}
