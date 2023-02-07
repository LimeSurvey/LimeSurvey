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
        $surveyGrid = $this->widget('application.extensions.admin.grid.CLSGridView', [//done
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
            'afterAjaxUpdate'       => 'function(id, data){window.LS.doToolTip();bindListItemclick();}',
            'lsAfterAjaxUpdate'          => ['window.LS.doToolTip();', 'bindListItemclick();'],
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
                    'type'              => 'raw',
                    'value'             => 'CHtml::link($data->sid, Yii::app()->createUrl("surveyAdministration/view/",array("iSurveyID"=>$data->sid)))',
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
                    'type'              => 'raw',
                    'value'             => 'isset($data->defaultlanguage) ? CHtml::link(flattenText($data->defaultlanguage->surveyls_title), Yii::app()->createUrl("surveyAdministration/view/",array("surveyid"=>$data->sid))) : ""',
                    'htmlOptions'       => ['class' => 'has-link'],
                    'headerHtmlOptions' => ['class' => 'text-nowrap'],
                ],
                [
                    'header'            => gT('Group'),
                    'name'              => 'group',
                    'type'              => 'raw',
                    'value'             => 'isset($data->surveygroup) ? CHtml::link(flattenText($data->surveygroup->title), Yii::app()->createUrl("surveyAdministration/view/",array("surveyid"=>$data->sid))) : ""',
                    'htmlOptions'       => ['class' => 'has-link'],
                    'headerHtmlOptions' => ['class' => 'text-nowrap'],
                ],
                [
                    'header'            => gT('Created'),
                    'name'              => 'creation_date',
                    'type'              => 'raw',
                    'value'             => 'CHtml::link($data->creationdate, Yii::app()->createUrl("surveyAdministration/view/",array("surveyid"=>$data->sid)))',
                    'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                    'htmlOptions'       => ['class' => 'd-none d-sm-table-cell has-link'],
                ],
                [
                    'header'            => gT('Owner'),
                    'name'              => 'owner',
                    'type'              => 'raw',
                    'value'             => 'CHtml::link(CHtml::encode($data->ownerUserName), Yii::app()->createUrl("surveyAdministration/view/",array("surveyid"=>$data->sid)))',
                    'headerHtmlOptions' => ['class' => 'd-md-none d-xl-table-cell text-nowrap'],
                    'htmlOptions'       => ['class' => 'd-md-none d-xl-table-cell has-link'],
                ],
                [
                    'header'            => gT('Anonymized responses'),
                    'name'              => 'anonymized_responses',
                    'type'              => 'raw',
                    'value'             => 'CHtml::link($data->anonymizedResponses, Yii::app()->createUrl("surveyAdministration/view/",array("surveyid"=>$data->sid)))',
                    'headerHtmlOptions' => ['class' => 'd-md-none d-lg-table-cell'],
                    'htmlOptions'       => ['class' => 'd-md-none d-lg-table-cell has-link'],
                ],
                [
                    'header'      => gT('Partial'),
                    'type'        => 'raw',
                    'value'       => 'CHtml::link($data->countPartialAnswers, Yii::app()->createUrl("surveyAdministration/view/",array("surveyid"=>$data->sid)))',
                    'name'        => 'partial',
                    'htmlOptions' => ['class' => 'has-link'],
                ],
                [
                    'header'      => gT('Full'),
                    'name'        => 'full',
                    'type'        => 'raw',
                    'value'       => 'CHtml::link($data->countFullAnswers, Yii::app()->createUrl("surveyAdministration/view/",array("surveyid"=>$data->sid)))',
                    'htmlOptions' => ['class' => 'has-link'],
                ],
                [
                    'header'      => gT('Total'),
                    'name'        => 'total',
                    'type'        => 'raw',
                    'value'       => 'CHtml::link($data->countTotalAnswers, Yii::app()->createUrl("surveyAdministration/view/",array("surveyid"=>$data->sid)))',
                    'htmlOptions' => ['class' => 'has-link'],
                ],
                [
                    'header'      => gT('Closed group'),
                    'name'        => 'uses_tokens',
                    'type'        => 'raw',
                    'value'       => 'CHtml::link($data->hasTokensTable ? gT("Yes"):gT("No"), Yii::app()->createUrl("surveyAdministration/view/",array("surveyid"=>$data->sid)))',
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
