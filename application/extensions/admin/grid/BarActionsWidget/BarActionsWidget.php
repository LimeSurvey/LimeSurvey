<?php

Yii::import('zii.widgets.grid.CGridView');

/**
 * Renders a list of actions available for each CGridView row.
 */
class BarActionsWidget extends CWidget
{
    /**
     * @var int $id A static counter to generate unique IDs for each widget instance.
     */
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
}
