<?php
   /**
    * This view displays the sidemenu on the left side, containing the question explorer
    *
    * Var to manage open/close state of the sidemenu, question explorer :
    * @var $sidemenu['state'] : if set, the sidemnu is close
    * @var $sidemenu['explorer']['state'] : if set to true, question explorer will be opened
    */
?>
<?php
    $sidemenu['state'] = isset($sidemenu['state']) ? $sidemenu['state'] : true;
    if ($sideMenuBehaviour == 'alwaysClosed'
        || ($sideMenuBehaviour == 'adaptive'
        && !$sidemenu['state'])) {
        $showSideMenu = false;
    } else {
        $showSideMenu = true;
    }
    $getQuestionsUrl = $this->createUrl("/admin/survey/sa/getAjaxQuestionGroupArray/" ,["surveyid" => $surveyid]);
    $getMenuUrl = $this->createUrl("/admin/survey/sa/getAjaxMenuArray/" ,["surveyid" => $surveyid]);
    $createQuestionGroupLink = $this->createUrl("admin/questiongroups/sa/add/" ,["surveyid" =>  $surveyid]);
    if(isset($oQuestionGroup)) {
        $createQuestionLink = $this->createUrl("admin/questions/sa/newquestion/" ,["surveyid" => $surveyid, "gid" => $oQuestionGroup->gid]);
    } else {
        $createQuestionLink = $this->createUrl("admin/questions/sa/newquestion/" ,["surveyid" => $surveyid]);
    }

    $updateOrderLink =  $this->createUrl("admin/questiongroups/sa/updateOrder/", ["surveyid" =>  $surveyid]);

    $createPermission = Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create');
    if ($activated || !$createPermission) {
        $createQuestionGroupLink = "";
        $createQuestionLink = "";
    }

    $menuObjectArray =  [
        "side" => [],
        "collapsed" => [],
        "top" => [],
        "bottom" => [],
    ];
    
    foreach ($menuObjectArray as $position => $arr) {
        $menuObjectArray[$position] = Survey::model()->findByPk($surveyid)->getSurveyMenus($position);
    }
    
    $menuObject =  json_encode($menuObjectArray);

    Yii::app()->getClientScript()->registerScript('SideBarGlobalObject', '
        window.SideMenuData = {
            getQuestionsUrl: "'.$getQuestionsUrl.'",
            getMenuUrl: "'.$getMenuUrl.'",
            createQuestionGroupLink: "'.$createQuestionGroupLink.'",
            createQuestionLink: "'.$createQuestionLink.'",
            options: [],
            surveyid: '.$surveyid.',
            isActive: '.(Survey::model()->findByPk($surveyid)->isActive ? "true" : "false").',
            getQuestionsUrl: "'.$getQuestionsUrl.'",
            getMenuUrl: "'.$getMenuUrl.'",
            basemenus: '.$menuObject.',
            createQuestionGroupLink: "'.$createQuestionGroupLink.'",
            createQuestionLink: "'.$createQuestionLink.'",
            updateOrderLink: "'.$updateOrderLink.'",
            translate: '.json_encode([
                "settings" => gT("Settings"),
                "structure" => gT("Structure"),
                "createQuestionGroup" => gT("Add question group"),
                "createQuestion" => gT("Add question")
            ]).'
        };
    ', LSYii_ClientScript::POS_HEAD);
?>

<div class="simpleWrapper ls-flex" id="vue-sidebar-container"
    v-bind:style="{'min-height':'80vh','max-height': $store.state.inSurveyViewHeight, width : $store.getters.sideBarSize}"
    v-bind:data-collapsed="$store.state.isCollapsed">
    <sidebar />
</div>