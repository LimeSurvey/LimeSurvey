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
    gT('Web server name') => $_SERVER['SERVER_NAME'],
    gT('Web server software') => $_SERVER['SERVER_SOFTWARE'],
    gT('Web server info') => $_SERVER['SERVER_SIGNATURE'] ?? $_SERVER['SERVER_PROTOCOL']
];

// MSSQL does not support some of these attributes, so much
// catch possible PDO exception.

try {
    $systemInfos[gT('Database driver')] = Yii::app()->db->driverName;
} catch (Exception $ex) {
    $systemInfos[gT('Database driver')] = $ex->getMessage();
}

try {
    $systemInfos[gT('Database driver version')] = Yii::app()->db->clientVersion;
} catch (Exception $ex) {
    $systemInfos[gT('Database driver version')] = $ex->getMessage();
}

try {
    $systemInfos[gT('Database server info')] = Yii::app()->db->serverInfo;
} catch (Exception $ex) {
    $systemInfos[gT('Database server info')] = $ex->getMessage();
}

try {
    $systemInfos[gT('Database server version')] = Yii::app()->db->serverVersion;
} catch (Exception $ex) {
    $systemInfos[gT('Database server version')] = $ex->getMessage();
}

/* Fix array to string , see #13352 */
foreach ($systemInfos as $key => $systemInfo) {
    if (is_array($systemInfo)) {
        $systemInfos[$key] = json_encode($systemInfo, JSON_PRETTY_PRINT);
    }
}
$questionEditor = $questionEditor ?? false;
?>
<!-- Footer -->
<footer class="container-fluid footer d-flex flex-grow-1 flex-column justify-content-end text-end">
    <div>
        <a data-bs-toggle="tooltip" title='<?php eT("Visit our website!"); ?>' href='https://community.limesurvey.org' target='_blank'>LimeSurvey Community Edition</a>
        <?php if (Permission::model()->hasGlobalPermission('superadmin', 'read')) { ?>
            <a href="#modalSystemInformation" data-bs-toggle="modal" title="<?= gT("Get system information") ?>">
        <?php } ?>
            <?php echo $versiontitle . "  " . $versionnumber . $buildtext; ?>
            <?php if (Permission::model()->hasGlobalPermission('superadmin', 'read')) { ?>
            </a>
            <?php } ?>

        <!-- Support / Donate -->
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => '',
            'id' => 'donate-button',
            'text' => gT('Donate'),
            'icon' => 'ri-service-fill',
            'iconPosition' => 'right',
            'link' => 'https://donate.limesurvey.org',
            'htmlOptions' =>
            [
                'class' => 'btn btn-primary btn-sm ms-4',
                'title' => sprintf(gT("Support this project - Donate to %s!"), 'LimeSurvey'),
                'data-bs-toggle' => 'tooltip',
                'target' => '_blank'
            ],
        ]); ?>
    </div>
    <!-- Help button -->
    <?php $this->renderPartial('/admin/super/help_button_dropup', ['questionEditor' => $questionEditor]); ?>
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
                <h5 class="modal-title"><?php eT("System information"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (Permission::model()->hasGlobalPermission('superadmin', 'read') && !Yii::app()->getConfig('demoMode')) { ?>
                    <h4><?php eT("Your system configuration:") ?></h4>
                    <ul class="list-group">
                        <?php foreach ($systemInfos as $name => $systemInfo) { ?>
                            <li class="list-group-item">
                                <div class="ls-flex-row">
                                    <div class="col-4"><?php echo $name ?></div>
                                    <div class="col-8"><?php echo $systemInfo ?></div>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <h4><?= gT("We are sorry but this information is only available to superadministrators.") ?></h4>
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
        data-bs-toggle='modal'
        data-bs-target='#confirmation-modal'
        data-onclick='(function() { LS.plugin.cintlink.cancelOrder("<?php echo $order->url; ?>"); })'
        class='btn btn-warning btn-sm'
    >

 */
?>

<?php /** this one works with assets/packages/adminbasics/src/parts/confirmationModal.js */ ?>
<div id="confirmation-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT("Confirm"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class='modal-body-text'><?php eT("Are you sure?"); ?></p>
                <!-- the ajax loader -->
                <div id="ajaxContainerLoading">
                    <p><?php eT('Please wait, loading data...'); ?></p>
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
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                <a id="actionBtn" class="btn btn-ok" data-actionbtntext="<?php eT('Confirm'); ?>"></a>
            </div>
            <div class="modal-footer-close modal-footer" style="display: none;">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
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
        <div class="modal-content">
            <div class="modal-header card-header">
                <h5 class="modal-title"><?php eT("Error"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class='modal-body-text'><?php eT("An error occurred."); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">&nbsp;<?php eT("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for success -->
<div id="success-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header card-header">
                <h5 class="modal-title"><?php eT("Success"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class='modal-body-text'><?php /* This must be set in Javascript */ ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">&nbsp;<?php eT("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<?php
//modal for survey activation
App()->getController()->renderPartial('/surveyAdministration/partial/topbar/_modalSurveyActivation');
?>

<?php
//modal for survey activation
App()->getController()->renderPartial('/surveyAdministration/partial/_modalCopySurvey');
?>

<!-- Modal for admin notifications -->
<div id="admin-notification-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content"> <?php // JS add not.type as panel-type, e.g. panel-default, panel-danger
        ?>
            <div class="modal-header card-header">
                <h5 class="modal-title"><?php eT("Notifications"); ?></h5>
                <span class='notification-date'></span>
            </div>
            <div class="modal-body">
                <p class='modal-body-text'></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">&nbsp;<?php eT("Close"); ?></button>
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

<?php
$this->renderPartial('/admin/htmleditor/modal_editor_partial');
?>

</body>

</html>
