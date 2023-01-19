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

    /** @var string icon which is used in the alert */
    private $icon = 'ri-notification-2-line';

    public function run()
    {
        $this->registerClientScript();
        $this->setTypeAndIcon();
        $errors = $this->extractErrors();
        $inErrorMode = $this->errorSummaryModel !== null && !empty($errors);
        $notInErrorMode = $this->errorSummaryModel === null;
        $this->buildHtmlOptions();

        // View is only rendered when there is a message to be shown:
        if($notInErrorMode || $inErrorMode) {
            $this->render('alert', [
                'tag' => $this->tag,
                'text' => $this->text,
                'header' => $this->header,
                'showIcon' => $this->showIcon,
                'showCloseButton' => $this->showCloseButton,
                'errors' => $errors,
                'inErrorMode' => $inErrorMode,
                'htmlOptions' => $this->htmlOptions,
                'icon' => $this->icon,
            ]);
        }
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

    /*
     * sets icon according to given alert type,
     * also sets default value for type, if unknown string is passed.
     */
    private function setTypeAndIcon()
    {
        $alertTypesAndIcons = [
        'success' => 'ri-checkbox-circle-fill',
        'primary' => 'ri-notification-2-line',
        'secondary' => 'ri-notification-2-line',
        'danger' => 'ri-error-warning-fill',
        'error' => 'ri-error-warning-fill',
        'warning' => 'ri-alert-fill',
        'info' => 'ri-notification-2-line',
        'light' => 'ri-notification-2-line',
        'dark' => 'ri-notification-2-line',
    ];
        if (array_key_exists($this->type, $alertTypesAndIcons)) {
            if ($this->type == 'error') {
                $this->type = 'danger';
            }
            $this->icon = $alertTypesAndIcons[$this->type];
        } else {
            $this->type = 'success';
        }
    }

    /*
     * Builds htmlOptions related to BS5 alerts, especially the class
     */
    private function buildHtmlOptions() {
        $alertClass = ' alert alert-';
        $alertClass .= $this->isFilled ? 'filled-' . $this->type : $this->type;
        $alertClass .= $this->showCloseButton ? ' alert-dismissible' : '';

        if (!array_key_exists('class', $this->htmlOptions)) {
            $this->htmlOptions['class'] = $alertClass;
        } else {
            $this->htmlOptions['class'] .= $alertClass;
        }
        $this->htmlOptions['role'] = 'alert';
    }
}