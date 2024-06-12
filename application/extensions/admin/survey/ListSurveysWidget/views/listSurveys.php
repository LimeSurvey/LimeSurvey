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
        $sessionKey = 'list_surveys';
        $filterableColumns = [];
        $filteredColumns  = [];
        $filteredColumns = !empty(isset($_SESSION['survey_' . $sessionKey]['filteredColumns'])) ? $_SESSION['survey_' . $sessionKey]['filteredColumns'] : [];
        $modalId = 'survey-column-filter-modal';

        $aColumns = [

            [
                'id'                => 'sid',
                'class'             => 'CCheckBoxColumn',
                'selectableRows'    => '100',
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
            [
                'header'            => gT('Status'),
                'name'              => 'running',
                'value'             => '$data->running',
                'type'              => 'raw',
                'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                'htmlOptions'       => ['class' => 'd-none d-sm-table-cell has-link'],
            ],
            [
                'header'            => gT('Title'),
                'name'              => 'title',
                'value'             => '$data->defaultlanguage->surveyls_title ?? null',
                'htmlOptions'       => ['class' => 'has-link'],
                'headerHtmlOptions' => ['class' => 'text-nowrap'],
            ],
            [
                'header'            => gT('Created'),
                'name'              => 'creation_date',
                'value'             => '$data->creationdate',
                'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                'htmlOptions'       => ['class' => 'd-none d-sm-table-cell has-link'],
            ],
            [
                'header'            => gT('Responses'),
                'name'              => 'responses',
                'value'             => '$data->countFullAnswers',
                'headerHtmlOptions' => ['class' => 'd-md-none d-lg-table-cell'],
                'htmlOptions'       => ['class' => 'd-md-none d-lg-table-cell has-link'],
            ]
        ];

        $filterableColumns['group'] = [
            'header'            => gT('Group'),
            'name'              => 'group',
            'value'             => '$data->surveygroup->title',
            'htmlOptions'       => ['class' => 'has-link'],
            'headerHtmlOptions' => ['class' => 'text-nowrap'],
        ];
        if (!isset($filteredColumns) || in_array('group', $filteredColumns)) {
            $aColumns[] = $filterableColumns['group'];
        }

        $filterableColumns['owner'] = [
            'header'            => gT('Owner'),
            'name'              => 'owner',
            'value'             => '$data->ownerUserName',
            'headerHtmlOptions' => ['class' => 'd-md-none d-xl-table-cell text-nowrap'],
            'htmlOptions'       => ['class' => 'd-md-none d-xl-table-cell has-link'],
        ];
        if (!isset($filteredColumns) || in_array('owner', $filteredColumns)) {
            $aColumns[] = $filterableColumns['owner'];
        }


        $filterableColumns['anonymized_responses'] = [
            'header'            => gT('Anonymized responses'),
            'name'              => 'anonymized_responses',
            'value'             => '$data->anonymizedResponses',
            'headerHtmlOptions' => ['class' => 'd-md-none d-lg-table-cell'],
            'htmlOptions'       => ['class' => 'd-md-none d-lg-table-cell has-link'],
        ];
        if (!isset($filteredColumns) || in_array('anonymized_responses', $filteredColumns)) {
            $aColumns[] = $filterableColumns['anonymized_responses'];
        }


        $filterableColumns['partial'] = [
            'header'      => gT('Partial'),
            'value'       => '$data->countPartialAnswers',
            'name'        => 'partial',
            'htmlOptions' => ['class' => 'has-link'],
        ];
        if (!isset($filteredColumns) || in_array('partial', $filteredColumns)) {
            $aColumns[] = $filterableColumns['partial'];
        }


        $filterableColumns['full'] = [
            'header'      => gT('Full'),
            'name'        => 'full',
            'value'       => '$data->countFullAnswers',
            'htmlOptions' => ['class' => 'has-link'],
        ];
        if (!isset($filteredColumns) || in_array('full', $filteredColumns)) {
            $aColumns[] = $filterableColumns['full'];
        }


        $filterableColumns['total'] = [
            'header'      => gT('Total'),
            'name'        => 'total',
            'value'       => '$data->countTotalAnswers',
            'htmlOptions' => ['class' => 'has-link'],
        ];
        if (!isset($filteredColumns) || in_array('total', $filteredColumns)) {
            $aColumns[] = $filterableColumns['total'];
        }

        $filterableColumns['uses_tokens'] = [
            'header'      => gT('Closed group'),
            'name'        => 'uses_tokens',
            'type'        => 'raw',
            'value'       => '$data->hasTokensTable ? gT("Yes"):gT("No")',
            'htmlOptions' => ['class' => 'has-link'],
        ];
        if (!isset($filteredColumns) || in_array('uses_tokens', $filteredColumns)) {
            $aColumns[] = $filterableColumns['uses_tokens'];
        }


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
            'modalId' => $modalId,
            'filterableColumns' => $filterableColumns,
            'filteredColumns' => $filteredColumns,
            'columnsData' => $aColumns,
        ]);


        ?>
    </div>
</div>
