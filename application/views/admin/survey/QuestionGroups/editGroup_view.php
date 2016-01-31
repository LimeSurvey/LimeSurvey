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
                <?php echo CHtml::form(array("admin/questiongroups/sa/update/gid/{$gid}"), 'post', array('id'=>'frmeditgroup', 'name'=>'frmeditgroup', 'class'=>'form30 form-horizontal')); ?>

                    <div class="tab-content">

                        <?php foreach ($tabtitles as $i=>$eachtitle):?>
                            <div id="editgrp_<?php echo $i;?>" class="tab-pane fade in <?php if($count==1) {echo "active"; $count++;}?> center-box">

                                <div class="form-group">
                                    <label class="control-label col-sm-2" id="question-group-title"><?php eT("Title:"); ?></label>
                                    <div class="col-sm-3">
                                        <input type="text" maxlength='100' size='80' class="form-control"  name='group_name_<?php echo $aGroupData[$i]['language']; ?>' id='group_name_<?php echo $aGroupData[$i]['language']; ?>' value="<?php echo htmlspecialchars($aGroupData[$i]['group_name']); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="description_<?php echo $aGroupData[$i]['language']; ?>"><?php eT("Description:"); ?></label>
                                    <div class="col-sm-3 htmleditorboot">
                                        <textarea cols='70' rows='8' id='description_<?php echo $aGroupData[$i]['language']; ?>' name='description_<?php echo $aGroupData[$i]['language']; ?>'>
                                            <?php echo htmlspecialchars($aGroupData[$i]['description']); ?>
                                        </textarea>
                                        <?php echo getEditor("group-desc","description_".$aGroupData[$i]['language'], "[".gT("Description:", "js")."](".$aGroupData[$i]['language'].")",$surveyid,$gid,'',$action); ?>
                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2" id="randomization-group"><?php eT("Randomization group:"); ?></label>
                        <div class="col-sm-3">
                            <input type='text' maxlength='20' size='20'class="form-control" name='randomization_group' id='randomization_group' value="<?php echo $aGroupData[$aBaseLanguage]['randomization_group']; ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2" id="randomization-group"><?php eT("Relevance equation:"); ?></label>
                        <div class="col-sm-3">
                            <textarea cols='1' rows='1' id='grelevance' class="form-control" name='grelevance'><?php echo $aGroupData[$aBaseLanguage]['grelevance']; ?></textarea>
                        </div>
                    </div>

                    <input type="submit" class="hidden" value="Save" role="button" aria-disabled="false">
                </form>
            </div>
        </div>
    </div>
</div>
