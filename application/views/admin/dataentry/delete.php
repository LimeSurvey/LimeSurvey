<div class='header ui-widget-header'><?php eT("Data entry"); ?></div>
	<div class='messagebox ui-corner-all'>
		<div class='successheader'><?php eT("Record Deleted"); ?> (ID: <?php echo $id; ?>)</div>
		<br /><br />
        <input type='submit' value='<?php eT("Browse responses"); ?>' onclick="window.open('<?php echo $this->createUrl("/admin/responses/sa/index/surveyid/{$surveyid}/all"); ?>', '_top');" />
        <br /><br />
    </div>
