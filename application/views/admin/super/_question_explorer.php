<?php
/**
 * This view render the question explorer
 *
 * @var $aGroups
 * @var $iSurveyId
 */
?>
<li class="panel panel-default" id="explorer" class="dropdownlvl2 dropdownstyle">
    <a data-toggle="collapse" id="explorer-collapse" href="#explorer-lvl1">
        <span class="glyphicon glyphicon-folder-open"></span> <?php eT('Questions explorer');?>
       <span class="caret" ></span>
    </a>

    <div id="explorer-lvl1" class="panel-collapse collapse" >
        <div class="panel-body">
            <ul class="nav navbar-nav dropdown-first-level" id="explorer-container">
                <?php if(count($aGroups)):?>
                    <?php foreach($aGroups as $aGroup):?>
                        <li class="panel panel-default dropdownstyle" id="">

                            <!-- Group Name -->
                            <a data-toggle="collapse" id="" href="#questiongroup-<?php echo $aGroup->gid; ?>" class="question-group-collapse">
                               <span class="question-group-collapse-title"><?php echo $aGroup->group_name;?><span class="caret"></span></span>
                            </a>

                            <div id="questiongroup-<?php echo $aGroup->gid; ?>" class="panel-collapse collapse questiongroupdropdown" >
                                <div class="panel-body">
                                    <ul class="nav navbar-nav dropdown-first-level">

                                        <?php if(count($aGroup['aQuestions'])):?>
                                            <?php foreach($aGroup['aQuestions'] as $question):?>
                                            <!-- Question  -->
                                                <?php if($question->parent_qid == 0):?>

                                                    <li class="toWhite">
                                                        <a href="<?php echo $this->createUrl("/admin/questions/sa/view/surveyid/$iSurveyId/gid/".$aGroup->gid."/qid/".$question->qid); ?>"">
                                                            <span class="question-collapse-title">
                                                                <span class="glyphicon glyphicon-list"></span>
                                                                <strong><?php echo sanitize_html_string($question->title);?> </strong>
                                                                <br/><em> <?php echo substr(sanitize_html_string($question->question), 0, 40);?></em>
                                                            </span>
                                                        </a>
                                                    </li>
                                                <?php endif;?>
                                            <?php endforeach; ?>
                                        <?php else:?>
                                            <li class="toWhite">
                                                <a href="" onclick="event.preventDefault();" style="cursor: default;">
                                                    <?php eT('no questions in this group');?>
                                                </a>
                                            </li>
                                        <?php endif;?>

                                        <!-- add question to this group -->
                                        <li>
                                            <a class="text-success" href="<?php echo $this->createUrl("/admin/questions/sa/newquestion/surveyid/$iSurveyId/gid/$aGroup->gid"); ?>">
                                                <span class="glyphicon glyphicon-plus-sign"></span>
                                                <?php eT('Add new question to group');?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    <?php endforeach;?>
                <?php else:?>
                <li class="toWhite">
                    <a href="" onclick="event.preventDefault();" style="cursor: default;">
                        <?php eT('No question group in this survey');?>
                    </a>
                </li>
                <?php endif;?>
            </ul>
        </div>
</li>
