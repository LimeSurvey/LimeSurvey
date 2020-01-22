
<?php
    $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
?>

<div class="container-fluid ls-space padding left-35 right-35">
	<div class="ls-flex-column">
		<div class="col-12 h1"><?php eT('Survey menu')?></div>
		<div class="ls-flex-row">
			<div class="col-12 ls-flex-item">
			<?php $this->widget('bootstrap.widgets.TbGridView', array(
					'dataProvider' => $model->search(),
					'id' => 'surveymenu-shortlist-grid',
					'columns' => $model->getShortListColumns(),
					'emptyText'=>gT('No customizable entries found.'),
					'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
						CHtml::dropDownList(
							'pageSize',
							$pageSize,
							Yii::app()->params['pageSizeOptions'],
							array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),

					'itemsCssClass' =>'table table-striped',
					'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view col-12'),
					'ajaxUpdate' => 'surveymenu-shortlist-grid'
				));
			?>
			</div>
		</div>
	</div>
</div>


