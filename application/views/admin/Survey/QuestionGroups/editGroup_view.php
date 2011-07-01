<?php if (isset($i))
{ ?>
    <div class='settingrow'><span class='settingcaption'><label for='group_name_<?php echo $esrow['language']; ?>'><?php echo $clang->gT("Title"); ?>:</label></span>
    <span class='settingentry'><input type='text' maxlength='100' size='80' name='group_name_<?php echo $esrow['language']; ?>' id='group_name_<?php echo $esrow['language']; ?>' value="<?php echo $esrow['group_name']; ?>" />
    </span>
    </div>
    <div class='settingrow'><span class='settingcaption'><label for='description_<?php echo $esrow['language']; ?>'><?php echo $clang->gT("Description:"); ?></label>
    </span><span class='settingentry'><textarea cols='70' rows='8' id='description_<?php echo $esrow['language']; ?>' name='description_<?php echo $esrow['language']; ?>'><?php echo $esrow['description']; ?></textarea>
    <?php echo getEditor("group-desc","description_".$esrow['language'], "[".$clang->gT("Description:", "js")."](".$esrow['language'].")",$surveyid,$gid,'',$action); ?>
    </span>
    </div>
    <div style='clear:both'></div>
    
    
<?php } 
else
{ ?>
    
    <div class='settingrow'><span class='settingcaption'><label for='group_name_<?php echo $esrow['language']; ?>'><?php echo $clang->gT("Title"); ?>:</label></span>
    <span class='settingentry'><input type='text' maxlength='100' size='80' name='group_name_<?php echo $esrow['language']; ?>' id='group_name_<?php echo $esrow['language']; ?>' value="<?php echo $esrow['group_name']; ?>" />
    </span></div>
    <div class='settingrow'><span class='settingcaption'><label for='description_<?php echo $esrow['language']; ?>'><?php echo $clang->gT("Description:"); ?></label>
    </span><span class='settingentry'><textarea cols='70' rows='8' id='description_<?php echo $esrow['language']; ?>' name='description_<?php echo $esrow['language']; ?>'><?php echo $esrow['description']; ?></textarea>
    <?php echo getEditor("group-desc","description_".$esrow['language'], "[".$clang->gT("Description:", "js")."](".$esrow['language'].")",$surveyid,$gid,'',$action); ?>
    </span></div><div style='clear:both'></div>
<?php }?>