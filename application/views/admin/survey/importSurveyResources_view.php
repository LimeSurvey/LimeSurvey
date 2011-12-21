<div class='header ui-widget-header'><?php echo $clang->gT("Import survey resources"); ?></div>\n";
	<div class='messagebox ui-corner-all'>
		<strong><?php echo $clang->gT("Imported Resources for"); ?>" SID:</strong> <?php echo $surveyid; ?><br /><br />
        <div class="<?php echo $statusClass; ?>">
        	<?php echo $status; ?>
        </div>
        <br />
        <strong>
        	<u><?php echo $clang->gT("Resources Import Summary"); ?></u>
        </strong>
        <br />
        <?php echo $clang->gT("Total Imported files"); ?>: <?php echo $okfiles; ?><br />
        <?php echo $clang->gT("Total Errors"); ?>: <?php echo $errfiles; ?><br />
        <?php echo $additional_content; ?>
    	<input type='submit' value='<?php echo $clang->gT("Back"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/editsurveysettings/surveyid/'.$surveyid); ?>', '_top')" />\n";
	</div>
