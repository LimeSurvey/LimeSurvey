<?php
/* @var $this SurveymenuEntriesController */
/* @var $dataProvider CActiveDataProvider */

// $this->breadcrumbs=array(
// 	'Surveymenu Entries',
// );

// $this->menu=array(
// 	array('label'=>'Create SurveymenuEntries', 'url'=>array('create')),
// 	array('label'=>'Manage SurveymenuEntries', 'url'=>array('admin')),
// );

$pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
?>
<div class="container-fluid ls-space padding left-50 right-50">
	<div class="ls-flex-column ls-space padding left-35 right-35">
		<div class="col-12 h1">
			<?php eT('Menu entries')?> 
			<a class="btn btn-primary pull-right col-xs-6 col-sm-3 col-md-2" href="#editcreatemenuentry" data-toggle="modal">
				<span id="createnewmenuentry">
					<i class="fa fa-plus"></i>&nbsp;<?php eT('New menu entry') ?>
				</span>
				<span id="editmenuentry" class="hide">
					<i class="fa fa-edit"></i>&nbsp;<?php eT('Edit entry') ?>
				</span>
			</a>
			
		</div>

		<div class="ls-flex-row">
			<div class="col-12 ls-flex-item">
			<?php $this->widget('bootstrap.widgets.TbGridView', array(
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
							array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),

					'itemsCssClass' =>'table table-striped',
					'rowHtmlOptionsExpression' => '["data-surveymenu-entry-id" => $data->id]',
					'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view col-12'),
					'ajaxType' => 'POST',
					'ajaxUpdate' => true,
    				'afterAjaxUpdate'=>'bindActions',
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
	var bindActions = function(){
	$('.action_selectthisentry').on('click', function(){
		var checked = $(this).prop('checked') ? true : false;
		$('.action_selectthisentry').prop('checked', false);
		$(this).prop('checked', checked);
		if(checked){ 
			$('#createnewmenuentry').addClass('hide'); $('#editmenuentry').removeClass('hide'); 
		} else {
			$('#createnewmenuentry').removeClass('hide'); $('#editmenuentry').addClass('hide'); 
		}
	})
	$('#editcreatemenuentry').on('show.bs.modal', function(){
		var loadSurveyEntryFormUrl = "<?php echo Yii::app()->urlManager->createUrl('/admin/menuentries/sa/getsurveymenuentryform' ) ?>";
		var loadSurveyEntryFormData ={};
		if($('.action_selectthisentry:checked').length > 0){
			console.log($('.action_selectthisentry:checked'));
			loadSurveyEntryFormData.menuentryid = $('.action_selectthisentry:checked').val();
		}
		$(this).find('.modal-content').load(loadSurveyEntryFormUrl, loadSurveyEntryFormData, function(){
			$('#surveymenu-entries-form').on('submit', function(evt){
					evt.preventDefault();
					var data = $('#surveymenu-entries-form').serializeArray();
					var url = $('#surveymenu-entries-form').attr('action');
					$.ajax({
						url : url,
						data: data,
						method: 'POST',
						dataType: 'json',
						success: function(data){
							console.log(data);
							$('#editcreatemenuentry').modal('hide');
							$.fn.yiiGridView.update('surveymenu-entries-grid');
						},
						error: function(error){
							console.log(error);
						}
					})
				});
		});
	});
	$('#editcreatemenuentry').on('hidden.bs.modal', function(){
		$(this).find('.modal-content').html('');
	});
	$('#surveymenu-entries-grid').on('click', 'tr', function(){
		$(this).find('.action_selectthisentry').trigger('click');
	});

	$('.action_surveymenuEntries_deleteModal').on('click',function(){
		var menuEntryid = $(this).closest('tr').data('surveymenu-entry-id');
		$('#deletemodal').modal('show');
		$('#deletemodal').on('shown.bs.modal',function(){
			$('#deletemodal-confirm').on('click', function(){
				var url = "<?php echo Yii::app()->getController()->createUrl('/admin/menuentries/sa/delete'); ?>";
				$.ajax({
					url: url,
					data: {menuEntryid: menuEntryid, ajax: true},
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
	};
	$(document).ready(bindActions);

</script>