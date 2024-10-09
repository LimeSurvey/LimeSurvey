<?php

Yii::import('zii.widgets.grid.CGridView');

/**
 * ColumnFilterWidget provides column filtering functionality for GridViews.
 * It allows users to filter the visible columns in a grid based on the available and selected columns.
 */
class ColumnFilterWidget extends CWidget
{
    /**
     * @var array $filteredColumns An array of columns that are currently filtered and visible in the grid.
     */
    public $filteredColumns = [];

    /**
     * @var array $filterableColumns An array of columns that can be filtered (shown or hidden) by the user.
     */
    public $filterableColumns = [];

    /**
     * @var mixed $columnsData Data related to the columns, potentially including column metadata or additional information needed for rendering.
     */
    public $columnsData;

    /**
     * @var string $modalId The ID of the modal dialog used for selecting columns to filter.
     */
    public $modalId;

    /**
     * @var CModel $model The data model associated with the grid view.
     */
    public $model;

    /**
     * Initializes the widget by registering necessary client-side scripts.
     */
    public function init(): void
    {
        $this->registerClientScript();
    }

    /**
     * Executes the widget by rendering the column filter actions.
     *
     * @throws CException If rendering fails.
     */
    public function run(): void
    {
        $this->renderActions();
    }

    /**
     * Renders the actions for filtering columns in a CLSGridView.
     *
     * @throws CException If rendering fails.
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
