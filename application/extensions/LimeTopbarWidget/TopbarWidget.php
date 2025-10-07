<?php

use LimeSurvey\Menu\MenuButton;

class TopbarWidget extends CWidget
{
    /**
     * @var string this can be a simple text (string) or a breadcrumb
     */
    public $leftSide = null;

    public $titleBackLink = null;

    /**
     * @var bool true if leftSide is a breadcrumb, defaults to false
     */
    public $isBreadCrumb = false;

    /**
     * @var ButtonWidget[] the menu buttons in the middle
     */
    public $middle = null;

    /**
     * @var ButtonWidget[] the menu buttons on the right side
     */
    public $rightSide = null;

    /**
     * Initializes and renders the widget
     */
    public function init()
    {
        parent::init();

        $this->registerClientScript();
        // Render the topbar
        $this->renderTopbar();
    }

    /**
     * Renders the topbar
     */
    protected function renderTopbar()
    {
        $this->render(
            'topbar',
            [
                'leftSide' => $this->leftSide,
                'middle' => $this->middle,
                'rightSide' => $this->rightSide,
                'isBreadCrumb' => $this->isBreadCrumb,
                'titleBackLink' => $this->titleBackLink,
                'editorEnabled' => Yii::app()->getConfig('editorEnabled') ?? false,
            ]
        );
    }

    /**
     * Registers required script files
     * @return void
     */
    public function registerClientScript()
    {
        App()->getClientScript()->registerScriptFile(
            App()->getConfig('adminscripts') . 'topbar.js',
            CClientScript::POS_END
        );
    }
}
