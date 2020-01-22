<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// $this->breadcrumbs=array(
// 	'Surveymenus',
// );

// $this->menu=array(
// 	array('label'=>'Create Surveymenu', 'url'=>array('create')),
// 	array('label'=>'Manage Surveymenu', 'url'=>array('admin')),
// );
//
$pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
$massiveAction = App()->getController()->renderPartial('/admin/surveymenu/massive_action/_selector', array(), true, false);

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyMenus');

?>
<div class="container-fluid ls-space padding left-50 right-50">
    <div class="ls-flex-column ls-space padding left-35 right-35">
        <div class="col-12 h1 pagetitle">
            <?php eT('Survey menus')?> 
        </div>
        <div class="col-12">
            <a class="btn btn-default pull-left col-xs-6 col-sm-3 col-md-2" id="createnewmenu" >
                <i class="icon-add text-success"></i>&nbsp;<?php eT('New') ?>
            </a>	
            <?php if(Permission::model()->hasGlobalPermission('superadmin','read')):?>
            <a class="btn btn-danger pull-right ls-space margin right-10 col-xs-6 col-sm-3 col-md-2" href="#restoremodal" data-toggle="modal">
                <i class="fa fa-refresh"></i>&nbsp;
                <?php eT('Reset') ?>
            </a>
            <?php endif; ?>	
        </div>
		<div class="col-12 ls-space margin top-15">
			<div class="col-12 ls-flex-item">
				<?php $this->widget('bootstrap.widgets.TbGridView', array(
					'dataProvider' => $model->search(),
					// Number of row per page selection
					'id' => 'surveymenu-grid',
					'columns' => $model->getColumns(),
					'filter' => $model,
					'emptyText'=>gT('No customizable entries found.'),
					'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
						CHtml::dropDownList(
							'pageSize',
							$pageSize,
							Yii::app()->params['pageSizeOptions'],
							array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto')
						)
					),
					'rowHtmlOptionsExpression' => '["data-surveymenu-id" => $data->id]',
					'htmlOptions' => array('class'=> 'table-responsive'),
					'itemsCssClass' => 'table table-responsive table-striped',
					'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view'),
					'ajaxType' => 'POST',
                    'ajaxUpdate' => 'surveymenu-grid',
                    'template'  => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
    				'afterAjaxUpdate'=>'bindAction',
				));
				?>
			</div>
		</div>
	</div>
</div>


<input type="hidden" id="surveymenu_open_url_selected_entry" value="0" />
<!-- modal! -->

<div class="modal fade" id="editcreatemenu" tabindex="-1" role="dialog">
  	<div class="modal-dialog modal-lg" role="document">
    	<div class="modal-content">
		</div>
	</div>
</div>
<div class="modal fade" id="deletemodal" tabindex="-1" role="dialog">
  	<div class="modal-dialog modal-lg" role="document">
    	<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php eT("Really delete this survey menu?");?></h4>
			</div>
			<div class="modal-body">
				<?php eT("All menu entries of this menu will also be deleted."); ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Cancel'); ?></button>
				<button type="button" id="deletemodal-confirm" class="btn btn-danger"><?php eT('Delete now'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="restoremodal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><?php eT("Really restore the default survey menus?");?></h4>
        </div>
        <div class="modal-body">
          <p>
            <?php eT("All custom menus will be lost."); ?>
          </p>
          <p>
            <?php eT("Please do a backup of the menus you want to keep."); ?>
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <?php eT('Cancel'); ?>
          </button>
          <button type="button" id="reset-menus-confirm" class="btn btn-danger">
            <?php eT('Yes, restore default'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>

<script>
var surveyMenuEntryFunctions = new SurveyMenuFunctionsWrapper('#editcreatemenu','surveymenu-grid', {
    loadSurveyEntryFormUrl: "<?php echo Yii::app()->urlManager->createUrl('/admin/menus/sa/getsurveymenuform' ) ?>",
    restoreEntriesUrl: "<?php echo Yii::app()->getController()->createUrl('/admin/menus/sa/restore'); ?>",
    deleteEntryUrl: "<?php echo Yii::app()->getController()->createUrl('/admin/menus/sa/delete'); ?>"
  }),
  bindAction = surveyMenuEntryFunctions.getBindActionForSurveymenus();

  $(document).on('ready pjax:scriptcomplete', bindAction);
</script>
