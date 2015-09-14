<div class='menubar surveybar' id="questiongroupbarid">
    <div class='row container-fluid'>
    	<div class="col-md-12">
		<?php if(isset($questiongroupbar['buttons']['view'])):?>
	
			<?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
				<?php if (count($languagelist) > 1): ?>
					<!-- Single button -->
					<div class="btn-group">
					  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					  	<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/preview.png" />
					    <?php eT("Preview this question group"); ?> <span class="caret"></span>
					  </button>
					  <ul class="dropdown-menu" style="min-width : 252px;">
					  	<?php foreach ($languagelist as $tmp_lang): ?>
					  		<li>
					  			<a target="_blank" href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $tmp_lang); ?>" >
					  				<?php echo getLanguageNameFromCode($tmp_lang,false); ?>
					  			</a>
					  		</li>	
					  	<?php endforeach; ?>
					  </ul>
					</div>
				<?php else:?>
					<a class="btn btn-default" href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>" role="button" target="_blank">
						<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/preview.png" />
						<?php eT("Preview this question group");?>
					</a>
				<?php endif; ?>                	
			<?php else: ?>
				<a class="btn disabled" href="#" role="button">
					<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/preview.png" />
					<?php eT("Preview this question group");?>
				</a>            	
			<?php endif; ?>		
	    		
			<!-- Edit button -->    		
			<?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
				<a class="btn btn-default" href="<?php echo $this->createUrl('admin/questiongroups/sa/edit/surveyid/'.$surveyid.'/gid/'.$gid); ?>" role="button">
					<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/edit.png" />
					<?php eT("Edit current question group");?>
				</a>			
			<?php endif; ?>    		
	
			<!-- Check survey logic -->
	        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')): ?>
				<a class="btn btn-default" href="<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>" role="button">
					<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/quality_assurance.png" />
					<?php eT("Check survey logic for current question group"); ?>
				</a>
			<?php endif; ?>
	
			<?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete')):?>
				<?php if( ($sumcount4 == 0 && $activated != "Y") || $activated != "Y" ):?>
					<?php if(is_null($condarray)):?>
						<a class="btn btn-default" onclick="if (confirm('<?php eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>')) { window.open('<?php echo $this->createUrl("admin/questiongroups/sa/delete/surveyid/$surveyid/gid/$gid"); ?>','_top'); }" role="button">
							<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/delete.png" />
							<?php eT("Delete current question group"); ?>
						</a>
					<?php else: ?>
						<a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid/gid/$gid"); ?>'  class="btn btn-default" onclick="alert('<?php eT("Impossible to delete this group because there is at least one question having a condition on its content","js"); ?>'); return false;">
							<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/delete.png" />
							<?php eT("Delete current question group"); ?>
						</a>
					<?php endif; ?>
				<?php else:?>

			    	<span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Impossible to delete this group because there is at least one question having a condition on its content","js"); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
				    	<button type="button" class="btn btn-default btntooltip" disabled="disabled">
							<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/delete.png" />
							<?php eT("Delete current question group"); ?>
				    	</button>
			    	</span>
				<?php endif; ?>
			<?php endif; ?>
			
			<?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export')):?>
				<a class="btn btn-default" href="<?php echo $this->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>" role="button">
					<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/dumpgroup.png" />
					<?php eT("Export this question group"); ?>
				</a>		
			<?php endif; ?>
		
		<?php endif; ?>
				

    	</div>
    	<div class="col-md-4 col-md-offset-8 text-right">
    			<?php if(isset($questiongroupbar['savebutton']['form'])):?>
	            	<a class="btn btn-success" href="#" role="button" id="save-button">
	            		<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
	            		<?php eT("Save");?>
	            	</a>
	
	            	<a class="btn btn-default" href="<?php echo $this->createUrl("admin/survey/sa/listquestiongroups/surveyid/282267{$surveyid}"); ?>" role="button">
	            		<span class="glyphicon glyphicon-saved" aria-hidden="true"></span>
	            		<?php eT("Save and close");?>
	            	</a>
	            <?php endif;?>
            	
            	<?php if(isset($questiongroupbar['closebutton']['url'])):?>
            		<!-- $this->createUrl("admin/survey/sa/listquestiongroups/surveyid/{$surveyid}"); ?>-->
	            	<a class="btn btn-danger" href="<?php echo $this->createUrl($questiongroupbar['closebutton']['url']); ?>" role="button">
	            		<span class="glyphicon glyphicon-close" aria-hidden="true"></span>
	            		<?php eT("Close");?>
	            	</a>
            	<?php endif;?>
            	
				<?php if(isset($questiongroupbar['returnbutton']['url'])):?>
					<a class="btn btn-default" href="<?php echo $questiongroupbar['returnbutton']['url']; ?>" role="button">
						<span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>
						<?php echo $questiongroupbar['returnbutton']['text'];?>
					</a>
				<?php endif;?>




            	
    	</div>
    </div>
</div>