<?php
/**
* This file render the list of surveys
* It use the Survey model search method to build the data provider.
*
* @var $model  obj    the QuestionGroup model
*/
?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>
<div class="col-sm-12 list-surveys">
    <h3><?php eT('Survey list'); ?></h3>

    <!-- Search Box -->
    <div class="row">
        <div class="pull-right">
            <div class="form text-right">
                <!-- Begin Form -->
                <?php $form=$this->beginWidget('CActiveForm', array(
                    'action' => Yii::app()->createUrl('admin/survey/sa/listsurveys/'),
                    'method' => 'get',
                    'htmlOptions'=>array(
                        'class'=>'form-inline',
                    ),
                )); ?>

                <!-- search input -->
                <div class="form-group">
                    <?php echo $form->label($model, 'searched_value', array('label'=>gT('Search:'),'class'=>'control-label')); ?>
                    <?php echo $form->textField($model, 'searched_value', array('class'=>'form-control')); ?>
                </div>

                <!-- select state -->
                <div class="form-group">
                    <?php echo $form->label($model, 'active', array('label'=>gT('Active:'),'class'=>'control-label')); ?>
                    <select name="active" id='Survey_active' class="form-control">
                        <option value="" <?php if( $model->active==""){echo "selected";}?>><?php eT('(Any state)');?></option>
                        <option value="Y" <?php if( $model->active=="Y"){echo "selected";}?>><?php eT('Yes');?></option>
                        <option value="N" <?php if( $model->active=="N"){echo "selected";}?>><?php eT('No');?></option>
                        <option value="E" <?php if( $model->active=="E"){echo "selected";}?>><?php eT('Expired');?></option>
                        <option value="S" <?php if( $model->active=="S"){echo "selected";}?>><?php eT('Not yet started');?></option>
                    </select>
                </div>
                <?php echo CHtml::submitButton(gT('Search','unescaped'), array('class'=>'btn btn-success')); ?>
                <a href="<?php echo Yii::app()->createUrl('admin/survey/sa/listsurveys');?>" class="btn btn-warning"><?php eT('Reset');?></a>

                <?php $this->endWidget(); ?>
            </div>
        </div>
    </div>

    <!-- Grid -->
    <div class="row">
        <div class="col-sm-12 content-right">
            <?php
            $surveyGrid = $this->widget('bootstrap.widgets.TbGridView', array(
                'dataProvider' => $model->search(),

                // Number of row per page selection
                'id' => 'survey-grid',
                'emptyText'=>gT('No surveys found.'),
                'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                    CHtml::dropDownList(
                        'pageSize',
                        $pageSize,
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
                        'header' => gT('Active'),
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
                        'value'=>'CHtml::link($data->defaultlanguage->surveyls_title, Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
                        'htmlOptions' => array('class' => 'col-md-4 has-link'),
                        'header' => gT('Title'),
                        'headerHtmlOptions'=>array('class' => 'col-md-4'),
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
                        'value'=>'CHtml::link($data->owner->users_name, Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
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
                        'value'=>'CHtml::link($data->hasTokens, Yii::app()->createUrl("admin/survey/sa/view/",array("surveyid"=>$data->sid)))',
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
                'ajaxUpdate' => true,
                'afterAjaxUpdate' => 'doToolTip',
                'template'  => "{items}\n<div class=\"row-fluid\"><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
            ));
            ?>
        </div>
    </div>
</div>


<!-- To update rows per page via ajax -->
<script type="text/javascript">
    jQuery(function($) {
        jQuery(document).on("change", '#pageSize', function(){
            $.fn.yiiGridView.update('survey-grid',{ data:{ pageSize: $(this).val() }});
        });
    });
</script>
