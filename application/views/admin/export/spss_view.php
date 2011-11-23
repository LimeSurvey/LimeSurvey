<div class='header ui-widget-header'><?php echo $clang->gT("Export result data to SPSS");?></div>

<form action='<?php echo $this->createUrl("admin/export/sa/exportspss/sid/$surveyid/");?>' id='exportspss' method='post'><ul>
<li><label for='filterinc'><?php echo $clang->gT("Data selection:");?></label><select id='filterinc' name='filterinc' onchange='this.form.submit();'>
<option value='filter' <?php echo $selecthide;?>><?php echo $clang->gT("Completed responses only");?></option>
<option value='show' <?php echo $selectshow;?>><?php echo $clang->gT("All responses");?></option>
<option value='incomplete' <?php echo$selectinc;?>><?php echo $clang->gT("Incomplete responses only");?></option>
</select></li>

<li><label for='spssver'><?php echo $clang->gT("SPSS version:");?></label><select id='spssver' name='spssver' onchange='this.form.submit();'>
<?php if ($spssver == 1) $selected = "selected='selected'"; else $selected = "";?>
<option value='1' <?php echo $selected;?>><?php echo $clang->gT("Prior to 16");?></option>
<?php if ($spssver == 2) $selected = "selected='selected'"; else $selected = ""; ?>
<option value='2' <?php echo $selected;?>><?php echo $clang->gT("16 or up");?></option>
</select></li>
<input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
<input type='hidden' name='action' value='exportspss' /></li>
<li><label for='dlstructure'><?php echo $clang->gT("Step 1:");?></label><input type='submit' name='dlstructure' id='dlstructure' value='<?php echo $clang->gT("Export syntax");?>'/></li>
<li><label for='dldata'/><?php echo $clang->gT("Step 2:");?></label><input type='submit' name='dldata' id='dldata' value='<?php echo $clang->gT("Export data");?>'/></li></ul>
</form>

<p><div class='messagebox ui-corner-all'><div class='header ui-widget-header'><?php echo $clang->gT("Instructions for the impatient");?></div>
<br/><ol style='margin:0 auto; font-size:8pt;'>
<li><?php echo $clang->gT("Download the data and the syntax file");?></li>
<li><?php echo $clang->gT("Open the syntax file in SPSS in Unicode mode");?></li>
<li><?php echo $clang->gT("Edit the 4th line and complete the filename with a full path to the downloaded data file");?></li>
<li><?php echo $clang->gT("Choose 'Run/All' from the menu to run the import");?></li>
</ol><p>
	<?php echo $clang->gT("Your data should be imported now");?></div>