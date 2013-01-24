<div class='warningheader'><?php $clang->eT("Error"); ?></div>
	<?php echo $error_msg; ?>
	<br /><br />
	<input type='submit' value='<?php $clang->eT("Back to Response Import"); ?>' onclick=\"window.open('<?php echo $this->createUrl('admin/dataentry/sa/vvimport/surveyid/'.$surveyid); ?>', '_top')\">
</div><br />&nbsp;
