<?php

class TopbarWidget extends CWidget
{
    /** @var TopbarConfiguration Topbar configuration object */
    public $config = null;

    /** @var array View data for the left and right sides */
    public $aData = array();

    /**
     * Initializes and renders the widget
     */
    public function init()
    {
        parent::init();

        if (is_null($this->config)) return;
        
        // Register assets.
        Yii::app()->getClientScript()->registerPackage('admintoppanel');

        // Render the topbar
        $this->renderTopbar();
    }

    /**
     * Renders the topbar
     */
    protected function renderTopbar()
    {
        $aTopbarData = array();

        // If the view for the left side is set, render the content and add it to the main view data
        $leftSideView = $this->config->getLeftSideView();
        if (!empty($leftSideView)) $aTopbarData['leftSideContent'] = $this->render('includes/' . $leftSideView, $this->aData, true);

        // If the view for the right side is set, render the content and add it to the main view data
        $rightSideView = $this->config->getRightSideView();
        if (!empty($rightSideView)) $aTopbarData['rightSideContent'] = $this->render('includes/' . $rightSideView, $this->aData, true);

        $aTopbarData['topbarId'] = $this->config->getId();
        $aTopbarData = array_merge($this->config->getData(), $aTopbarData);

        $this->render($this->config->getViewName(), $aTopbarData);
    }
}