<?php
   /**
    * This file render the list of groups
    */
    
?>
        <div class="side-body">
        	<h3><?php eT('Questions in this survey'); ?></h3>
		
			<div class="row">
				<div class="col-lg-12 content-right">
		        	<?php
						$this->widget('bootstrap.widgets.TbGridView', array(
							'dataProvider' => $questionsDatas,
							'columns' => array(
								array(
									'name' => 'Question id',
									'value'=>'$data->qid',
									'htmlOptions' => array('class' => 'col-md-1'),
								),
								array(
									'name' => 'Question order',
									'value'=>'$data->question_order',
									'htmlOptions' => array('class' => 'col-md-2'),
								),															
								array(
									'name' => 'Title',
									'value'=>'$data->title',
									'htmlOptions' => array('class' => 'col-md-1'),
								),
								array(
									'name' => 'Question',
									'value'=>'$data->question',
								),
								array(
									'name' => 'Group',
									//'value'=>'$data->groupName',
									'value'=>'$data->groups->group_name',
									'htmlOptions' => array('class' => 'col-md-2'),
								),				

								array(            
								    'name'=>'',
								    'type'=>'raw',
								    'value'=>'$data->buttons',
								    'htmlOptions' => array('class' => 'col-md-2 text-right'),
								),	
																												
							),
							'ajaxUpdate' => false,
							/*
							'columns' => array(

								array(            
								    'name'=>'Group id',
								    'value'=>'$data->gid',
								    'htmlOptions' => array('class' => 'col-md-1'),
								),


								array(            
								    'name'=>'Group Order',
								    'value'=>'$data->group_order',
								    'htmlOptions' => array('class' => 'col-md-1'),
								),

								array(            
								    'name'=>'Group Name',
								    'value'=>'$data->group_name',
								    'htmlOptions' => array('class' => 'col-md-2'),
								),

								array(            
								    'name'=>'Description',
								    'type'=>'raw',
								    'value'=>'$data->description',
								    'htmlOptions' => array('class' => 'col-md-2'),
								),
																								
		  					),

							'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction'),
							'selectionChanged'=>"function(id){window.location='" . Yii::app()->urlManager->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/' ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
		  					'ajaxUpdate' => true,
		  					 */
		   				)); 
		        	?>
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
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

