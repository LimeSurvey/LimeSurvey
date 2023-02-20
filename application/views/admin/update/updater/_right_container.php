<?php
/**
 * This view display the right container of the updater
 * The ajax code change the active step.
 */
?>

	<!-- the ajax loader -->
	<div id="ajaxContainerLoading" >
		<p><?php eT('Please wait, loading data...');?></p>
        <i class="ri-loader-2-fill remix-3x remix-spin lsLoadingStateIndicator"></i>
	</div>

	<!-- Here come the different steps content. Content is loaded by the ajax request (see ./steps for html views)	-->
	<div id="updaterContainer">
		<!-- content loaded by ajax -->
	</div>
