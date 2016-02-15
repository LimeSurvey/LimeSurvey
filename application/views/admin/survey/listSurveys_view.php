<?php
/**
* This file render the list of surveys
* It use the Survey model search method to build the data provider.
*
* @var $model  obj    the QuestionGroup model
*/
?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>
<div class="col-lg-12 list-surveys">
    <h3><?php eT('Survey list'); ?></h3>

    <!-- Search Box -->
    <div class="row">
        <div class="col-lg-12">
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
                    <?php echo $form->label($model, 'search', array('label'=>gT('Search:'),'class'=>'control-label')); ?>
                    <?php echo $form->textField($model, 'searched_value', array('class'=>'form-control')); ?>
                </div>

                <!-- select state -->
                <div class="form-group">
                    <?php echo $form->label($model, 'active', array('label'=>gT('Active:'),'class'=>'control-label')); ?>
                    <select name="active" class="form-control">
                        <option value="" <?php if( $model->active!="Y" && $model->active!="N" ){echo "selected";}?>><?php eT('(Any state)');?></option>
                        <option value="Y" <?php if( $model->active=="Y"){echo "selected";}?>><?php eT('Yes');?></option>
                        <option value="N" <?php if( $model->active=="N"){echo "selected";}?>><?php eT('No');?></option>
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
        <div class="col-lg-12 content-right">
            <?php
            $this->widget('bootstrap.widgets.TbGridView', array(
                'dataProvider' => $model->search(),

                // Number of row per page selection
                'id' => 'survey-grid',
                'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                    CHtml::dropDownList(
                        'pageSize',
                        $pageSize,
                        Yii::app()->params['pageSizeOptions'],
                        array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),

                'columns' => array(
                    array(
                        'header' => gT('Survey ID'),
                        'name' => 'survey_id',
                        'value'=>'$data->sid',
                        'htmlOptions' => array('class' => 'hidden-xs'),
                    ),

                    array(
                        'header' => gT('Title'),
                        'name' => 'title',
                        'value'=>'$data->defaultlanguage->surveyls_title',
                        'htmlOptions' => array('class' => 'col-md-4'),
                    ),

                    array(
                        'header' => gT('Created'),
                        'name' => 'creation_date',
                        'value'=>'$data->creationdate',
                        'htmlOptions' => array('class' => 'hidden-xs'),
                    ),

                    array(
                        'header' => gT('Owner'),
                        'name' => 'owner',
                        'value'=>'$data->owner->users_name',
                        'htmlOptions' => array('class' => 'hidden-xs'),
                    ),

                    array(
                        'header' => gT('Anonymized responses'),
                        'name' => 'anonymized_responses',
                        'value'=>'$data->anonymizedResponses',
                        'htmlOptions' => array('class' => 'hidden-xs'),
                    ),

                    array(
                        'header' => gT('Active'),
                        'name' => 'active',
                        'value'=>'$data->activeWord',
                        'htmlOptions' => array('class' => 'hidden-xs'),
                    ),

                    array(
                        'header' => gT('Partial'),
                        'name' => 'partial',
                        'value'=>'$data->countPartialAnswers',
                        'htmlOptions' => array('class' => ''),
                    ),

                    array(
                        'header' => gT('Full'),
                        'name' => 'full',
                        'value'=>'$data->countFullAnswers',
                        'htmlOptions' => array('class' => ''),
                    ),

                    array(
                        'header' => gT('Total'),
                        'name' => 'total',
                        'value'=>'$data->countTotalAnswers',
                        'htmlOptions' => array('class' => ''),
                    ),

                    array(
                        'header' => gT('Uses tokens'),
                        'name' => 'uses_tokens',
                        'value'=>'$data->hasTokens',
                        'htmlOptions' => array('class' => ''),
                    ),

                    array(
                        'header' => '',
                        'name' => 'actions',
                        'value'=>'$data->buttons',
                        'type'=>'raw',
                        'htmlOptions' => array('class' => ''),
                    ),

                ),

                'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction'),
                'selectionChanged'=>"function(id){window.location='" . Yii::app()->urlManager->createUrl('admin/survey/sa/view/surveyid' ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                'ajaxUpdate' => true,
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
