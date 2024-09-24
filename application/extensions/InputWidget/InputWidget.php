<?php

class InputWidget extends CInputWidget
{
  /** @var string the value of the input */
  public $value = '';

  /** @var bool if button should behave as dropdown true or false */
  public $isImportant = false;

  /** @var bool if the attach is displayed.   */
  public $isAttached = false;

  /** @var string string that should contain valid html list for bootstrap component.
   * Only used when not empty and isAttached is true
   */
  public $attachContent = '';

  /** @var array html options */
  public $htmlOptions = [];

  /** @var array html options */
  public $wrapperHtmlOptions = [];


  public function init()
  {
    $this->setDefaultOptions();
  }

  public function run()
  {
    $this->renderInput();
  }

  /** Renders the button */
  public function renderInput()
  {
    list($name, $id) = $this->resolveNameID();
    $this->render('input', [
      'name' => $name,
      'id' => $id,
      'value' => $this->value,
      'isImportant' => $this->isImportant,
      'htmlOptions' => $this->htmlOptions,
      'isAttached' => $this->isAttached,
      'attachContent' => $this->attachContent,
      'wrapperHtmlOptions' => $this->wrapperHtmlOptions,
    ]);
  }


  private function setDefaultOptions()
  {

    if (array_key_exists('class', $this->wrapperHtmlOptions)) {
      $this->wrapperHtmlOptions['class'] = $this->wrapperHtmlOptions['class'] . ' position-relative';
    } else {
      $this->wrapperHtmlOptions['class'] = 'position-relative';
    }



    if (!array_key_exists('class', $this->htmlOptions)) {
      if ($this->isImportant || $this->isAttached) {
        $this->htmlOptions['class'] = 'form-control  ls-important-field';
      } else {
        $this->htmlOptions['class'] = 'form-control';
      }
    }

    if (!array_key_exists('name', $this->htmlOptions)) {
      $this->htmlOptions['name'] = $this->name;
    }
    if (!array_key_exists('id', $this->htmlOptions)) {
      $this->htmlOptions['id'] = $this->id;
    }
  }
}
