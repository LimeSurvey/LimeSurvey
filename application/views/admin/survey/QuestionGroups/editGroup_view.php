<?php echo PrepareEditorScript(false, $this);?>

<div class='header ui-widget-header'><?php $clang->eT("Edit Group"); ?></div>
<?php echo CHtml::form(array("admin/questiongroups/sa/update/gid/{$gid}"), 'post', array('id'=>'frmeditgroup', 'name'=>'frmeditgroup', 'class'=>'form30')); ?>
    <div id='tabs'>
        <ul>
            <?php foreach ($tabtitles as $i=>$eachtitle){?>
                <li style='clear:none'><a href='#editgrp_<?php echo $i;?>'><?php echo $eachtitle;?></a></li><?php
            }?>
        </ul>
        <?php
            foreach ($tabtitles as $i=>$eachtitle)
            {?>

            <div id='editgrp_<?php echo $i;?>'>
                <ul><li><label for='group_name_<?php echo $aGroupData[$i]['language']; ?>'><?php $clang->eT("Title"); ?>:</label>
                        <input type='text' maxlength='100' size='80' name='group_name_<?php echo $aGroupData[$i]['language']; ?>' id='group_name_<?php echo $aGroupData[$i]['language']; ?>' value="<?php echo htmlspecialchars($aGroupData[$i]['group_name']); ?>" />
                    </li>
                    <li><label for='description_<?php echo $aGroupData[$i]['language']; ?>'><?php $clang->eT("Description:"); ?></label>
                        <div class="htmleditor">
                            <textarea cols='70' rows='8' id='description_<?php echo $aGroupData[$i]['language']; ?>' name='description_<?php echo $aGroupData[$i]['language']; ?>'><?php echo htmlspecialchars($aGroupData[$i]['description']); ?></textarea>
                            <?php echo getEditor("group-desc","description_".$aGroupData[$i]['language'], "[".$clang->gT("Description:", "js")."](".$aGroupData[$i]['language'].")",$surveyid,$gid,'',$action); ?>
                        </div>
                    </li>
                </ul>
                <div style='clear:both'></div>
            </div>
            <?php
        }?>


    </div>
    <ul>
        <li>
            <label for='randomization_group'><?php $clang->eT("Randomization group:"); ?></label><input type='text' maxlength='20' size='20' name='randomization_group' id='randomization_group' value="<?php echo $aGroupData[$aBaseLanguage]['randomization_group']; ?>" />
        </li>
        <li>
            <label for='grelevance'><?php $clang->eT("Relevance equation:"); ?></label>
            <textarea cols='50' rows='1' id='grelevance' name='grelevance'><?php echo $aGroupData[$aBaseLanguage]['grelevance']; ?></textarea>
        </li>
    </ul>
    <p>
        <input type='submit' class='standardbtn' value='<?php $clang->eT("Save");?>' />
    </p>
</form>

