<form id='exportstructureQuestion' name='exportstructureQuestion' action='<?php echo $this->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>' method='post'>
<div class='header ui-widget-header'><?php echo $clang->gT("Export question structure");?></div>
<ul>
<li>
<input type='radio' class='radiobtn' name='type' value='structurecsvQuestion' checked='checked' id='surveycsv'
onclick="this.form.action.value='exportstructurecsvQuestion'"/>
<label for='surveycsv'><?php echo $clang->gT("LimeSurvey group file (*.csv)");?></label></li>
<?php if(Yii::app()->getConfig('export4lsrc')) { ?>
    <li><input type='radio' class='radiobtn' name='type' value='structureLsrcCsvQuestion'  id='LsrcCsv' onclick="this.form.action.value='exportstructureLsrcCsvQuestion'" />
    <label for='LsrcCsv'><?php echo $clang->gT("Save for Lsrc (*.csv)");?></label></li>
<?php } ?>
</ul>
<p>
<input type='submit' value='<?php echo $clang->gT("Export to file");?>' />
<input type='hidden' name='sid' value='$surveyid' />
<input type='hidden' name='gid' value='$gid' />
<input type='hidden' name='action' value='exportstructurecsvQuestion' />
</form>