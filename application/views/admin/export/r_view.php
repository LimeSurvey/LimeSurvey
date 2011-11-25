<div class='header ui-widget-header'><?php echo $clang->gT("Export result data to R");?></div>
<form action='<?php echo $this->createUrl("admin/export/sa/exportr/sid/$surveyid");?>' id='exportspss' method='post'><ul>
<li><label for='filterinc'><?php echo $clang->gT("Data selection:");?></label><select id='filterinc' name='filterinc' onchange='this.form.submit();'>
<option value='filter' <?php echo$selecthide;?>><?php echo $clang->gT("Completed responses only");?></option>
<option value='show' <?php echo$selectshow;?>><?php echo $clang->gT("All responses");?></option>
<option value='incomplete' <?php echo$selectinc;?>><?php echo $clang->gT("Incomplete responses only");?></option>
</select></li>

<input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
<input type='hidden' name='action' value='exportr' /></li>
<li><label for='dlstructure'><?php echo $clang->gT("Step 1:");?></label><input type='submit' name='dlstructure' id='dlstructure' value='<?php echo $clang->gT("Export R syntax file");?>'/></li>
<li><label for='dldata'/><?php echo $clang->gT("Step 2:");?></label><input type='submit' name='dldata' id='dldata' value='<?php echo $clang->gT("Export .csv data file");?>'/></li></ul>
</form>

<p><div class='messagebox ui-corner-all'><div class='header ui-widget-header'><?php echo $clang->gT("Instructions for the impatient");?></div>
<br/><ol style='margin:0 auto; font-size:8pt;'>
<li><?php echo $clang->gT("Download the data and the syntax file.");?></li>
<li><?php echo $clang->gT("Save both of them on the R working directory (use getwd() and setwd() on the R command window to get and set it)");?></li>
<li><?php echo sprintf($clang->gT("digit:       source(\"%s\", encoding = \"UTF-8\")        on the R command window"), $filename);?></li>
</ol><br />
<?php echo $clang->gT("Your data should be imported now, the data.frame is named \"data\", the variable.labels are attributes of data (\"attributes(data)\$variable.labels\"), like for foreign:read.spss.");?>
</div>