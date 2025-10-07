<?php

/**
 * Creates a bootstrap alert on given options to fit the admin theme design.
 * - in general there are two different alerts:
 *  1. popup alerts (white background with colored border on left side), which disappear after 3 seconds by default,
 *     unless they are danger-alerts. For danger-alerts timeout is set to 10 seconds.
 *  2. inline alerts (with colored background according to the alert type and no animation on appearance)
 *
 * If you pass an AR model with "errorSummaryModel", this widget is able to extract the model errors
 * and behaves like ->errorSummary function but with the styling of this widget.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AlertWidget extends CWidget
{
    const DEFAULT_TIMEOUT = 3000;
    const DEFAULT_LONGER_TIMEOUT = 6000;

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
     * @var array | LSActiveRecord | CActiveRecord | CModel | null $model the models whose input errors are to be displayed. This can be either
     * a single model or an array of models
     */
    public $errorSummaryModel = null;

    /** @var array html options */
    public $htmlOptions = [];

    /** @var int | null $timeout milliseconds for how long the popup styled alerts should stay (0 = forever) */
    public $timeout = null;

    /** @var string icon which is used in the alert */
    private $icon = 'ri-notification-2-line';

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $errors = $this->handleErrors();
        $inErrorMode = $this->errorSummaryModel !== null && !empty($errors);
        $notInErrorMode = $this->errorSummaryModel === null;
        $this->setTypeAndIcon();
        $this->setTimeout();
        $this->buildHtmlOptions();
        $this->registerClientScript();

        // View is only rendered when there is a message to be shown:
        if ($notInErrorMode || $inErrorMode) {
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
                'type' => $this->type,
                'isFilled' => $this->isFilled
            ]);
        }
    }

    /**
     * Registers required script files
     * @return void
     */
    public function registerClientScript()
    {
        // auto close for popup alerts generated from PHP
        $script = "
        if($('.non-ajax-alert').length > 0) {
            var alertContainer = $('.non-ajax-alert');
            LS.autoCloseAlert(alertContainer, $this->timeout);
        }
        ";
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        Yii::app()->clientScript->registerScript('notif-autoclose', $script, CClientScript::POS_END);
    }

    /**
     * if errorSummaryModel contains something, the errors from the model(s)
     * will be extracted and returned as an array of strings,
     * additionally type and text will be set to default behavior,
     * if those are not passed.
     *
     * @return array
     */
    private function handleErrors()
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
            $this->text = $this->text == '' ? gT("Please fix the following input errors:") : $this->text;
            $this->type = $this->type == '' ? 'danger' : $this->type;
        }
        return $sumErrors;
    }

    /**
     * sets icon according to given alert type,
     * also sets default value for type, if unknown string is passed.
     *
     * @return void
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

    /**
     * Builds htmlOptions related to BS5 alerts, especially the class
     * @return void
     */
    private function buildHtmlOptions()
    {
        $alertClass = ' alert alert-';
        $alertClass .= $this->isFilled ? 'filled-' . $this->type : $this->type;
        $alertClass .= $this->showCloseButton ? ' alert-dismissible fade show' : '';

        if (!array_key_exists('class', $this->htmlOptions)) {
            $this->htmlOptions['class'] = $alertClass;
        } else {
            $this->htmlOptions['class'] .= $alertClass;
        }
        $this->htmlOptions['role'] = 'alert';
    }

    /**
     * Sets default timout value if it is not set by the widget call
     * @return void
     */
    private function setTimeout()
    {
        if ($this->type === 'danger' || $this->type === 'info') {
            $this->timeout = $this->timeout ?? self::DEFAULT_LONGER_TIMEOUT;
        } else {
            $this->timeout = self::DEFAULT_TIMEOUT;
        }
    }
}
