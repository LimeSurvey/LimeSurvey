<?php

use LimeSurvey\Menu\MenuButton;

class TopbarWidget2 extends CWidget
{
    /**
     * @var string path and name of the view that should be rendered
     */
    public $view = null;

    /**
     * @var null
     */
    public $leftSide = null;

    /**
     * @var MenuButton[] the menu buttons in the middle
     */
    public $middle = null;

    /**
     * @var MenuButton[] the menu buttons on the right side
     */
    public $left = null;

    /**
     * Initializes and renders the widget
     */
    public function init()
    {
        parent::init();

        if (is_null($this->view)) {
            return;
        }

        // Render the topbar
        $this->renderTopbar();
    }

    /**
     * Renders the topbar
     */
    protected function renderTopbar()
    {
        $this->render($this->view, $aTopbarData);
    }
}
