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

        <div class="col-sm-4 pull-left" id="surveyListActions">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
              <?php eT('With selected survey:');?>
            <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="surveyListActions" style="left: 15px;">
              <li>
                  <a href="#" data-url="<?php echo App()->createUrl('/admin/survey/sa/deleteMultipleSurveys/');?>" data-action="delete">
                      <span class="text-danger glyphicon glyphicon-trash"></span>
                      <?php eT('Delete');?>
                  </a>
              </li>
              <li>
                  <a href="#" data-url="<?php echo App()->createUrl('/admin/survey/sa/activateMultipleSurveys/');?>" data-action="activate">
                      <span class="fa fa-play text-success"></span><?php eT('Activate');?>
                  </a>
              </li>
              <li>
                  <a href="#" data-url="<?php echo App()->createUrl('/admin/survey/sa/deactivateMultipleSurveys/');?>" data-action="deactivate">
                      <span class="fa fa-stop text-warning"></span>
                      <?php eT('Deactivate');?>
                  </a>
              </li>
              <li>
                  <a href="#" data-url="<?php echo App()->createUrl('/admin/survey/sa/exportMultipleSurveys/');?>" data-action="export">
                      <span class="icon-export"></span>
                      <?php eT('Export');?>
                  </a>
              </li>
            </ul>
        </div>

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
                        'value'=>'$data->sid',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs'),
                        //'footer'=>$this->renderPartial('//admin/survey/subview/_surveys_list_footer', array(), true),
                        //'oneRowFooter'=>true,
                    ),

                    array(
                        'header' => gT('Active'),
                        'name' => 'running',
                        'value'=>'$data->running',
                        'type'=>'raw',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs'),
                    ),

                    array(
                        'header' => gT('Title'),
                        'name' => 'title',
                        'value'=>'$data->defaultlanguage->surveyls_title',
                        'headerHtmlOptions'=>array('class' => 'col-md-4'),
                        'htmlOptions' => array('class' => 'col-md-4'),
                    ),

                    array(
                        'header' => gT('Created'),
                        'name' => 'creation_date',
                        'value'=>'$data->creationdate',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs'),
                    ),

                    array(
                        'header' => gT('Owner'),
                        'name' => 'owner',
                        'value'=>'$data->owner->users_name',
                        'headerHtmlOptions'=>array('class' => 'hidden-md hidden-sm hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-md hidden-sm hidden-xs'),
                    ),

                    array(
                        'header' => gT('Anonymized responses'),
                        'name' => 'anonymized_responses',
                        'value'=>'$data->anonymizedResponses',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs hidden-sm col-md-1'),
                        'htmlOptions' => array('class' => 'hidden-xs hidden-sm col-md-1'),
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
                        'header' => gT('Closed group'),
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
                'afterAjaxUpdate' => 'doToolTip'
            ));
            ?>
        </div>
    </div>
    <?php $this->renderPartial('//admin/survey/subview/_surveys_list_footer', array()); ?>
</div>

<!-- Mass action popup -->


<!-- To update rows per page via ajax -->
<script type="text/javascript">
    jQuery(function($) {
        jQuery(document).on("change", '#pageSize', function(){
            $.fn.yiiGridView.update('survey-grid',{ data:{ pageSize: $(this).val() }});
        });
    });
</script>
