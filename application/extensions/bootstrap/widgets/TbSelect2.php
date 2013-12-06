<?php
/**
 * TbSelect2.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 11/4/12
 * Time: 9:11 PM
 */
class TbSelect2 extends CInputWidget
{
  /**
   * @var TbActiveForm when created via TbActiveForm, this attribute is set to the form that renders the widget
   * @see TbActionForm->inputRow
   */
  public $form;
  /**
   * @var array @param data for generating the list options (value=>display)
   */
  public $data = array();

  /**
   * @var string[] the JavaScript event handlers.
   */
  public $events = array();

  /**
   * @var bool whether to display a dropdown select box or use it for tagging
   */
  public $asDropDownList = true;
  /**
   * @var
   */
  public $options;

  /**
   * Initializes the widget.
   */
  public function init()
  {
    if(empty($this->data) && $this->asDropDownList === true)
      throw new CException(Yii::t('zii', '"data" attribute cannot be blank'));
  }

  /**
   * Runs the widget.
   */
  public function run()
  {
    list($name, $id) = $this->resolveNameID();

    if ($this->hasModel())
    {
      if($this->form)
        echo $this->asDropDownList?
          $this->form->dropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions) :
          $this->form->hiddenField($this->model, $this->attribute, $this->htmlOptions);
      else
        echo $this->asDropDownList?
          CHtml::activeDropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions) :
          CHtml::activeHiddenField($this->model, $this->attribute, $this->htmlOptions);

    } else
      echo $this->asDropDownList ?
        CHtml::dropDownList($name, $this->value, $this->data, $this->htmlOptions) :
        CHtml::hiddenField($name, $this->value, $this->htmlOptions);

    $this->registerClientScript($id);
  }

  /**
   * Registers required client script for bootstrap select2. It is not used through bootstrap->registerPlugin
   * in order to attach events if any
   */
  public function registerClientScript($id)
  {
    Yii::app()->bootstrap->registerAssetCss('select2.css');
    Yii::app()->bootstrap->registerAssetJs('select2.js');

    $options = !empty($this->options) ? CJavaScript::encode($this->options) : '';

    ob_start();
    echo "jQuery('#{$id}').select2({$options})";
    foreach ($this->events as $event => $handler)
      echo ".on('{$event}', " . CJavaScript::encode($handler) . ")";

    Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), ob_get_clean() . ';');
  }
}