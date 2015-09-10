<?php 
	echo PrepareEditorScript(false, $this);
	$count = 0;
?>

<div class="side-body" id="edit-group">
	<div class="row">
		<div class="col-lg-12 content-right">
			<h3><?php eT("Edit Group"); ?></h3>
			<ul class="nav nav-tabs" id="edit-group-language-selection">
				<?php foreach ($tabtitles as $i=>$eachtitle):?>
					<li role="presentation" class="<?php if($count==0) {echo "active"; $count++;}?>">
						<a data-toggle="tab" href="#editgrp_<?php echo $i;?>">
							<?php echo $eachtitle;?>
						</a>
					</li>
				<?php endforeach;?>
			</ul>	
	</div>		
	<div class="row">
		<div class="col-lg-12">			
			<?php echo CHtml::form(array("admin/questiongroups/sa/update/gid/{$gid}"), 'post', array('id'=>'frmeditgroup', 'name'=>'frmeditgroup', 'class'=>'form30')); ?>
			<div class="tab-content">
        	<?php foreach ($tabtitles as $i=>$eachtitle):?>
				<div id="editgrp_<?php echo $i;?>" class="tab-pane fade in <?php if($count==1) {echo "active"; $count++;}?> col-lg-6 center-box">
					<div class="input-group">
					  <span class="input-group-addon" id="question-group-title"><?php eT("Title"); ?></span>
					  <input type="text" maxlength='100' size='80' class="form-control"  name='group_name_<?php echo $aGroupData[$i]['language']; ?>' id='group_name_<?php echo $aGroupData[$i]['language']; ?>' value="<?php echo htmlspecialchars($aGroupData[$i]['group_name']); ?>">
					</div>					
					
					<div class="form-group form-group-lg">
						<label class="col-sm-2 control-label" for="description_<?php echo $aGroupData[$i]['language']; ?>"><?php eT("Description:"); ?></label>
						<div class="htmleditorboot">
						<textarea cols='70' rows='8' id='description_<?php echo $aGroupData[$i]['language']; ?>' name='description_<?php echo $aGroupData[$i]['language']; ?>'>
							<?php echo htmlspecialchars($aGroupData[$i]['description']); ?>
						</textarea>
						<?php echo getEditor("group-desc","description_".$aGroupData[$i]['language'], "[".gT("Description:", "js")."](".$aGroupData[$i]['language'].")",$surveyid,$gid,'',$action); ?>
						</div>
						
					</div>
					
				</div>
        	<?php endforeach;?>
			</div>
		</div>
	</div>		
	<div class="row">			
			<div class="col-lg-6">
					<div class="input-group">
					  <span class="input-group-addon" id="randomization-group"><?php eT("Randomization group:"); ?></span>
					  <input type='text' maxlength='20' size='20'class=" form-control" name='randomization_group' id='randomization_group' value="<?php echo $aGroupData[$aBaseLanguage]['randomization_group']; ?>" />
					</div>									
			</div>

			<div class="col-lg-6 pull-left">
					<div class="input-group">
					  <span class="input-group-addon" id="randomization-group"><?php eT("Relevance equation:"); ?></span>
					  <textarea cols='50' rows='1' id='grelevance' class=" form-control" name='grelevance'><?php echo $aGroupData[$aBaseLanguage]['grelevance']; ?></textarea>
					</div>									
			</div>


			</form>


		</div>
	</div>
</div>



