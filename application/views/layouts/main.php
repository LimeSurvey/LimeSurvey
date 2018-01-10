<?php

    //All paths relative from /application/views

    //headers will be generated with the template file /admin/super/header.php
    $this->_showHeaders($aData);
    //The adminmenu bar will be generated from /admin/super/adminmenu.php
    $this->_showadminmenu($aData);
    // Generated through /admin/usergroup/usergroupbar_view
    $this->_userGroupBar($aData);
    // Generated through /admin/super/fullpagebar_view
    $this->_fullpagebar($aData);

    $this->_updatenotification();
    $this->_notifications();
    
    //The load indicator for pjax
    echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

    echo '<!-- Full page, started in Survey_Common_Action::render_wrapped_template() -->
                <div class="container-fluid full-page-wrapper" id="in_survey_common_action">
                    ';

    echo $content;

    echo '</div>';

    
    // Footer
    if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
        Yii::app()->getController()->_loadEndScripts();
    }

    if (!Yii::app()->user->isGuest) {
        if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
            Yii::app()->getController()->_getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));
        }
    } else {
        echo '</body>
        </html>';
    }
