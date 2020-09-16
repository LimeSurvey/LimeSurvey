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
    <div class="col-sm-12 content-right">
        <?php
            $surveyGrid = $this->widget('bootstrap.widgets.TbGridView', array(
            'dataProvider' => $this->model->search(),

                // Number of row per page selection
                'id' => 'survey-grid',
                'emptyText'=>gT('No surveys found.'),
                'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                    CHtml::dropDownList(
                        'pageSize',
                        $this->pageSize,
                        Yii::app()->params['pageSizeOptions'],
                        array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),

                'columns' => array(

                    array(
                        'id'=>'sid',
                        'class'=>'CCheckBoxColumn',
                        'selectableRows' => '100',
                    ),

                    array(
                        'header' => gT('Survey ID'),
                        'name' => 'survey_id',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->sid, Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs has-link'),
                    ),

                    array(
                        'header' => gT('Status'),
                        'name' => 'running',
                        'value'=>'$data->running',
                        'type'=>'raw',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs has-link'),
                    ),

                    array(
                        'header' => gT('Title'),
                        'name' => 'title',
                        'type' => 'raw',
                        'value'=>'isset($data->defaultlanguage) ? CHtml::link(flattenText($data->defaultlanguage->surveyls_title), Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid))) : ""',
                        'htmlOptions' => array('class' => 'col-md-2 has-link'),
                        'headerHtmlOptions'=>array('class' => 'col-md-4'),
                    ),

                    array(
                        'header' => gT('Group'),
                        'name' => 'group',
                        'type' => 'raw',
                        'value'=>'isset($data->surveygroup) ? CHtml::link(flattenText($data->surveygroup->title), Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid))) : ""',
                        'htmlOptions' => array('class' => 'col-md-2 has-link'),
                    ),

                    array(
                        'header' => gT('Created'),
                        'name' => 'creation_date',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->creationdate, Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs has-link'),
                    ),

                    array(
                        'header' => gT('Owner'),
                        'name' => 'owner',
                        'type' => 'raw',
                        'value'=>'CHtml::link(CHtml::encode($data->ownerUserName), Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
                        'headerHtmlOptions'=>array('class' => 'hidden-md hidden-sm hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-md hidden-sm hidden-xs has-link'),
                    ),

                    array(
                        'header' => gT('Anonymized responses'),
                        'name' => 'anonymized_responses',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->anonymizedResponses, Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs hidden-sm col-md-1'),
                        'htmlOptions' => array('class' => 'hidden-xs hidden-sm col-md-1 has-link'),
                    ),


                    array(
                        'header' => gT('Partial'),
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->countPartialAnswers, Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
                        'name' => 'partial',
                        'htmlOptions' => array('class' => 'has-link'),
                    ),

                    array(
                        'header' => gT('Full'),
                        'name' => 'full',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->countFullAnswers, Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
                        'htmlOptions' => array('class' => 'has-link'),
                    ),

                    array(
                        'header' => gT('Total'),
                        'name' => 'total',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->countTotalAnswers, Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
                        'htmlOptions' => array('class' => 'has-link'),
                    ),

                    array(
                        'header' => gT('Closed group'),
                        'name' => 'uses_tokens',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->hasTokensTable ? gT("Yes"):gT("No"), Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
                        'htmlOptions' => array('class' => 'has-link'),
                    ),

                    array(
                        'header' => '',
                        'name' => 'actions',
                        'value'=>'$data->buttons',
                        'type'=>'raw',
                        'htmlOptions' => array('class' => 'text-right'),
                    ),

                ),
                'itemsCssClass' =>'table-striped',
                //'htmlOptions'=>array('style'=>'cursor: pointer;'),
                'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view'),
                //'selectionChanged'=>"function(id){window.location='" . Yii::app()->urlManager->createUrl('admin/survey/sa/view/surveyid' ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                'ajaxUpdate' => 'survey-grid',
                'afterAjaxUpdate' => 'function(id, data){window.LS.doToolTip();bindListItemclick();}',
                'template'  => $this->template,
            ));
        ?>
    </div>
</div>
