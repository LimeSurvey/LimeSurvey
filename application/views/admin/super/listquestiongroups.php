<?php
   /**
    * This file render the list of groups
    */
    
?>
        <div class="side-body">
        	<h3><?php eT('Question Groups in this survey'); ?></h3>
			<div class="row">
				<div class="col-lg-12 content-right">
		        	<?php
						$this->widget('bootstrap.widgets.TbGridView', array(
							'dataProvider' => $groupsDatas,
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
								
								array(            
								    'name'=>'',
								    'type'=>'raw',
								    'value'=>'$data->buttons',
								    'htmlOptions' => array('class' => 'col-md-2 text-right'),
								),								
																								
		  					),

		  					'ajaxUpdate' => true,
		  					 
		   				)); 
		        	?>
				</div>
			</div>
        </div>
        