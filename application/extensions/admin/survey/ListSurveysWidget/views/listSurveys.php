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
                'switchStatusOfListActions();'
            ],
            'rowLink'               => 'Yii::app()->createUrl("surveyAdministration/view/",array("iSurveyID"=>$data->sid,"allowRedirect"=>1))',
            // 'template'  => $this->template,
            'massiveActionTemplate' => $this->render('massive_actions/_selector', [], true, false),
            'columns'               => [

                [
                    'id'                => 'sid',
                    'class'             => 'CCheckBoxColumn',
                    'selectableRows'    => '100',
                    'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                    'htmlOptions'       => ['class' => 'ls-sticky-column']
                ],
                [
                    'header'            => gT('Survey ID'),
                    'name'              => 'survey_id',
                    'value'             => '$data->sid',
                    'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                    'htmlOptions'       => ['class' => 'd-none d-sm-table-cell has-link'],
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
                    'header'            => gT('Group'),
                    'name'              => 'group',
                    'value'             => '$data->surveygroup->title',
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
                    'header'            => gT('Owner'),
                    'name'              => 'owner',
                    'value'             => '$data->ownerUserName',
                    'headerHtmlOptions' => ['class' => 'd-md-none d-xl-table-cell text-nowrap'],
                    'htmlOptions'       => ['class' => 'd-md-none d-xl-table-cell has-link'],
                ],
                [
                    'header'            => gT('Anonymized responses'),
                    'name'              => 'anonymized_responses',
                    'value'             => '$data->anonymizedResponses',
                    'headerHtmlOptions' => ['class' => 'd-md-none d-lg-table-cell'],
                    'htmlOptions'       => ['class' => 'd-md-none d-lg-table-cell has-link'],
                ],
                [
                    'header'      => gT('Partial'),
                    'value'       => '$data->countPartialAnswers',
                    'name'        => 'partial',
                    'htmlOptions' => ['class' => 'has-link'],
                ],
                [
                    'header'      => gT('Full'),
                    'name'        => 'full',
                    'value'       => '$data->countFullAnswers',
                    'htmlOptions' => ['class' => 'has-link'],
                ],
                [
                    'header'      => gT('Total'),
                    'name'        => 'total',
                    'value'       => '$data->countTotalAnswers',
                    'htmlOptions' => ['class' => 'has-link'],
                ],
                [
                    'header'      => gT('Closed group'),
                    'name'        => 'uses_tokens',
                    'type'        => 'raw',
                    'value'       => '$data->hasTokensTable ? gT("Yes"):gT("No")',
                    'htmlOptions' => ['class' => 'has-link'],
                ],
                [
                    'header'            => gT('Action'),
                    'name'              => 'actions',
                    'value'             => '$data->buttons',
                    'type'              => 'raw',
                    'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                    'htmlOptions'       => ['class' => 'text-center ls-sticky-column'],
                ],

            ],
        ]);
        ?>
    </div>
</div>
