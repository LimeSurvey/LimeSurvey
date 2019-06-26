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
    <h3><?php eT('Question groups in this survey'); ?></h3>
    <div class="row">
        <div class="col-lg-12 content-right">

            <!-- Search Box -->
            <?php $form=$this->beginWidget('TbActiveForm', array(
                'action' => Yii::app()->createUrl('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid),
                'method' => 'get',
                'htmlOptions'=>array(
                    'class'=>'form',
                ),
            )); ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">    
                            <div class="form-group col-sm-10">
                                <?php echo CHtml::label(gT('Search by group name:'), 'group_name', array('class'=>' control-label text-right col-sm-6')); ?>
                                <div class="col-sm-4 text-right">
                                    <?php echo $form->textField($model, 'group_name', array('class'=>'form-control col-sm-12')); ?>
                                </div>
                                <div class="col-sm-2 text-right">
                                    <?php echo CHtml::submitButton(gT('Search','unescaped'), array('class'=>'btn btn-success')); ?>
                                    <a href="<?php echo Yii::app()->createUrl('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid);?>" class="btn btn-warning"><?php eT('Reset');?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php $this->endWidget(); ?>

            <!-- The table grid  -->
            <div class="row">
                <div class="col-lg-12">
                    <?php
                    $this->widget('ext.LimeGridView.LimeGridView', array(
                        'id' => 'question-group-grid',
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
                        'ajaxUpdate' => 'question-group-grid',
                        'afterAjaxUpdate' => 'bindPageSizeChange'
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- To update rows per page via ajax -->
<?php App()->getClientScript()->registerScript("ListQuestionGroups-pagination", "
        var bindPageSizeChange = function(){
            $('#pageSize').on('change', function(){
                $.fn.yiiGridView.update('question-group-grid',{ data:{ pageSize: $(this).val() }});
            });
            $(document).trigger('actions-updated');
        };
    ", LSYii_ClientScript::POS_BEGIN); ?>
    
<?php App()->getClientScript()->registerScript("ListQuestionGroups-run-pagination", "bindPageSizeChange(); ", LSYii_ClientScript::POS_POSTSCRIPT); ?>
