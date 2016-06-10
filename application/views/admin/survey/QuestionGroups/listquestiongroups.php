<?php
/**
* This file render the list of groups
* It use the QuestionGroup model search method to build the data provider.
*
* @var $model  obj    the QuestionGroup model
* @var $surveyid int
*/
?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>
<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=>gT("Question groups in this survey"))); ?>
    <h3><?php eT('Question groups in this survey'); ?></h3>
    <div class="row">
        <div class="col-lg-12 content-right">

            <!-- Search Box -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="form">
                        <?php $form=$this->beginWidget('CActiveForm', array(
                            'action' => Yii::app()->createUrl('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid),
                            'method' => 'get',
                            'htmlOptions'=>array(
                                'class'=>'form-horizontal',
                            ),
                        )); ?>
                        <div class="form-group">
                            <?php echo CHtml::label(gT('Search by group name:'), 'group_name', array('class'=>'col-sm-2 control-label text-right col-sm-offset-6')); ?>
                            <div class="col-sm-2 text-right">
                                <?php echo $form->textField($model, 'group_name', array('class'=>'form-control')); ?>
                            </div>
                            <div class="col-sm-2">
                                <?php echo CHtml::submitButton(gT('Search','unescaped'), array('class'=>'btn btn-success')); ?>
                                <a href="<?php echo Yii::app()->createUrl('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid);?>" class="btn btn-warning"><?php eT('Reset');?></a>
                            </div>
                        </div>
                        <?php $this->endWidget(); ?>
                    </div><!-- form -->
                </div>
            </div>

            <!-- The table grid  -->
            <div class="row">
                <div class="col-lg-12">
                    <?php
                    $this->widget('bootstrap.widgets.TbGridView', array(
                        'id'=>'question-group-grid',
                        'dataProvider' => $model->search(),
                        'emptyText'=>gT('No questions groups found.'),
                        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).') .' '.sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                Yii::app()->params['pageSizeOptions'],
                                array(  'class'=>'changePageSize form-control',
                                    'style'=>'display: inline; width: auto'))),

                        // Columns to dispplay
                        'columns' => array(

                            // Group Id
                            array(
                                'header'=>gT('Group ID'),
                                'name'=>'group_id',
                                'value'=>'$data->gid'
                            ),

                            // Group Order
                            array(
                                'header'=>gT('Group order'),
                                'name'=>'group_order',
                                'value'=>'$data->group_order'
                            ),

                            // Group Name
                            array(
                                'name'=>'group_name',
                                'value'=>'$data->group_name',
                                'htmlOptions' => array('class' => 'col-md-2'),
                            ),

                            // Description
                            array(
                                'header'=>gT('Description'),
                                'name'=>'description',
                                'type'=>'raw',
                                'value'=>'viewHelper::flatEllipsizeText($data->description,true,0)',
                                'htmlOptions' => array('class' => 'col-md-6'),
                            ),

                            // Action buttons (defined in model)
                            array(
                                'header'=>'',
                                'name'=>'actions',
                                'type'=>'raw',
                                'value'=>'$data->buttons',
                                'htmlOptions' => array('class' => 'col-md-2 text-right nowrap'),
                            ),

                        ),
                        'ajaxUpdate' => true,
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- To update rows per page via ajax -->
<script type="text/javascript">
    jQuery(function($) {
        jQuery(document).on("change", '#pageSize', function(){
            $.fn.yiiGridView.update('question-group-grid',{ data:{ pageSize: $(this).val() }});
        });
    });
</script>
