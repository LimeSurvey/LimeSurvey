<div class='header ui-widget-header'><?php echo $clang->gT("Data entry"); ?></div>
	<div class='messagebox ui-corner-all'>
		<div class='successheader'><?php echo $clang->gT("Record Deleted"); ?> (ID: <?php echo $id; ?>)</div>
		<br /><br />
        <input type='submit' value='<?php echo $clang->gT("Browse responses"); ?>' onclick="window.open('<?php echo $this->createUrl('/').'/admin/browse/surveyid/'.$surveyid.'/all'; ?>, '_top')" />
        <br /><br />
    </div>
