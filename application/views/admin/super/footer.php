<?php
/**
 * Footer view
 * Inserted in all pages
 */
$systemInfos = [
    gT('LimeSurvey version') => Yii::app()->getConfig('versionnumber'),
    gT('LimeSurvey build') => Yii::app()->getConfig('buildnumber') == '' ? 'github' : Yii::app()->getConfig('buildnumber'),
    gT('Operating system') => php_uname(),
    gT('PHP version') => phpversion(),
    gT('Webserver name') => $_SERVER['SERVER_NAME'],
    gT('Webserver software') => $_SERVER['SERVER_SOFTWARE'],
    gT('Webserver info') => isset($_SERVER['SERVER_SIGNATURE']) ? $_SERVER['SERVER_SIGNATURE'] : $_SERVER['SERVER_PROTOCOL'],
    gT('Database driver') => Yii::app()->db->driverName,
    gT('Database version') => Yii::app()->db->clientVersion,
    gT('Database serverinfo') => Yii::app()->db->serverInfo,
    gT('Database serverversion') => Yii::app()->db->serverVersion
];
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
                <a  title='<?php eT("Visit our website!"); ?>' href='https://www.limesurvey.org' target='_blank'><?=gT('LimeSurvey')?></a><br />
                <?php if(Permission::model()->hasGlobalPermission('superadmin','read')) { ?> 
                    <a href="#modalSystemInformation" data-toggle="modal" title="<?=gT("Get system information")?>"> 
                <?php } ?>
                <?php echo $versiontitle."  ".$versionnumber.$buildtext;?>
                <?php if(Permission::model()->hasGlobalPermission('superadmin','read')) { ?>
                    </a> 
                <?php } ?>
            </div>
        </div>
    </div>
</footer>
<div id="bottomScripts">
    <###end###>
</div>

<!-- Modal for system information -->

<div id="modalSystemInformation" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="h3 modal-title"><?php eT("System information"); ?></div>
            </div>
            <div class="modal-body">
                <?php if(Permission::model()->hasGlobalPermission('superadmin','read')) { ?>
                    <h4><?=gT("Your system configuration:")?></h4>
                    <ul class="list-group">
                        <?php foreach($systemInfos as $name => $systemInfo){ ?>
                            <li class="list-group-item">
                                <div class="ls-flex-row">
                                    <div class="col-4"><?=$name?></div>
                                    <div class="col-8"><?=$systemInfo?></div>
                                </div>
                            </li>   
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <h4><?=gT("To get the system information, please contact your Administrator")?></h4>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

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
