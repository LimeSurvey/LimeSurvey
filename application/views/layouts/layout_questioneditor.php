<?php

Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);

$aData = App()->getController()->aData;

$layoutHelper = new LayoutHelper();

//All paths relative from /application/views

$layoutHelper->showHeaders($aData, false);

$layoutHelper->showadminmenu($aData);

echo "<!-- BEGIN LAYOUT_INSURVEY -->";
// Needed to evaluate EM expressions in question summary
// See bug #11845
LimeExpressionManager::StartProcessingPage(false, true);
$aData['debug'] = $aData;
//$this->_titlebar($aData);
$layoutHelper->rendertitlebar($aData);

//The load indicator for pjax
echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

// echo "<pre>".print_r($aData, true)."</pre>";

//The container to hold the vuejs application
echo ' <!-- Survey page, started in Survey_Common_Action::render_wrapped_template() -->
        <div id="vue-apps-main-container" '
    . 'class="ls-flex-row align-items-flex-begin align-content-flex-end col-12" '
    . '>';

$layoutHelper->renderSurveySidemenu($aData);


echo '<div '
    . 'class="ls-flex-column align-items-flex-start align-content-flex-start col-11 ls-flex-item transition-animate-width main-content-container" '
    . '>';
//New general top bar (VueComponent)
?>
<div id="vue-topbar-container" class="container-fluid vue-general-topbar" style="width: 100%; padding: 0px; margin: 0px;">
    <div class="topbarpanel">
        <nav class="navbar navbar-default scoped-topbar-nav" style="border: none;">
            <div id="itm-9127598" class="ls-flex ls-flex-row ls-space padding top-5">
                <ul class="nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row grow-2 text-left">
                    <li>
                        <div class="topbarbutton">
<a type="button" href="/index.php?r=surveyAdministration/activate&amp;iSurveyID=829174" id="ls-activate-survey" data-btntype="1" class="btn navbar-btn button  white btn-success">
&nbsp;Activate this survey&nbsp;</a>
                        </div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <a id="save-button" type="button" href="#" class="btn navbar-btn button btn-success">Save</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<?php
// TODO: Enable this
//if (!isset($aData['renderSpecificTopbar'])) {
    //$layoutHelper->renderGeneraltopbar($aData);
//}

echo '<div id="pjax-content" class="col-12">';

echo '<div id="in_survey_common" '
    . 'class="container-fluid ls-flex-column fill col-12"'
    . '>';

//Rendered through /admin/update/_update_notification
$layoutHelper->updatenotification();
$layoutHelper->notifications();

echo $content;

//$this->_generaltopbarAdditions($aData);
if (!isset($aData['renderSpecificTopbar'])) {
    $layoutHelper->renderGeneralTopbarAdditions($aData);
}
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "<!-- END LAYOUT_INSURVEY -->";

// Footer
if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
    //Yii::app()->getController()->_loadEndScripts();
    $layoutHelper->loadEndScripts();
}

if (!Yii::app()->user->isGuest) {
    if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
        //Yii::app()->getController()->_getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));
        $layoutHelper->getAdminFooter('http://manual.limesurvey.org');
    }
} else {
    echo '</body>
    </html>';
}
