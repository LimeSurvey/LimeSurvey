<?php

Yii::import('zii.widgets.grid.CGridView');

class ColumnFilterWidget extends CWidget
{
    public $filteredColumns = [];
    public $filterableColumns = [];
    public $columnsData;
    public $modalId;
    public $model;

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
        $this->render('columns_filter', [
            'model' => $this->model,
            'modalId' => $this->modalId,
            'filterableColumns' => $this->filterableColumns,
            'filteredColumns' => $this->filteredColumns,
            'columnsData' => $this->columnsData
        ]);
    }


    /** Registers required script files */
    public function registerClientScript(): void
    {
        $sNeededScriptVar = "modalId = '" . $this->modalId . "';";
        App()->getClientScript()->registerScript('sNeededScriptVar', $sNeededScriptVar, CClientScript::POS_BEGIN);

        App()->getClientScript()->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/grid/ColumnFilterWidget/assets/columns_filter.js',
            CClientScript::POS_END
        );
    }
}
