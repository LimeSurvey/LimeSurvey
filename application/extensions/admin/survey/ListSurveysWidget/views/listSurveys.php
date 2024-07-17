<?php
/*
* LimeSurvey
* Copyright (C) 2007-2016 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

?>

<!-- Grid -->
<div class="row">
    <div class="col-12">
        <?php
        $gridId = 'survey-grid';
        $modalId = 'survey-column-filter-modal';
        list($aColumns, $filterableColumns) = $this->model->getFilterableColumns();
        $filteredColumns = explode('|', SettingsUser::getUserSettingValue('column_filter_' . $gridId));

        $columns_filter_button = '<button role="button" type="button" class="btn b-0" data-bs-toggle="modal" data-bs-target="#'. $modalId .'">
                <i class="ri-add-fill"></i>
            </button>';


        $aColumns [] = [
            'header'            => gT('Action'),
            'name'              => 'actions',
            'value'             => '$data->actionButtons',
            'type'              => 'raw'
        ];

        $aColumns [] = [
            'header'            => $columns_filter_button,
            'name'              => 'dropdown_actions',
            'value'             => '$data->buttons',
            'type'              => 'raw',
            'headerHtmlOptions' => ['class' => 'ls-sticky-column', 'style' => 'font-size: 1.5em; font-weight: 400;'],
            'htmlOptions'       => ['class' => 'text-center ls-sticky-column'],
        ];


        $surveyGrid = $this->widget('application.extensions.admin.grid.CLSGridView', [
            'dataProvider'          => $this->model->search(),
            // Number of row per page selection
            'id'                    => 'survey-grid',
            'emptyText'             => gT('No surveys found.'),
            'summaryText'           => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                    gT('%s rows per page'),
                    CHtml::dropDownList(
                        'surveygrid--pageSize',
                        $this->pageSize,
                        Yii::app()->params['pageSizeOptions'],
                        ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                    )
                ),
            'ajaxUpdate'            => 'survey-grid',
            'lsAfterAjaxUpdate'     => [
                'window.LS.doToolTip();',
                'bindListItemclick();',
                'switchStatusOfListActions();',
            ],
            'rowLink'               => 'Yii::app()->createUrl("surveyAdministration/view/",array("iSurveyID"=>$data->sid))',
            // 'template'  => $this->template,
            'massiveActionTemplate' => $this->render('massive_actions/_selector', [], true, false),
            'columns'               => $aColumns,

        ]);

        App()->getController()->widget('ext.admin.grid.ColumnFilterWidget.ColumnFilterWidget', [
            'model' => get_class($this->model),
            'modalId' => $modalId,
            'filterableColumns' => $filterableColumns,
            'filteredColumns' => $filteredColumns,
            'columnsData' => $aColumns,
        ]);
        ?>
    </div>
</div>
