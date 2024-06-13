<?php

Yii::import('zii.widgets.grid.CGridView');

class BarActionsWidget extends CWidget
{
    /** @var int Since button dropdown is placed inside different HTML element than <ul> dropdown, this id
     * can be used by test code to connect the two. */
    private static $id = 1;

    /**
     * @var array Available actions for table row
     */
    public $items = [];


    /** Executes the widget
     * @throws CException
     */
    public function run(): void
    {
        self::$id++;
        $this->render('action_list', [
            'items' => $this->items,
            'id' => self::$id
        ]);
    }

    /** Initializes the widget */
    public function init(): void
    {
        $this->registerClientScript();
    }

    /** Registers required script files */
    public function registerClientScript(): void
    {
        App()->getClientScript()->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/grid/BarActionsWidget/assets/action_list.js',
            CClientScript::POS_END
        );
    }
}
