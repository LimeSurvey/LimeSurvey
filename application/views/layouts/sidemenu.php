<?php
   /**
    * This view displays the sidemenu on the left side, containing the question explorer
    *
    */
?>
<?php
    $sidemenu['state'] = isset($sidemenu['state']) ? $sidemenu['state'] : true;
    if (
        $sideMenuBehaviour == 'alwaysClosed'
        || ($sideMenuBehaviour == 'adaptive'
        && !$sidemenu['state'])
    ) {
        $showSideMenu = false;
    } else {
        $showSideMenu = true;
    }
    //todo: change urls after refactoring the controllers
    $getQuestionsUrl = $this->createUrl("/admin/survey/sa/getAjaxQuestionGroupArray/", ["surveyid" => $surveyid]);
    $getMenuUrl = $this->createUrl("/admin/survey/sa/getAjaxMenuArray/", ["surveyid" => $surveyid]);
    $createQuestionGroupLink = $this->createUrl("admin/questiongroups/sa/add/", ["surveyid" =>  $surveyid]);
    $createQuestionLink = "questionEditor/view/surveyid/".$surveyid;
    $unlockLockOrganizerUrl = $this->createUrl("admin/user/sa/togglesetting/", ['surveyid' => $surveyid]);

    $updateOrderLink =  $this->createUrl("admin/questiongroups/sa/updateOrder/", ["surveyid" =>  $surveyid]);

    $createPermission = Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create');
    if ($activated || !$createPermission) {
        $createQuestionGroupLink = "";
        $createQuestionLink = "";
    }
    
    $landOnSideMenuTab = (isset($sidemenu['landOnSideMenuTab']) ? $sidemenu['landOnSideMenuTab'] : '');
    
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
            gid: '.(isset($gid) ? $gid : 'null').',
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
