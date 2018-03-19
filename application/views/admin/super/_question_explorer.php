<?php
/**
* This view render the question explorer
*
* @var $sidemenu
* @var $aGroups
* @var $iSurveyId
*/
?>

<!-- State when page is loaded : for JavaScript-->
<li id="explorer" class="dropdownlvl2 dropdownstyle panel panel-default">
<?php if(isset($sidemenu['explorer']['state']) && $sidemenu['explorer']['state']==true):?>
    <input type="hidden" id="open-explorer" />

    <?php if(isset($sidemenu['explorer']['gid'])):?>
        <input type="hidden" id="open-questiongroup" data-gid="<?php echo $sidemenu['explorer']['gid'];?>" />
        <?php endif;?>
    <?php endif;?>

<a data-toggle="collapse" id="explorer-collapse" href="#explorer-lvl1">
    <span class="fa fa-folder-open"></span> <?php eT('Question explorer');?>
    <span class="caret" ></span>
</a>

    <div id="explorer-lvl1" class="panel-collapse collapse" >
        <div class="panel-body">
    <ul class="nav navbar-nav dropdown-first-level" id="explorer-container">

        <!--  Groups and questions-->
        <?php if(count($aGroups)):?>
            <li class="panel panel-default dropdownstyle" id="questionexplorer-group-container">


                <?php if (!$bSurveyIsActive && Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'create')): ?>
                    <div class="row ">
                        <div class="col-sm-8" >
                            <!-- add group -->
                            <a class="btn btn-link"
                                data-toggle="tooltip"
                                data-placement="bottom"
                                title="<?php eT('Add a group');?>"
                                href="<?php echo $this->createUrl("/admin/questiongroups/sa/add/surveyid/$iSurveyId"); ?>">
                                <span class="fa fa-plus-sign"></span>
                                <?php eT('Add group');?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>


                <?php foreach($aGroups as $aGroup):?>

                    <!-- Group -->
                    <div class="row explorer-group-title">
                        <div class="col-sm-8">
                            <a href="#" data-question-group-id="<?php echo $aGroup->gid; ?>" class="explorer-group">
                                <span id="caret-<?php echo $aGroup->gid; ?>" class="fa fa-caret-right caret-explorer-group"></span>&nbsp;&nbsp;
                                <span class="question-explorer-group-name"><?php echo flattenText($aGroup->group_name);?></span>
                            </a>
                        </div>

                        <?php
                        if (!$bSurveyIsActive && Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'create')): ?>
                            <div class="col-sm-1" id="questions-container-<?php echo $aGroup->gid; ?>">
                                <!-- add question to this group -->
                                <a  data-toggle="tooltip" data-placement="top" style="padding: 0" title="<?php eT('Add a question to this group');?>" class="question-explorer-add-question" href="<?php echo $this->createUrl("/admin/questions/sa/newquestion/surveyid/$iSurveyId/gid/$aGroup->gid"); ?>">
                                    <span class="fa fa-plus-sign"></span>
                                </a>
                            </div>
                            <?php elseif (Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'create')): ?>
                            <div class="col-sm-1" style="padding: 0" id="questions-container-<?php echo $aGroup->gid; ?>">
                                <!-- add question to this group -->
                                <a title="<?php eT("You can't add questions while the survey is active.");?>" class='disabled question-explorer-add-question' href="#" data-toggle="tooltip" data-placement="top">
                                    <span class="fa fa-plus-sign"></span>
                                </a>
                            </div>
                            <?php endif;?>
                        <div class="col-sm-1">
                            <!-- add question to this group -->
                            <a  data-toggle="tooltip" data-placement="top"  title="<?php eT('Group summary');?>" class="question-explorer-add-question" href="<?php echo $this->createUrl("/admin/questiongroups/sa/view/surveyid/$iSurveyId/gid/$aGroup->gid"); ?>">
                                <span class="fa fa-list"></span>
                            </a>
                        </div>
                    </div>

                    <!-- Questions -->
                    <div class="row" id="questions-group-<?php echo $aGroup->gid; ?>" style="display: none;">
                        <div class="col-sm-12">
                            <?php if(count($aGroup['aQuestions'])):?>
                                <?php foreach($aGroup['aQuestions'] as $question):?>
                                    <?php if($question->parent_qid == 0):?>

                                        <?php if(isset($sidemenu['explorer']['qid']) && $question->qid == $sidemenu['explorer']['qid']): ?>
                                            <!-- Active question -->
                                            <div  class="question-link active" >
                                                <span class="question-collapse-title">
                                                    <span class="fa fa-list-alt"></span>
                                                    <strong>
                                                        <?php echo sanitize_html_string(strip_tags($question->title));?>
                                                    </strong>
                                                    <br/>
                                                    <em class="question-explorer-question">
                                                        <?php
                                                        echo $question->question;
                                                        ?>
                                                    </em>
                                                </span>
                                            </div>
                                            <?php else: ?>
                                            <!-- Other questions -->
                                            <a href="<?php echo $this->createUrl("/admin/questions/sa/view/surveyid/$iSurveyId/gid/".$aGroup->gid."/qid/".$question->qid); ?>" class="question-link" >
                                                <span class="question-collapse-title">
                                                    <span class="fa fa-list"></span>
                                                    <strong>
                                                        <?php echo sanitize_html_string(strip_tags($question->title));?>
                                                    </strong>
                                                    <br/>
                                                    <em class="question-explorer-question">
                                                        <?php
                                                        echo $question->question;
                                                        ?>
                                                    </em>
                                                </span>
                                            </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach;?>
                                <?php else:?>
                                <a href="" onclick="event.preventDefault();" style="cursor: default;">
                                    <?php eT('There are no questions in this group.');?>
                                </a>
                                <?php endif;?>
                        </div>
                    </div>
                    <?php endforeach;?>
            </li>


            <?php else:?>
            <li class="toWhite">
                <a href="" onclick="event.preventDefault();" style="cursor: default;">
                    <?php eT('No question group in this survey');?>
                </a>
            </li>
            <?php endif;?>
    </ul>
        </div>
    </div>
</li>
