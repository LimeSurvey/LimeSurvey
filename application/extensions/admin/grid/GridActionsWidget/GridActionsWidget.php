<?php

Yii::import('zii.widgets.grid.CGridView');

class GridActionsWidget extends CWidget
{

    /**
     * @var array Available actions for table row
     */
    public $dropdownItems = [];

    /** Initializes the widget */
    public function init(): void
    {
        $this->registerClientScript();
    }

    /** Executes the widget
     * @throws CException
     */
    public function run(): void
    {
        $this->renderActions();
    }

    /** Renders the actions for a row in CLSGridView tables
     * @throws CException
     */
    public function renderActions(): void
    {
        $this->render('action_dropdown', [
            'dropdownItems' => $this->dropdownItems,
        ]);
    }


    /** Registers required script files */
    public function registerClientScript(): void
    {
        App()->getClientScript()->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/grid/GridActionsWidget/assets/action_dropdown.js',
            CClientScript::POS_END
        );
        // Link for each row
        App()->clientScript->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/grid/assets/rowLink.js',
            CClientScript::POS_END
        );
    }
}
