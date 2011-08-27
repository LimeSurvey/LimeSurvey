<form id='exportstructureGroup' name='exportstructureGroup' action='<?php echo site_url("admin/export/group/$surveyid/$gid");?>' method='post'>
<div class='header ui-widget-header'><?php echo $clang->gT("Export group structure");?></div>
<ul>
<li>
<input type='radio' class='radiobtn' name='type' value='structurecsvGroup' checked='checked' id='surveycsv'
onclick="this.form.action.value='exportstructurecsvGroup'"/>
<label for='surveycsv'><?php echo $clang->gT("LimeSurvey group file (*.csv)");?></label></li>
<?php if($this->config->item('export4lsrc')) { ?>
    <li><input type='radio' class='radiobtn' name='type' value='structureLsrcCsvGroup'  id='LsrcCsv' onclick="this.form.action.value='exportstructureLsrcCsvGroup'" />
    <label for='LsrcCsv'><?php echo $clang->gT("Save for Lsrc (*.csv)");?></label></li>
<?php } ?>
</ul>
<p>
<input type='submit' value='<?php echo $clang->gT("Export to file");?>' />
<input type='hidden' name='sid' value='$surveyid' />
<input type='hidden' name='gid' value='$gid' />
<input type='hidden' name='action' value='exportstructurecsvGroup' />
</form>