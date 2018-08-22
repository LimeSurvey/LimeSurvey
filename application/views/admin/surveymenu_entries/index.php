<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// $this->breadcrumbs=array(
// 	'Surveymenu Entries',
// );

// $this->menu=array(
// 	array('label'=>'Create SurveymenuEntries', 'url'=>array('create')),
// 	array('label'=>'Manage SurveymenuEntries', 'url'=>array('admin')),
// );

$pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
$massiveAction = App()->getController()->renderPartial('/admin/surveymenu_entries/massive_action/_selector', array(), true, false);

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyMenuEntries');

?>

<div class="container-fluid ls-space padding left-50 right-50">
    <div class="ls-flex-column ls-space padding left-35 right-35">
        <div class="col-12 h1 pagetitle">
            <?php eT('Menu entries')?>
        </div>
        <div class="col-12">
            <a class="btn btn-default pull-left col-xs-12 col-sm-3 col-md-2" id="createnewmenuentry">
                <i class="icon-add text-success"></i>&nbsp;
                <?php eT('New') ?>
            </a>
            <a class="btn btn-warning pull-right ls-space margin right-10 col-sm-3 col-md-2" id="reorderentries">
                <i class="fa fa-sort"></i>&nbsp;
                <?php eT('Reorder') ?>
            </a>
            <?php if(Permission::model()->hasGlobalPermission('superadmin','read')):?>
                <a class="btn btn-danger pull-right ls-space margin right-10 col-sm-3 col-md-2" href="#restoremodal" data-toggle="modal">
                    <i class="fa fa-refresh"></i>&nbsp;
                    <?php eT('Reset') ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="ls-flex-row">
            <div class="col-12 ls-flex-item">
                <?php
                $this->widget('bootstrap.widgets.TbGridView', array(
                    'dataProvider' => $model->search(),
                    'id' => 'surveymenu-entries-grid',
                    'columns' => $model->getColumns(),
                    'filter' => $model,
                    'emptyText'=>gT('No customizable entries found.'),
                    'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            array('class'=>'changePageSize form-control', 'id'=>'pageSize', 'style'=>'display: inline; width: auto')
                        )
                    ),
                    'itemsCssClass' =>'table table-striped',
                    'rowHtmlOptionsExpression' => '["data-surveymenu-entry-id" => $data->id]',
                    'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view col-12'),
                    'ajaxType' => 'POST',
                    'ajaxUpdate' => 'bindAction',
                    'afterAjaxUpdate'=>'bindAction',
                    'template'  => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                ));
            ?>
            </div>
        </div>
    </div>
</div>

  <input type="hidden" id="surveymenu_open_url_selected_entry" value="" />
  <!-- modal! -->

  <div class="modal fade" id="editcreatemenuentry" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
      </div>
    </div>
  </div>

  <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><?php eT("Really delete this survey menu entry?");?></h4>
        </div>
        <div class="modal-body">
          <?php eT("Please be careful - if you delete default entries you may not be able access some parts of the application."); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <?php eT('Cancel'); ?>
          </button>
          <button type="button" id="deletemodal-confirm" class="btn btn-danger">
            <?php eT('Delete now'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="restoremodal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><?php eT("Really restore the default survey menu entries?");?></h4>
        </div>
        <div class="modal-body">
          <p>
            <?php eT("All custom menu entries will be lost."); ?>
          </p>
          <p>
            <?php eT("Please do a backup of the menu entries you want to keep."); ?>
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <?php eT('Cancel'); ?>
          </button>
          <button type="button" id="reset-menu-entries-confirm" class="btn btn-danger">
            <?php eT('Yes, restore default'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    var surveyMenuEntryFunctions = new SurveyMenuFunctionsWrapper('#editcreatemenuentry','surveymenu-entries-grid', {
      loadSurveyEntryFormUrl: "<?php echo Yii::app()->urlManager->createUrl('/admin/menuentries/sa/getsurveymenuentryform' ) ?>",
      restoreEntriesUrl: "<?php echo Yii::app()->getController()->createUrl('/admin/menuentries/sa/restore'); ?>",
      reorderEntriesUrl: "<?php echo Yii::app()->getController()->createUrl('/admin/menuentries/sa/reorder'); ?>",
      deleteEntryUrl: "<?php echo Yii::app()->getController()->createUrl('/admin/menuentries/sa/delete'); ?>"
    }),
    bindAction = surveyMenuEntryFunctions.getBindActionForSurveymenuEntries();
    bindAction();
  </script>
