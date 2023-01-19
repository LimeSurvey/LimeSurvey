<?php

/**
 * Creates a bootstrap alert on given options to fit the admin theme design.
 */
class AlertWidget extends CWidget
{
    /** @var string the html element in which the alert should be displayed */
    public $tag = 'div';

    /** @var string the text displayed in the alert */
    public $text = '';

    /** @var string the header text displayed in the alert */
    public $header = '';

    /** @var string the type of the alert ('success', 'primary', 'secondary', 'danger', 'warning', 'info', 'light', 'dark') */
    public $type = '';

    /** @var bool whether the general style is of type "filled", if not the style is "outlined" */
    public $isFilled = true;

    /** @var bool whether the icon before the text is shown */
    public $showIcon = true;

    /** @var bool whether the closeButton after the text is shown */
    public $showCloseButton = false;

    /**
     * @var mixed $model the models whose input errors are to be displayed. This can be either
     * a single model or an array of models
     */
    public $errorSummaryModel = null;

    /** @var array html options */
    public $htmlOptions = [];

    public function run()
    {
        $this->registerClientScript();
        $errors = $this->extractErrors();
        $this->render('alert', [
            'tag' => $this->tag,
            'text' => $this->text,
            'header' => $this->header,
            'type' => $this->type,
            'isFilled' => $this->isFilled,
            'showIcon' => $this->showIcon,
            'showCloseButton' => $this->showCloseButton,
            'errorSummaryModel' => $this->errorSummaryModel,
            'errors' => $errors,
            'htmlOptions' => $this->htmlOptions
        ]);
    }

    /** Registers required script files */
    public function registerClientScript()
    {
        $script = "
        var alertContainer = $('.non-ajax-alert');
        LS.autoCloseAlert(alertContainer);
        ";
        Yii::app()->clientScript->registerScript('notif-autoclose', $script, CClientScript::POS_END);
    }

    /**
     * if errorSummaryModel contains something, the errors from the model(s)
     * will be extracted and returned as an array of strings
     * @return array
     */
    private function extractErrors()
    {
        $sumErrors = [];
        if (!empty($this->errorSummaryModel)) {
            $model = $this->errorSummaryModel;

            if (!is_array($model)) {
                $model = array($model);
            }
            if (isset($this->htmlOptions['firstError'])) {
                $firstError = $this->htmlOptions['firstError'];
                unset($this->htmlOptions['firstError']);
            } else {
                $firstError = false;
            }
            foreach ($model as $m) {
                foreach ($m->getErrors() as $errors) {
                    foreach ($errors as $error) {
                        if ($error != '') {
                            if (!isset($this->htmlOptions['encode']) || $this->htmlOptions['encode']) {
                                $error = CHtml::encode($error);
                            }
                            $sumErrors[] = $error;
                        }
                        if ($firstError) {
                            break;
                        }
                    }
                }
            }
        }
        return $sumErrors;
    }
}