<div class="col-lg-12 list-surveys">
	<h3><?php eT('Survey list'); ?></h3>

	<div class="row">
		<div class="col-lg-12 content-right">
        	<?php
				$this->widget('bootstrap.widgets.TbGridView', array(
					'dataProvider' => $surveysDatas,

					'columns' => array(
						array(
							'name' => 'Survey id',
							'value'=>'$data->sid',
							'htmlOptions' => array('class' => 'col-md-1'),
						),
						
						array(
							'name' => 'Title',
							'value'=>'$data->defaultlanguage->surveyls_title',
							'htmlOptions' => array('class' => 'col-md-1'),
						),
														
						array(
							'name' => 'Creation date',
							'value'=>'$data->creationdate',
							'htmlOptions' => array('class' => 'col-md-1'),
						),

						array(
							'name' => 'Owner',
							'value'=>'$data->owner->users_name',
							'htmlOptions' => array('class' => 'col-md-1'),
						),

						array(
							'name' => 'Anonymized responses',
							'value'=>'$data->anonymizedResponses',
							'htmlOptions' => array('class' => 'col-md-1'),
						),

						array(
							'name' => 'Active',
							'value'=>'$data->activeWord',
							'htmlOptions' => array('class' => 'col-md-1'),
						),						

																																															
/*
						array(
							'name' => 'Partial',
							'value'=>'$data->countPartialAnswers',
							'htmlOptions' => array('class' => 'col-md-1'),
						),
																																						
						array(
							'name' => 'Full',
							'value'=>'$data->countFullAnswers',
							'htmlOptions' => array('class' => 'col-md-1'),
						),																																								

						array(
							'name' => 'Total',
							'value'=>'$data->countTotalAnswers',
							'htmlOptions' => array('class' => 'col-md-1'),
						),
*/
						array(
							'name' => '',
							'value'=>'$data->buttons',
						    'type'=>'raw',
							'htmlOptions' => array('class' => 'col-md-1'),
						),

					),

							'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction'),
							'selectionChanged'=>"function(id){window.location='" . Yii::app()->urlManager->createUrl('admin/survey/sa/view/surveyid' ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
		  					'ajaxUpdate' => true,


   				)); 
        	?>
		</div>
	</div>
</div>