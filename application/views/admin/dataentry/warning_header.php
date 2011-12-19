<div class='warningheader'><?php echo $clang->gT("Error"); ?></div>
	<?php echo $error_msg; ?>
	<br /><br />
	<input type='submit' value='<?php echo $clang->gT("Back to Response Import"); ?>' onclick=\"window.open('<?php echo $this->createUrl('admin/dataentry/sa/vvimport/surveyid/'.$surveyid); ?>', '_top')\">
</div><br />&nbsp;
