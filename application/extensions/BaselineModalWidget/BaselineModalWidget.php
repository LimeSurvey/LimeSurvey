<?php

/**
 * BaselineModalWidget
 *
 * A reusable, content-agnostic Bootstrap 5 modal shell.
 * It renders an empty modal (header/body/footer) that can be filled with
 * arbitrary html and reused throughout the codebase. The body can be filled
 * up-front or asynchronously (e.g. loaded via ajax after the modal is shown).
 *
 * Render inline (echoes the html where the widget is called):
 *   $this->widget('ext.BaselineModalWidget.BaselineModalWidget', [
 *       'id'         => 'my-modal',
 *       'modalTitle' => gT('My title'),
 *       'body'       => '<p>Some content</p>',
 *       'footer'     => '<button class="btn btn-primary">Ok</button>',
 *       'modalSize'  => 'modal-lg',
 *   ]);
 *
 * Capture the html to reuse it somewhere else (e.g. from a plugin event where
 * echoing into the page is not possible):
 *   $html = Yii::app()->getController()->widget(
 *       'ext.BaselineModalWidget.BaselineModalWidget',
 *       ['id' => 'my-modal', 'modalTitle' => gT('My title')],
 *       true
 *   );
 */
class BaselineModalWidget extends CWidget
{
    /** @var int auto-increment counter to guarantee unique ids within one request */
    private static int $counter = 0;

    /** @var string|null the modal id, auto-generated when not provided */
    public $id = null;

    /** @var string the modal header title */
    public $modalTitle = '';

    /** @var string the modal body html */
    public $body = '';

    /** @var string the modal footer html (buttons etc.), footer is hidden when empty */
    public $footer = '';

    /** @var string extra class for the .modal-dialog element, e.g. 'modal-lg' or 'modal-sm' */
    public $modalSize = '';

    /** @var bool whether to render the header close (x) button */
    public $showCloseButton = true;

    /** @var bool if true the modal cannot be closed via keyboard/backdrop click */
    public $static = false;

    /** @var bool if true a script is registered that moves the modal to <body> */
    public $appendToBody = false;

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $modalAttr = '';
        if ($this->static) {
            $modalAttr = 'data-bs-keyboard="false" data-bs-backdrop="static"';
        }

        $this->render('modal', [
            'id'              => $this->getModalId(),
            'modalTitle'      => $this->modalTitle,
            'body'            => $this->body,
            'footer'          => $this->footer,
            'modalSize'       => $this->modalSize,
            'showCloseButton' => $this->showCloseButton,
            'modalAttr'       => $modalAttr,
        ]);

        if ($this->appendToBody) {
            $this->registerAppendToBodyScript();
        }
    }

    /**
     * Returns the modal id, generating a random one when none was provided.
     * @return string
     */
    public function getModalId(): string
    {
        if (!isset($this->id)) {
            self::$counter++;
            $this->id = 'modal_' . self::$counter;
        }

        return $this->id;
    }

    /**
     * Registers a small script that moves the modal to the end of <body>.
     * Useful to avoid stacking/overflow issues when the modal is rendered
     * deep inside the DOM.
     * @return void
     */
    protected function registerAppendToBodyScript()
    {
        $modalId = $this->getModalId();
        Yii::app()->clientScript->registerScript(
            'moveModalToBody_' . $modalId,
            '$("#' . $modalId . '").appendTo("body");',
            CClientScript::POS_END
        );
    }
}

