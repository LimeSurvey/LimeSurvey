<?php

//All paths relative from /application/views
/**
 * @var SurveyCommonAction $this
 * @var array $aData
 *
 * todo: remove this view when all controllers are refactored
 */

//headers will be generated with the template file /admin/super/header.php
$this->showHeaders($aData);

//The adminmenu bar will be generated from /admin/super/adminmenu.php
$this->showadminmenu($aData);

$layoutHelper = new LayoutHelper();

echo $layoutHelper->renderTopbarTemplate($aData);
echo "<!-- BEGIN LAYOUT_MAIN -->";

$this->updatenotification();
$this->notifications();

//The load indicator for pjax
echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

$containerClass = !Yii::app()->user->isGuest ? 'container-fluid full-page-wrapper' : 'container-fluid ps-0';
echo '<!-- Full page, started in SurveyCommonAction::renderWrappedTemplate() -->
<div class="full-page-wrapper ' . $containerClass . '" id="in_survey_common_action">';

if (Yii::app()->session['templatetoken'] ?? null) {
    Yii::import('application.helpers.admin.token_helper', true);
    $filename = decodeFilename(Yii::app()->session['templatetoken']);
    ?>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
        function removeTemplateToken() {
            alert("Kivertek a franciakat Burkina faszabol");
        }
    </script>
    <div id="dialog" title="Import Template?">
        <?php echo "Shall we import the template file of {$filename}?" ?>
    </div>
    <script>
        jQuery(function() {
            $("#dialog").dialog({
                open: function() {
                    $(this).closest(".ui-dialog")
                    .find(".ui-dialog-titlebar-close")
                    .removeClass("ui-dialog-titlebar-close")
                    .html("<span class=\'ui-button-icon-primary ui-icon ui-icon-closethick\' id=\'dialog-close\'></span>");
                    $(this).parent().find(".ui-dialog-title").css("width", "calc(100% - 32px)");
                },
                buttons: {
                    Yes: function() {},
                    No: function() {
                        jQuery('#dialog-close').click();
                    }
                }
            });
        })
    </script>'
    <?php
}

echo $content;

echo '</div>';
echo "<!-- END LAYOUT_MAIN -->";

// Footer
if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
    Yii::app()->getController()->loadEndScripts();
}

if (!Yii::app()->user->isGuest) {
    if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
        Yii::app()->getController()->getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));
    }
} else {
    echo '</body>
    </html>';
}
