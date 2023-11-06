<?php
    echo PrepareEditorScript(false, $this);
    $count = 0;
?>
<div id='edit-group' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-sm-12 content-right">
            <div class="pagetitle h3"><?php eT("Edit Group"); ?></div>
            <ul class="nav nav-tabs" id="edit-group-language-selection">
                <?php foreach ($tabtitles as $i=>$eachtitle):?>
                    <li role="presentation" class="<?php if($count==0) {echo "active"; $count++;}?>">
                        <a role="tab" data-toggle="tab" href="#editgrp_<?php echo $i;?>">
                            <?php echo $eachtitle;?>
                        </a>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php echo CHtml::form(array("questionGroupsAdministration/saveQuestionGroupData/sid/{$surveyid}"), 'post', array('id'=>'frmeditgroup', 'name'=>'frmeditgroup', 'class'=>'form30 ')); ?>
                    
                    <input type="hidden" name="questionGroup[gid]" id="questionGroup[gid]" value="<?=$oQuestionGroup['gid']?>">
                    <input type="hidden" name="questionGroup[sid]" id="questionGroup[sid]" value="<?=$oQuestionGroup['sid']?>"> 
                    <input type="hidden" name="questionGroup[group_order]" id="questionGroup[group_order]" value="<?=$oQuestionGroup['group_order']?>"> 

                    <div class="tab-content">

                        <?php foreach ($tabtitles as $i=>$eachtitle):?>
                            <div id="editgrp_<?php echo $i;?>" class="tab-pane fade in <?php if($count==1) {echo "active"; $count++;}?> center-box">

                                <div class="form-group">
                                    <label class="control-label " id="question-group-title-<?=$aGroupData[$i]['language']?>"><?php eT("Title:"); ?></label>
                                    <div class="">
                                        <?php echo CHtml::textField("questionGroupI10N[{$aGroupData[$i]['language']}][group_name]",$aGroupData[$i]['group_name'],array('class'=>'form-control','size'=>"80",'maxlength'=>'200','id'=>"group_name_{$aGroupData[$i]['language']}")); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label" for="description_<?php echo $aGroupData[$i]['language']; ?>"><?php eT("Description:"); ?></label>
                                    <div class="">
                                        <div class="htmleditor input-group">
                                            <?php echo CHtml::textArea("questionGroupI10N[{$aGroupData[$i]['language']}][description]",$aGroupData[$i]['description'],array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"description_{$aGroupData[$i]['language']}")); ?>
                                            <?php echo getEditor("group-desc","description_".$aGroupData[$i]['language'], "[".gT("Description:", "js")."](".$aGroupData[$i]['language'].")",$surveyid,$gid,'',$action); ?>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <div class="form-group">
                        <label class="control-label " id="randomization-group"><?php eT("Randomization group:"); ?></label>
                        <div class="">
                            <?php echo CHtml::textField("questionGroup[randomization_group]",$oQuestionGroup['randomization_group'],array('class'=>'form-control','size'=>"20",'maxlength'=>'20','id'=>"randomization_group")); ?>
                        </div>
                    </div>

                    <!-- Relevance Equation -->
                    <div class="form-group">
                        <label class="control-label " id="relevance-group"><?php eT("Condition:"); ?></label>
                        <div class="input-group">
                          <div class="input-group-addon">{</div>
                            <?php  echo CHtml::textArea("questionGroup[grelevance]",$oQuestionGroup['grelevance'],array('class'=>'form-control','cols'=>'20','rows'=>'1','id'=>"grelevance")); ?>
                            <div class="input-group-addon">}</div>
                          </div>
                        </div>
                    <input type="submit" class="btn btn-primary hidden" value="Save" role="button" aria-disabled="false">
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Reset topbar to "non-extended" mode.
// If this view wasn't loaded by ajax (ex: from the side menu) this wouldn't be necessary
Yii::app()->getClientScript()->registerScript(
    "EditGroup_topbar_switch", 'window.EventBus.$emit("doFadeEvent", false);', 
    LSYii_ClientScript::POS_END
);
?>
