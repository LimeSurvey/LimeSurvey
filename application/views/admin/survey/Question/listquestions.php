<?php
   /**
    * This file render the list of groups
    */
?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <?php if(App()->request->getParam('group_name')!=''):?>
        <h3><?php eT('Questions in group: '); ?> <em><?php echo App()->request->getParam('group_name'); ?></em></h3>
    <?php else:?>
        <h3><?php eT('Questions in this survey'); ?></h3>
    <?php endif;?>


    <div class="row">
        <div class="col-lg-12 content-right">

            <!-- Search Box -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="form  text-right">
                        <!-- Begin Form -->
                        <?php $form=$this->beginWidget('CActiveForm', array(
                            'action' => Yii::app()->createUrl('admin/survey/sa/listquestions/surveyid/'.$surveyid),
                            'method' => 'get',
                                'htmlOptions'=>array(
                                    'class'=>'form-inline',
                                ),
                            )); ?>

                            <!-- search input -->
                            <div class="form-group">
                                <?php echo $form->label($model, 'search', array('label'=>gT('Search:'),'class'=>'control-label' )); ?>
                                <?php echo $form->textField($model, 'title', array('class'=>'form-control')); ?>
                            </div>

                            <!-- select group -->
                            <div class="form-group">
                                <?php echo $form->label($model, 'group', array('label'=>gT('Group:'),'class'=>'control-label')); ?>
                                    <select name="gid" class="form-control">
                                        <option value=""><?php eT('(Any group)');?></option>
                                        <?php foreach($model->AllGroups as $group): ?>
                                            <option value="<?php echo $group->gid;?>" <?php if( $group->gid == $model->gid){echo 'selected';} ?>>
                                                <?php echo flattenText($group->group_name);?>
                                            </option>
                                        <?php endforeach?>
                                    </select>
                            </div>

                            <?php echo CHtml::submitButton(gT('Search','unescaped'), array('class'=>'btn btn-success')); ?>
                            <a href="<?php echo Yii::app()->createUrl('admin/survey/sa/listquestions/surveyid/'.$surveyid);?>" class="btn btn-warning"><?php eT('Reset');?></a>

                        <?php $this->endWidget(); ?>
                    </div><!-- form -->
                </div>
            </div>

            <!-- Grid -->
            <div class="row">
                <div class="col-lg-12">

                    <?php
                        $columns = array(
                            array(
                                'id'=>'id',
                                'class'=>'CCheckBoxColumn',
                                'selectableRows' => '100',
                            ),
                            array(
                                'header' => gT('Question ID'),
                                'name' => 'question_id',
                                'value'=>'$data->qid',
                            ),
                            array(
                                'header' => gT("Group / Question order"),
                                'name' => 'question_order',
                                'value'=>'$data->groups->group_order ." / ". $data->question_order',
                            ),
                            array(
                                'header' => gT('Code'),
                                'name' => 'title',
                                'value'=>'$data->title',
                                'htmlOptions' => array('class' => 'col-md-1'),
                            ),
                            array(
                                'header' => gT('Question'),
                                'name' => 'question',
                                'value'=>'viewHelper::flatEllipsizeText($data->question,true,0)',
                                'htmlOptions' => array('class' => 'col-md-5'),
                            ),
                            array(
                                'header' => gT('Question type'),
                                'name' => 'type',
                                'type'=>'raw',
                                'value'=>'$data->typedesc',
                                'htmlOptions' => array('class' => 'col-md-1'),
                            ),

                            array(
                                'header' => gT('Group'),
                                'name' => 'group',
                                'value'=>'$data->groups->group_name',
                            ),

                            array(
                                'header' => gT('Mandatory'),
                                'type' => 'raw',
                                'name' => 'mandatory',
                                'value'=> '$data->mandatoryIcon',
                                 'htmlOptions' => array('class' => 'text-center'),
                            ),

                            array(
                                'header' => gT('Other'),
                                'type' => 'raw',
                                'name' => 'other',
                                'value'=> '$data->otherIcon',
                                 'htmlOptions' => array('class' => 'text-center'),
                            ),


                            array(
                                'header'=>'',
                                'name'=>'actions',
                                'type'=>'raw',
                                'value'=>'$data->buttons',
                                'htmlOptions' => array('class' => 'col-md-2 col-xs-1 text-right nowrap'),
                            ),

                        );
                    ?>

                    <?php
                    $massiveAction = App()->getController()->renderPartial('/admin/survey/Question/massive_actions/_selector', array('model'=>$model, 'oSurvey'=>$oSurvey), true, false);
                    $this->widget('ext.LimeGridView.LimeGridView', array(
                        'dataProvider' => $model->search(),
                        // Number of row per page selection
                        'id' => 'question-grid',
                        'type'=>'striped',
                        'emptyText'=>gT('No questions found.'),
                        'template'      => "{items}\n<div id='ListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).') .' '.sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                Yii::app()->params['pageSizeOptions'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),
                                'columns' => $columns,
                                'ajaxUpdate' => 'question-grid',
                                'afterAjaxUpdate' => "bindPageSizeChange"
                            ));
                            ?>
                        </div>
                    </div>
        </div>
    </div>
</div>



<div class="modal fade" id="question-preview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php eT("Question preview");?></h4>
      </div>
      <div class="modal-body">
          <iframe id="frame-question-preview" src="" style="zoom:0.60" width="99.6%" height="600" frameborder="0"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close");?></button>
      </div>
    </div>
  </div>
</div>


<!-- To update rows per page via ajax -->
<?php App()->getClientScript()->registerScript("ListQuestions-pagination", "
        function bindPageSizeChange(){
            $('#pageSize').on('change', function(){
                $('#question-grid').yiiGridView('update',{ data:{ pageSize: $(this).val() }});
            });
            $(document).trigger('actions-updated');            
        };
    ", LSYii_ClientScript::POS_BEGIN); ?>
    
<?php App()->getClientScript()->registerScript("ListQuestions-run-pagination", "bindPageSizeChange();", LSYii_ClientScript::POS_POSTSCRIPT); ?>
