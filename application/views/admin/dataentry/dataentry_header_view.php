<div class='header ui-widget-header'><?php $clang->eT("Data entry"); ?> - <?php
		if ($subaction == "edit") {
	            echo sprintf($clang->gT("Editing response (ID %s)"), $id);
	    } else {
	            echo sprintf($clang->gT("Viewing response (ID %s)"), $id);
	    }
    ?>
</div>

<form method='post' action='<?php echo $this->createUrl('/admin/dataentry/update'); ?>' name='editresponse' id='editresponse'>
   <table id='responsedetail' width='99%' align='center'>
