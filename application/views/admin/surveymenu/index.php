<?php
/* @var $this SurveymenuController */
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
?>
<div class="container-fluid ls-space padding left-50 right-50">
	<div class="ls-flex-column ls-space padding left-35 right-35">
		<div class="ls-flex-row">
			<div class="col-12 h1">
				<?php eT('Surveymenus')?> 
				<a class="btn btn-primary pull-right col-xs-6 col-sm-3 col-md-2" href="#editcreatemenu" data-toggle="modal">
					<span id="createnewmenu">
						<i class="fa fa-plus"></i>&nbsp;<?php eT('New menu') ?>
					</span>
					<span id="editmenu" class="hide">
						<i class="fa fa-edit"></i>&nbsp;<?php eT('Edit menu') ?>
					</span>
				</a>		
			</div>
		</div>

		<div class="ls-flex-row">
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
					'ajaxUpdate' => true,
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
				<h4 class="modal-title"><?php eT("Really delete this surveymenu?");?></h4>
			</div>
			<div class="modal-body">
				<?php eT("All menuentries of this menu will also be deleted."); ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Cancel'); ?></button>
				<button type="button" id="deletemodal-confirm" class="btn btn-danger"><?php eT('Delete now'); ?></button>
			</div>
		</div>
	</div>
</div>

<script>
function bindAction(){
	$('.action_selectthismenu').on('click', function(){
		var checked = $(this).prop('checked') ? true : false;
		$('.action_selectthismenu').prop('checked', false);
		$(this).prop('checked', checked);
		if(checked){ 
			$('#createnewmenu').addClass('hide'); $('#editmenu').removeClass('hide'); 
		} else {
			$('#createnewmenu').removeClass('hide'); $('#editmenu').addClass('hide'); 
		}
	})
	$('#editcreatemenu').on('show.bs.modal', function(){
		var loadSurveyEntryFormUrl = "<?php echo Yii::app()->urlManager->createUrl('/admin/menus/sa/getsurveymenuform' ) ?>";
		var loadSurveyEntryFormData ={};
		if($('.action_selectthismenu:checked').length > 0){
			console.log($('.action_selectthismenu:checked'));
			loadSurveyEntryFormData.menuid = $('.action_selectthismenu:checked').val();
		}
		$(this).find('.modal-content').load(loadSurveyEntryFormUrl, loadSurveyEntryFormData,
				function(){
					console.log($('#surveymenu-form'));
				$('#surveymenu-form').on('submit', function(evt){
					evt.preventDefault();
					var data = $('#surveymenu-form').serializeArray();
					var url = $('#surveymenu-form').attr('action');
					$.ajax({
						url : url,
						data: data,
						method: 'POST',
						dataType: 'json',
						success: function(data){
							console.log(data);
							$('#editcreatemenu').modal('hide');
							$.fn.yiiGridView.update('surveymenu-grid');
						},
						error: function(error){
							console.log(error);
						}
					})
				}
			);
		});
	});
	$('#editcreatemenu').on('hidden.bs.modal', function(){
		$(this).find('.modal-content').html('');
	});
	$('#surveymenu-grid').on('click', 'tr', function(){
		$(this).find('.action_selectthismenu').trigger('click');
	});

	$('.action_surveymenu_deleteModal').on('click',function(){
		var menuid = $(this).closest('tr').data('surveymenu-id');
		$('#deletemodal').modal('show');
		$('#deletemodal').on('shown.bs.modal',function(){
			$('#deletemodal-confirm').on('click', function(){
				var url = "<?php echo Yii::app()->getController()->createUrl('/admin/menus/sa/delete'); ?>";
				$.ajax({
					url: url,
					data: {menuid: menuid, ajax: true},
					method: 'post',
					success: function(data){
						console.log(data);
						window.location.reload();
					},
					error: function(err){
						console.log(err);
						window.location.reload();
					}
				})
			})
		});
	});
	
	$('.action_surveymenu_editModal').on('click',function(){

	});
};
$(document).ready(bindAction);

</script>