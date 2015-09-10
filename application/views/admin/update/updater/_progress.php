<?php
/**
 * This view display the left progress "menus" (steps such as welcome, or pre-installation check, etc.)
 * The ajax code change the active step.
 */
?>
    <h3 class="maintitle"><?php eT("Progress"); ?></h3>
	<ol>
	    <li id ="step0Updt" class="on">
	        <span id="welcome"><?php eT("Welcome"); ?></span>
	        <span id="newKey" style="display : none;"><?php eT("New key"); ?></span>
	    </li>
	    
	    <li id="step1Updt" class="off">
	        <span><?php eT("Pre-installation check"); ?></span>
	    </li>
	    <li id="step2Updt" class="off">
	        <span><?php eT("Change Log"); ?></span>
	    </li>
	    <li id="step3Updt" class="off">
	        </pan><?php eT("File System"); ?></span>
	    </li>
	    <li id="step4Updt" class="off">
	        <span><?php eT("Backup"); ?></span>
	    </li>
	    <li id="step5Updt" class="off">
	        <span><?php eT("Download"); ?></span>
	    </li>
	    <li id="step6Updt" class="off">
	        <span><?php eT("End"); ?></span>
	    </li>			    
	</ol>	        
