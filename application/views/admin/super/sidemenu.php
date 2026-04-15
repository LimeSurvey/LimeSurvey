<?php

/* @deprecated moved to layouts/sidemenulayouts/sidemenu */
//todo should be removed when all controllers have been refactored

   /**
    * This view displays the sidemenu on the left side, containing the question explorer
    *
    */
?>
<?php
    // todo $showSideMenu is not used by vue sidebar.vue? normally set by $aData['sidemenu']['state']
    $sidemenu['state'] = $sidemenu['state'] ?? true;
    if (
        $sideMenuBehaviour == 'alwaysClosed'
        || ($sideMenuBehaviour == 'adaptive'
        && !$sidemenu['state'])
    ) {
        $showSideMenu = false;
    } else {
        $showSideMenu = true;
    }
    $getQuestionsUrl = $this->createUrl("/surveyAdministration/getAjaxQuestionGroupArray/", ["surveyid" => $surveyid]);
    $getMenuUrl = $this->createUrl("/surveyAdministration/getAjaxMenuArray/", ["surveyid" => $surveyid]);
    $createQuestionGroupLink = $this->createUrl("/questionGroupsAdministration/add/", ["surveyid" =>  $surveyid]);
    $createQuestionLink = $this->createUrl("/questionAdministration/create/", ["surveyid" => $surveyid]);
    $unlockLockOrganizerUrl = $this->createUrl("admin/user/sa/togglesetting/", ['surveyid' => $surveyid]);

    $updateOrderLink =  $this->createUrl("/questionGroupsAdministration/updateOrder/", ["surveyid" =>  $surveyid]);

    $createPermission = Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create');
    if ($activated || !$createPermission) {
        $createQuestionGroupLink = "";
        $createQuestionLink = "";
    }

    $landOnSideMenuTab = ($sidemenu['landOnSideMenuTab'] ?? 'structure');

    $menuObjectArray =  [
        "side" => [],
        "collapsed" => [],
        "top" => [],
        "bottom" => [],
    ];
    foreach ($menuObjectArray as $position => $arr) {
        $menuObjectArray[$position] = Survey::model()->findByPk($surveyid)->getSurveyMenus($position);
    }


    Yii::app()->getClientScript()->registerScript('SideBarGlobalObject', '
        window.SideMenuData = {
            getQuestionsUrl: "'.$getQuestionsUrl.'",
            getMenuUrl: "'.$getMenuUrl.'",
            createQuestionGroupLink: "'.$createQuestionGroupLink.'",
            createQuestionLink: "'.$createQuestionLink.'",
            gid: '.($gid ?? 'null').',
            options: [],
            surveyid: '.$surveyid.',
            isActive: '.(Survey::model()->findByPk($surveyid)->isActive ? "true" : "false").',
            basemenus: '.json_encode($menuObjectArray).',
            updateOrderLink: "'.$updateOrderLink.'",
            unlockLockOrganizerUrl: "'.$unlockLockOrganizerUrl.'",
            allowOrganizer: '.(SettingsUser::getUserSettingValue('lock_organizer') ? '1' : '0').',
            translate: '
            .json_encode(
                [
                    "settings" => gT("Settings"),
                    "structure" => gT("Structure"),
                    "createPage" => gT("Add group"),
                    "createQuestion" => gT("Add question"),
                    "lockOrganizerTitle" => gT("Lock question organizer"),
                    "unlockOrganizerTitle" => gT("Unlock question organizer"),
                    "collapseAll" => gT("Collapse all question groups"),
                ]
            )
        .'};', 
        LSYii_ClientScript::POS_HEAD
    );
?>

<div class="simpleWrapper ls-flex" id="vue-sidebar-container"
    v-bind:style="{'max-height': $store.state.inSurveyViewHeight, width : $store.getters.sideBarSize}"
    v-bind:data-collapsed="$store.state.isCollapsed">
    <?php if($landOnSideMenuTab !== ''): ?>
        <sidebar land-on-tab='<?php echo $landOnSideMenuTab ?>' />
    <?php else: ?>
        <sidebar />
    <?php endif; ?>
</div>
