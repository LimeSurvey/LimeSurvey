<?php
/**
 * TbCKEditor.php
 *
 * Supports new CKEditor 4
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 10/29/12
 * Time: 6:23 PM
 */
class TbCKEditor extends CInputWidget
{
	/**
	 * @var TbActiveForm when created via TbActiveForm, this attribute is set to the form that renders the widget
	 * @see TbActionForm->inputRow
	 */
	public $form;

	/**
	 * @var array the CKEditor options
	 * @see http://docs.cksource.com/
	 * @since 10/30/12 10:40 AM the Editor used is CKEditor 4 Beta will be updated as final version is done
	 */
	public $editorOptions = array();

	/**
	 * Display editor
	 */
	public function run()
	{

		list($name, $id) = $this->resolveNameID();

		$this->registerClientScript($id);

		$this->htmlOptions['id'] = $id;

		// Do we have a model?
		if ($this->hasModel())
		{
			if($this->form)
				$html = $this->form->textArea($this->model, $this->attribute, $this->htmlOptions);
			else
				$html = CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
		} else
		{
			$html = CHtml::textArea($name, $this->value, $this->htmlOptions);
		}
		echo $html;
	}

	/**
	 * Registers required javascript
	 * @param $id
	 */
	public function registerClientScript($id)
	{
		Yii::app()->bootstrap->registerAssetJs('ckeditor/ckeditor.js');

		$options = !empty($this->editorOptions)? CJavaScript::encode($this->editorOptions) : '{}';

		Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId(), "CKEDITOR.replace( '$id', $options);");
	}
}