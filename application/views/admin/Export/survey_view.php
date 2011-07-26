<form id='exportstructure' name='exportstructure' action='<?php echo site_url("admin/export/survey/$surveyid/");?>' method='post'>
<div class='header ui-widget-header'>
<?php echo $clang->gT("Export Survey Structure");?></div><br />
<ul style='margin-left:35%;'>
<li><input type='radio' class='radiobtn' name='action' value='exportstructurexml' checked='checked' id='surveyxml'/>
<label for='surveycsv'>
<?php echo $clang->gT("LimeSurvey XML survey file (*.lss)");?></label></li>

<li><input type='radio' class='radiobtn' name='action' value='exportstructurequexml'  id='queXML' />
<label for='queXML'>
<?php echo str_replace('queXML','<a href="http://quexml.sourceforge.net/" target="_blank">queXML</a>',$clang->gT("queXML Survey XML Format (*.xml)"));?>
</label></li>

<?php if($this->config->item("export4lsrc")) { ?>
    <li><input type='radio' class='radiobtn' name='type' value='structureLsrcCsv'  id='LsrcCsv' onclick="this.form.action.value='exportstructureLsrcCsv'" />
    <label for='LsrcCsv'><?php echo $clang->gT("Save for Lsrc (*.csv)");?></label></li>
<?php } ?>
</ul>

<p><input type='submit' value='<?php echo $clang->gT("Export To File");?>' />
<input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
</form>
 