<div class='header ui-widget-header'><?php echo $clang->gT("Add question group"); ?></div>
<div id='tabs'><ul>
    <?php foreach ($grplangs as $grouplang)
        { ?>
            <li><a href="#<?php echo $grouplang; ?>"><?php echo GetLanguageNameFromCode($grouplang,false); 
            if ($grouplang==$baselang) { ?>(<?php echo $clang->gT("Base language"); ?>) <?php } ?>
            </a></li>
        <?php }
    if (bHasSurveyPermission($surveyid,'surveycontent','import'))
    { ?>
        <li><a href="#import"><?php echo $clang->gT("Import question group"); ?></a></li>
            		
   	<?php } ?>
    </ul>
        
    
    
    <form action='<?php echo site_url("admin/database/index/"); ?>' class='form30' id='newquestiongroup' name='newquestiongroup' method='post' onsubmit=" if (1==0 
        
    <?php foreach ($grplangs as $grouplang)
    { ?>
        || document.getElementById('group_name_$grouplang').value.length==0 
    <?php } ?>
     ) {alert ('<?php echo $clang->gT("Error: You have to enter a group title for each language.",'js'); ?>'); return false;}" >
        
    <?php foreach ($grplangs as $grouplang)
    { ?>
        <div id="<?php echo $grouplang; ?>">
        <ul>
        <li>
        <label for='group_name_$grouplang'><?php echo $clang->gT("Title"); ?>:</label>
        <input type='text' size='80' maxlength='100' name='group_name_<?php echo $grouplang; ?>' id='group_name_<?php echo $grouplang; ?>' /><font color='red' face='verdana' size='1'> <?php echo $clang->gT("Required"); ?></font></li>
        <li><label for='description_<?php echo $grouplang; ?>'><?php echo $clang->gT("Description:"); ?></label>
        <textarea cols='80' rows='8' id='description_<?php echo $grouplang; ?>' name='description_<?php echo $grouplang; ?>'></textarea>
        <?php echo getEditor("group-desc","description_".$grouplang, "[".$clang->gT("Description:", "js")."](".$grouplang.")",$surveyid,'','',$action); ?>
        </li>
        </ul>
        <p><input type='submit' value='<?php echo $clang->gT("Save question group"); ?>' />
        </div>
    <?php } ?>
        
    <input type='hidden' name='action' value='insertquestiongroup' />
    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
    </form>
        
        
    
    <?php if (bHasSurveyPermission($surveyid,'surveycontent','import'))
    { ?>
        <div id="import">
        <form enctype='multipart/form-data' class='form30' id='importgroup' name='importgroup' action='$scriptname' method='post' onsubmit='return validatefilename(this,"<?php echo $clang->gT('Please select a file to import!','js'); ?>");'>
        <ul>
        <li>
        <label for='the_file'><?php echo $clang->gT("Select question group file (*.lsg/*.csv):"); ?></label>
        <input id='the_file' name="the_file" type="file" size="35" /></li>
        <li><label for='translinksfields'><?php echo $clang->gT("Convert resource links?"); ?></label>
        <input id='translinksfields' name="translinksfields" type="checkbox" checked="checked"/></li></ul>
        <p><input type='submit' value='<?php echo $clang->gT("Import question group"); ?>' />
        <input type='hidden' name='action' value='importgroup' />
        <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
        </form>
        
        </div>
    <?php } ?>
        	 
        
        
    
    </div>