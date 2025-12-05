<?php
/**
* Assesments view
*/

// todo implement new ekeditor 1580136051118
//echo PrepareEditorScript(true, $this);

$pageSize = intval(Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']));

?>
  <div class="side-body">
    <?=viewHelper::getViewTestTag('surveyAssessments');?>
      <h1 class="page-title" aria-level="1"><?=gT("Assessments")?></h1>
        <?php
            $messageLink = gT("Assessment mode for this survey is not activated.").'<br/>'
                . gT("If you want to activate it, click here:").'<br/>'
                . '<a role="button" class="btn btn-primary" href="'
                . $this->createUrl('/assessment/activate', ['surveyid'=> $surveyid])
                .'">'.gT('Activate assessements').'</a>';
        if(!Assessment::isAssessmentActive($surveyid)) {
        ?>
          <div class="row text-center">
            <div class="jumbotron message-box warningheader col-md-12 col-lg-6 offset-lg-3">
              <h2><?= gT("Assessment mode not activated"); ?></h2>
              <?php echo $messageLink; ?>
            </div>
          </div>

        <?php
        } else {
        ?>
            <h2><?php eT("Assessment rules");?></h2>
            <div class="row">
                <a href="#" id="loadEditUrl_forModalView" data-editurl="<?=$this->createUrl("assessment/edit/", ["surveyid" => $surveyid]);?>"></a>
                <?php
                    $this->widget('ext.admin.grid.CLSGridView', array(//done
                        'dataProvider' => $model->search(),
                        'id' => 'assessments-grid',
                        'columns' => $model->getColumns(),
                        'filter' => $model,
                        'emptyText' => gT('No customizable entries found.'),
                        'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                        . sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                Yii::app()->params['pageSizeOptions'],
                                array('class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto')
                            )
                        ),
                        'rowHtmlOptionsExpression' => '["data-assessment-id" => $data->id]',
                        'ajaxType'                 => 'POST',
                        'ajaxUpdate'               => 'assessments-grid',
                        'afterAjaxUpdate'          => 'bindAction',
                    ));
                ?>
            </div>
            <?php if ( Permission::model()->hasSurveyPermission($surveyid, 'assessments', 'create') ) { ?>
              <div class="row">
                <div class="col-12">
                  <button class="btn btn-primary" type="button" id="selector__assessment-add-new">
                    <?=eT("Add new assessment rule")?>
                  </button>
                </div>
              </div>
            <?php } ?>
            <!-- Edition - Modal -->
            <?php if ((Permission::model()->hasSurveyPermission($surveyid, 'assessments','update'))  || (Permission::model()->hasSurveyPermission($surveyid, 'assessments','create')) ) { ?>
                <?php $this->renderPartial('assessments_delete', ['surveyid' => $surveyid]); ?>
                <?php 
                    $this->renderPartial('assessments_edit', [
                            'surveyid' => $surveyid,
                            'editId' => $editId,
                            'assessmentlangs' => $assessmentlangs,
                            'baselang' => $baselang,
                            'groups' => $groups ?? [],
                            'gid' => $groupId,
                        ]
                    );
                ?>
            <?php } ?>
  <!-- opened in controller -->
    <?php 
    };
    ?>
</div>

<script type="text/javascript">
jQuery(function($) {
    // To update rows per page via ajax
    $(document).on("change", '#pageSize', function() {
        $.fn.yiiGridView.update('assessments-grid', {data:{pageSize: $(this).val()}});
    });
});
</script>
