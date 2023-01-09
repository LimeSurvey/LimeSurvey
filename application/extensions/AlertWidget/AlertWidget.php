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

    /** @var string the type of the alert ('success', 'primary', 'secondary', 'danger', 'warning', 'info', 'light', 'dark') */
    public $type = '';

    /** @var string whether the general style is of type "filled", if not the style is "outlined" */
    public $isFilled = true;

    /** @var string whether the icon before the text is shown */
    public $showIcon = true;

    /** @var string whether the closeButton after the text is shown */
    public $showCloseButton = true;

    /** @var array html options */
    public $htmlOptions = [];

    public function run()
    {
        $this->registerClientScript();
        $this->render('alert', [
            'tag' => $this->tag,
            'text' => $this->text,
            'type' => $this->type,
            'isFilled' => $this->isFilled,
            'showIcon' => $this->showIcon,
            'showCloseButton' => $this->showCloseButton,
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
}