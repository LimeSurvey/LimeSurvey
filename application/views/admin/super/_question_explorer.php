<?php
/**
* This view render the question explorer
*
* @var $sidemenu
* @var $aGroups
* @var $iSurveyId
* @var $language
* @var $iQuestionId
* @var $iQuestionGroupId
*/
?>
<?php
    App()->getClientScript()->registerPackage('fancytree');
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "jquery.fancytree.bstooltip.js");
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "jquery.fancytree.bsbuttonbar.js");
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "fancytree.surveyview.js");

?>


<!-- State when page is loaded : for JavaScript-->
<?php if(isset($sidemenu['explorer']['state']) && $sidemenu['explorer']['state']==true):?>
    <input type="hidden" id="open-explorer" />
    <?php if(isset($sidemenu['explorer']['gid'])):?>
        <input type="hidden" id="open-questiongroup" data-gid="<?php echo $sidemenu['explorer']['gid'];?>" />
    <?php endif;?>
<?php endif;?>

<div id="explorer" class=" panel panel-default">
    <!-- The actual panle-element which contains the tree, and the filter_input -->
    <div class="panel-body">
        <!-- The filtering input, fixed against submit, by js -->
        <div class="row row-with-margin"> 
            <input class="col-xs-12 form-control" id="searchInQuestionTree" name="searchInQuestionTree" placeholder="<?php eT("Search for question/questiongroup"); ?>" />
        </div>
        <!-- the fancytree container, here is where the magic happens -->
        <div id="fancytree" class="row" data-show-expand-collapse="1" data-expand-all="<?php eT('Expand all');?>" data-collapse-all="<?php eT('Collapse all'); ?>"></div>
        <!-- The necessary scripts and variables for the fancytree-library -->
        <script>
            var dblClickTitle = "<?php eT('Double-click to edit.');?>";
            var sourceUrl = "<?php echo  Yii::app()->urlManager->createUrl("admin/questiongroups/sa/getGroupExplorerDatas", array("surveyid"=>$iSurveyId, "language" => $language));?>";
            var questionDetailUrl = "<?php echo  Yii::app()->urlManager->createUrl("admin/questiongroups/sa/getQuestionDetailData", array("surveyid" => $iSurveyId, "language" => $language));?>";
            var fancytree = new CreateFancytree($("#fancytree"), $("#searchInQuestionTree"), sourceUrl, questionDetailUrl);
            var tree = fancytree.run("<?php echo $iQuestionId; ?>", "<?php echo $iQuestionGroupId; ?>", "#sideMenu");
        </script>
    </div>
</div> 
