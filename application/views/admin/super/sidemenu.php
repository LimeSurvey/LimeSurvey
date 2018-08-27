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
    
    foreach($menuObjectArray as $position => $arr) {
        $menuObjectArray[$position] = Survey::model()->findByPk($surveyid)->getSurveyMenus($position);
    }
    
    $menuObject =  json_encode($menuObjectArray);

?>
<sidebar
    :options="[]"
    surveyid = '<?=$surveyid?>'
    is-active = <?=(Survey::model()->findByPk($surveyid)->isActive ? 1 : 0)?>
    get-questions-url="<?=$getQuestionsUrl ?>"
    get-menu-url="<?=$getMenuUrl ?>"
    :basemenus='<?=$menuObject?>'
    create-question-group-link ="<?=$createQuestionGroupLink?>"
    create-question-link ="<?=$createQuestionLink?>"
    update-order-link="<?=$updateOrderLink?>"
    :translate="{settings: '<?php eT("Settings");?>', structure:'<?php eT("Structure");?>', createQuestionGroup:'<?php eT("Add question group");?>', createQuestion:'<?php eT("Add question");?>' }"
></sidebar>

