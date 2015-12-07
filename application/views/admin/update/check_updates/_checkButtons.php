<?php
/**
 * This view display the ckeck buttons, it is the first to be loaded when no step is setted.
 * It give the possibility to user to choose how often the updates must be checked, wich branches (stable and/or unstable)
 * and provides the button to check available updates.
 *
 * @var int $thisupdatecheckperiod  : the current check period in days (0 => never ; 1 => everyday ; 7 => every week, etc..  )
 * @var obj $clang : the translate object, now moved to global function TODO : remove it
 * @var $updatelastcheck
 * @var $UpdateNotificationForBranch
 *
 */
?>
<div id="ajaxupdaterLayoutLoading" style="text-align : center; margin-top: 200px; margin-bottom: 200px; display: none">
	<p><?php eT('Please wait, data loading...');?></p>
    <div class="preloader loading">
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
    </div>
</div>

<div id="preUpdaterContainer">
	<div class='header ui-widget-header'><?php echo eT("Updates"); ?></div><br/>
	<ul>




	<!-- FOR AJAX REQUEST -->
	<input type="hidden" id="updateBranch" value="<?php echo $UpdateNotificationForBranch;?>"/>

	<!-- The js will inject inside this li the HTML of the update buttons -->
	<li style="text-align: center" id="udapteButtonsContainer">
	    <div class="preloader loading">
            <span class="slice"></span>
            <span class="slice"></span>
            <span class="slice"></span>
            <span class="slice"></span>
            <span class="slice"></span>
            <span class="slice"></span>
        </div>
		<span id="updatesavailable"></span>
	</li>
	</ul>
</div>
