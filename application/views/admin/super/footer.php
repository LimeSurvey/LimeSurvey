<?php
/**
 * Footer view
 * Inserted in all pages
 */
?>
<!-- Footer -->
<footer class='footer'>
    <div class="container-fluid">
        <div class="row">
            <!-- Link to manual -->
            <div class="col-xs-6 col-sm-4 ">
                <a href='http://manual.limesurvey.org' onclick='function go(ev) { ev.preventDefault(); var win = window.open("http://manual.limesurvey.org", "_blank"); win.focus(); }; go(event);'>
                    <span class="fa fa-info-sign" id="info-footer"></span>
		    <span class="sr-only"><?php eT('Limesurvey online manual'); ?></span>
                </a>
            </div>

            <!-- Support / Donate -->
            <div  class="col-xs-6 col-sm-4 text-center"  >
                <a href='http://donate.limesurvey.org' target="_blank">
                    <img alt='<?php eT("Support this project - Donate to "); ?>LimeSurvey' title='<?php eT("Support this project - Donate to "); ?>LimeSurvey!' src='<?php echo Yii::app()->getConfig('adminimageurl');?>donate.png'/>
                </a>
            </div>

            <!-- Lime survey website -->
            <div class="col-xs-12 col-sm-4 text-right">
                <a  title='<?php eT("Visit our website!"); ?>' href='https://www.limesurvey.org' target='_blank'>LimeSurvey</a><br /><?php echo $versiontitle."  ".$versionnumber.$buildtext;?>
            </div>
        </div>
    </div>
</footer>

<!-- Modal for confirmation -->
<?php
/**

    Example of use:

    <button 
        data-toggle='modal'
        data-target='#confirmation-modal'
        data-onclick='(function() { LS.plugin.cintlink.cancelOrder("<?php echo $order->url; ?>"); })'
        class='btn btn-warning btn-sm' 
    >

 */
?>
<div id="confirmation-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="h3 modal-title"><?php eT("Confirm"); ?></div>
            </div>
            <div class="modal-body">
                <p class='modal-body-text'><?php eT("Are you sure?"); ?></p>
                <!-- the ajax loader -->
                <div id="ajaxContainerLoading" >
                    <p><?php eT('Please wait, loading data...');?></p>
                    <div class="preloader loading">
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                    </div>
                </div>

            </div>
            <div class="modal-footer modal-footer-yes-no">
                <a class="btn btn-primary btn-ok"><span class='fa fa-check'></span>&nbsp;<?php eT("Yes"); ?></a>
                <button type="button" class="btn btn-danger" data-dismiss="modal"><span class='fa fa-ban'></span>&nbsp;<?php eT("No"); ?></button>
            </div>
            <div class="modal-footer-close modal-footer" style="display: none;">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <?php eT("Close"); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for errors -->
<div id="error-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content panel-danger">
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="h3 modal-title"><?php eT("Error"); ?></div>
            </div>
            <div class="modal-body">
                <p class='modal-body-text'><?php eT("An error occurred."); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">&nbsp;<?php eT("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for success -->
<div id="success-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content panel-success">
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="h3 modal-title"><?php eT("Success"); ?></div>
            </div>
            <div class="modal-body">
                <p class='modal-body-text'><?php /* This must be set in Javascript */ ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">&nbsp;<?php eT("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for admin notifications -->
<div id="admin-notification-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">  <?php // JS add not.type as panel-type, e.g. panel-default, panel-danger ?>
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="h3 modal-title">
			<span class="sr-only"><?php eT("Notifications"); ?></span>
		</div>
                <span class='notification-date text-muted'></span>
            </div>
            <div class="modal-body">
                <p class='modal-body-text'></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">&nbsp;<?php eT("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Yet another general purpose modal, this one used by AjaxHelper to display JsonOutputModal messages -->
<div id="ajax-helper-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
        </div>
    </div>
</div>

</body>
</html>
