<?php

/**
 * RenderClass for Browser Detect Question
 *  * The ia Array contains the following
 *  0 => string qid
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 */
class RenderBrowserDetect extends QuestionBaseRenderer
{
  /**
   * Returns the view path of the short text input template.
   *
   * @return string
   */
  public function getMainView()
  {
    return '/survey/questions/answer/shortfreetext/text/item';
  }

  /**
   * Browser detect questions have no rows.
   *
   * @return void
   */
  public function getRows()
  {
    return;
  }

  /**
   * Renders the browser detect question as a single text input.
   *
   * @param string $sCoreClasses Additional CSS classes for the answer container
   * @return array{0: string, 1: string[]} Rendered HTML and the list of input names
   */
  public function render($sCoreClasses = '')
  {
    $coreClass = 'ls-answers answer-item text-item ' . $sCoreClasses;
    $extraclass = '';
    $withColumn = false;
    $maxlength = '';
    $inputsize = null;

    $maxChars = intval(trim((string) ($this->getQuestionAttribute('maximum_chars') ?? '')));
    if ($maxChars > 0) {
      $maxlength = $maxChars;
      $extraclass .= ' ls-input-maxchars';
    }

    $inputSizeAttr = trim((string) ($this->getQuestionAttribute('input_size') ?? ''));
    if (ctype_digit($inputSizeAttr)) {
      $inputsize = $inputSizeAttr;
      $extraclass .= ' ls-input-sized';
    }

    $prefix = trim((string) ($this->getQuestionAttribute('prefix', $this->sLanguage) ?? ''));
    if ($prefix !== '') {
      $extraclass .= ' withprefix';
    }
    $suffix = trim((string) ($this->getQuestionAttribute('suffix', $this->sLanguage) ?? ''));
    if ($suffix !== '') {
      $extraclass .= ' withsuffix';
    }
    $placeholder = trim((string) ($this->getQuestionAttribute('placeholder', $this->sLanguage) ?? ''));

    $dispVal = htmlspecialchars((string) $this->mSessionValue, ENT_QUOTES, 'UTF-8');

    $answer = '';
    if (!empty($this->getQuestionAttribute('time_limit'))) {
      $answer .= $this->getTimeSettingRender();
    }

    $answer .= Yii::app()->twigRenderer->renderQuestion(
      $this->getMainView(),
      array(
        'extraclass' => $extraclass,
        'coreClass' => $coreClass,
        'name' => $this->sSGQA,
        'basename' => $this->sSGQA,
        'prefix' => $prefix,
        'suffix' => $suffix,
        'kpclass' => '',
        'dispVal' => $dispVal,
        'maxlength' => $maxlength,
        'numberonly' => false,
        'inputsize' => $inputsize,
        'placeholder' => $placeholder,
        'withColumn' => $withColumn,
      ),
      true
    );

    $this->registerAssets();
    return array($answer, [$this->sSGQA]);
  }
}
