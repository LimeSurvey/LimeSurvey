<div class="header ui-widget-header"><?php echo $clang->gT("Edit survey text elements"); ?></div>
    <form id="addnewsurvey" class="form30" name="addnewsurvey" action="<?php echo $this->createUrl("admin/database/index/updatesurveylocalesettings");  ?>" method="post">
            <div id="tabs">
            	<?php echo $additional_content; ?>
            
            <?php if($has_permissions): ?>
	            <p>
	            	<input type="submit" class="standardbtn" value="<?php echo $clang->gT("Save"); ?>" />
	                <input type="hidden" name="action" value="updatesurveylocalesettings" />
	                <input type="hidden" name="sid" value="<?php echo $surveyid; ?>" />
	                <input type="hidden" name="language" value="<?php echo $surveyls_language; ?>" />
	            </p>
	        <?php endif; ?>
    </form>
