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

<li id="explorer" class="dropdownlvl2 dropdownstyle panel panel-default">
    <a data-toggle="collapse" id="explorer-collapse" href="#explorer-lvl1">
        <span class="glyphicon glyphicon-folder-open"></span> <?php eT('Question explorer');?>
        <span class="caret" ></span>
    </a>
    <!-- The actual panle-element which contains the tree, and the filter_input -->
    <div id="explorer-lvl1" class="panel-collapse collapse" >
        <div class="panel-body container-fluid">
            <!-- The filtering input, fixed against submit, by js -->
            <div class="row row-with-margin">
                <div class="row">
                    <label class="col-xs-12 control-label" for="searchInQuestionTree"><?php eT("Search for question/questiongroup"); ?></label>
                </div>
                <div class="row">
                    <input class="col-xs-12 form-control" id="searchInQuestionTree" name="searchInQuestionTree" placeholder=" ...<?php eT('Search');?>" />
                </div>
            </div>
            <!-- the fancytree container, here is where the magic happens -->
            <div id="fancytree" class="row" data-show-expand-collapse="1" data-expand-all="<?php eT('Expand all');?>" data-collapse-all="<?php eT('Collapse all'); ?>"></div>
            <!-- The necessary scripts and variables for the fancytree-library -->
            <script>
                var sourceUrl = "<?php echo  Yii::app()->urlManager->createUrl("admin/questiongroups/sa/getGroupExplorerDatas", array("surveyid"=>$iSurveyId, "language" => $language));?>";
                var questionDetailUrl = "<?php echo  Yii::app()->urlManager->createUrl("admin/questiongroups/sa/getQuestionDetailData", array("surveyid" => $iSurveyId, "language" => $language));?>";
                var fancytree = new CreateFancytree($("#fancytree"), $("#searchInQuestionTree"), sourceUrl, questionDetailUrl);
                fancytree.run("<?php echo $iQuestionId; ?>", "<?php echo $iQuestionGroupId; ?>");
            </script>

        </div>
    </div>
</li> 
