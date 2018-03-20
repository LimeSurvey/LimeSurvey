<?php
/**
* Assesments view
*/
echo PrepareEditorScript(false, $this);
Yii::app()->getClientScript()->registerScript(
"AssessmentsVariables",
"var strnogroup = '".gT("There are no groups available.", "js")."',\n
loadEditUrl = '".$this->createUrl("admin/assessments/sa/index/", ["surveyid" => $surveyid, 'action' => 'assessmentopenedit'])."',\n
deleteUrl = '".$this->createUrl("admin/assessments/sa/index/", ["surveyid" => $surveyid, 'action' => 'assessmentdelete'])."';",
LSYii_ClientScript::POS_BEGIN
);

?>
  <div class="side-body <?=getSideBodyClass(false)?>">
    <?=viewHelper::getViewTestTag('surveyAssessments');?>
      <h3 class="page-title"><?=gT("Assessments")?></h3>
      <div class="container-fluid">
        <?php 
        if($asessementNotActivated) {
        ?>
          <div class="row text-center">
            <div class="jumbotron message-box <?php echo isset($asessementNotActivated['class']) ? $asessementNotActivated['class'] : " "; ?>">
              <h2><?php echo $asessementNotActivated['title']; ?></h2>
              <?php echo $asessementNotActivated['message']; ?>
            </div>
          </div>
        </div>
          
        <?php
        } else {
        ?>
            <h4><?php eT("Assessment rules");?></h4>
            <div class="row">
              <div class="col-sm-12">
                <?php
                    $this->widget('bootstrap.widgets.TbGridView', array(
                        'dataProvider' => $model->search(),
                        'id' => 'assessments-grid',
                        'columns' => $model->getColumns(),
                        'filter' => $model,
                        'emptyText'=>gT('No customizable entries found.'),
                        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '
                        . sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSizeAsessements',
                                $pageSizeAsessements,
                                Yii::app()->params['pageSizeOptions'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto')
                            )
                        ),
                        'rowHtmlOptionsExpression' => '["data-assessment-id" => $data->id]',
                        'htmlOptions' => array('class'=> 'table-responsive'),
                        'itemsCssClass' => 'table table-responsive table-striped',
                        'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view'),
                        'ajaxType' => 'POST',
                        'ajaxUpdate' => 'assessments-grid',
                        'template'  => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                        'afterAjaxUpdate'=>'bindAction',
                    ));
                ?>
              </div>
            </div>
            <?php if ( Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'create') ) { ?>
              <div class="row">
                <div class="col-sm-12">
                  <button class="btn btn-success" id="selector__assessment-add-new">
                    <?=eT("Add new assessment rule")?>
                  </button>
                </div>
              </div>
            <?php } ?>
            <!-- Edition - Modal -->
            <?php if ((Permission::model()->hasSurveyPermission($surveyid, 'assessments','update'))  || (Permission::model()->hasSurveyPermission($surveyid, 'assessments','create')) ) { ?>
                <?php Yii::app()->getController()->renderPartial('/admin/assessments/assessments_delete', ['surveyid' => $surveyid]); ?>
                <?php 
                    Yii::app()->getController()->renderPartial('/admin/assessments/assessments_edit', [
                            'surveyid' => $surveyid,
                            'actionvalue' => $actionvalue,
                            'editId' => $editId,
                            'assessmentlangs' => $assessmentlangs,
                            'baselang' => $baselang,
                            'gid' => $gid,
                            'action' => $action,
                            'groups' => $groups
                        ]
                    ); 
                ?>
            <?php } ?>
      </div>
  <!-- opened in controller -->
    <?php 
    };
    ?>
</div>
