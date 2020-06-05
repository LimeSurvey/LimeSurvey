<?php
/**
 * Question group bar
 * Also used to Edit question
 */

?>

<!-- nquestiongroupbar -->
<div class='menubar surveybar' id="questiongroupbarid" style="display:none">
    <div class='ls-flex ls-flex-row wrap align-content-space-between ls-space padding left-10 right-10'>

        <?php if (isset($questiongroupbar['buttonspreview']) || isset($questiongroupbar['buttons']['view'])):?>
            <div id="questiongroupbar--previewbar" class="text-left ls-flex-item">
                <!-- test/execute survey -->
                <?php if (count($languagelist) > 1): ?>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="icon-do"></span>
                                <?=($oSurvey->active=='N' ? gT('Preview survey'): gT('Execute survey'));?>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" style="min-width : 252px;">
                            <?php foreach ($languagelist as $tmp_lang): ?>
                                <li>
                                    <a target='_blank'
                                        href='<?=$this->createUrl("survey/index", array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$tmp_lang));?>'>
                                        <?=getLanguageNameFromCode($tmp_lang, false); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="btn btn-default  btntooltip"
                        href="<?php echo $this->createUrl("survey/index", array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$oSurvey->language)); ?>"
                        role="button" accesskey='d' target='_blank'>
                        <span class="icon-do"></span>
                        <?=($oSurvey->active=='N' ? gT('Preview survey'): gT('Execute survey'));?>
                    </a>
                <?php endif;?>

                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')): ?>
                    <?php if (count($languagelist) > 1): ?>
                        <!-- Preview multilangue -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="icon-do"></span>
                                <?php eT("Preview question group"); ?>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" style="min-width : 252px;">
                                <?php foreach ($languagelist as $tmp_lang): ?>
                                <li>
                                    <a target="_blank"
                                        href="<?=$this->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $tmp_lang); ?>">
                                        <?=getLanguageNameFromCode($tmp_lang, false); ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else:?>
                        <!-- Preview simple langue -->
                        <a class="btn btn-default"
                            href="<?=$this->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>"
                            role="button" target="_blank">
                            <span class="icon-do"></span>
                            <?=gT("Preview question group");?>
                        </a>
                    <?php endif; ?>
                    <?php if (isset($questiongroupbar['importbutton']) && $questiongroupbar['importbutton']): ?>
                        <a class="btn btn-default" href="<?php echo App()->createUrl('admin/questiongroups/sa/importview/surveyid/' . $surveyid); ?>" role="button">
                            <span class="icon-import"></span>
                            <?php eT('Import a group'); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif;?>

        <!-- Left Buttons (only shown for question groups) -->
        <?php if (isset($questiongroupbar['buttons']['view'])):?>
            <!-- Buttons -->
            <div id="questiongroupbar--questiongroupbuttons" class="text-center ls-flex-item grow-2">
                <!-- Check survey logic -->
                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')): ?>
                    <a class="btn btn-default pjax"
                        href="<?= $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>"
                        role="button">
                        <span class="icon-expressionmanagercheck"></span>
                        <?php eT("Check survey logic for current question group"); ?>
                    </a>
                <?php endif; ?>

                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'delete')):?>
                    <!-- Delete -->
                    <?php if (($sumcount4 == 0 && $activated != "Y") || $activated != "Y"):?>
                        <!-- has question -->
                        <?php if (empty($condarray)):?>
                            <!-- can delete group and question -->
                            <button class="btn btn-default" data-toggle="modal" data-target="#confirmation-modal"
                                data-onclick='<?php echo convertGETtoPOST(Yii::app()->createUrl("admin/questiongroups/sa/delete/", ["surveyid" => $surveyid, "gid"=>$gid])); ?>'
                                data-message="<?php eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?", "js"); ?>">
                                <span class="fa fa-trash"></span>
                                <?php eT("Delete current question group"); ?>
                            </button>
                        <?php else: ?>
                        <!-- there is at least one question having a condition on its content -->
                            <button type="button" class="btn btn-default btntooltip" disabled data-toggle="tooltip"
                                data-placement="bottom"
                                title="<?php eT("Impossible to delete this group because there is at least one question having a condition on its content"); ?>">
                                <span class="fa fa-trash"></span>
                                <?php eT("Delete current question group"); ?>
                            </button>
                        <?php endif; ?>
                    <?php else:?>
                        <!-- Activated -->
                        <button type="button" class="btn btn-default btntooltip" disabled data-toggle="tooltip"
                            data-placement="bottom"
                            title="<?php eT("You can't delete this question group because the survey is currently active."); ?>">
                            <span class="fa fa-trash"></span>
                            <?php eT("Delete current question group"); ?>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'export')):?>
                    <!-- Export -->
                    <a class="btn btn-default "
                        href="<?php echo $this->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>"
                        role="button">

                        <span class="icon-export"></span>
                        <?php eT("Export this question group"); ?>
                    </a>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <?php if (isset($questiongroupbar['savebutton']['form'])&&isset($qid)
            && (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update'))): ?>
            <!-- ####### This is only shown when editing questions -->
            <div id="questiongroupbar--questionbuttons" class="text-left ls-flex-item">
            <!-- Previews while editing a question -->
                <?php if (count($languagelist) > 1): ?>
                    <!-- test/execute survey -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="icon-do"></span>
                            <?=($oSurvey->active=='N' ? gT('Preview survey') : gT('Execute survey'));?>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" style="min-width : 252px;">
                            <?php foreach ($languagelist as $tmp_lang): ?>
                                <li>
                                    <a target='_blank'
                                        href='<?= $this->createUrl("survey/index", array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$tmp_lang));?>'>
                                        <?= getLanguageNameFromCode($tmp_lang, false); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Preview multilangue -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="icon-do"></span>
                            <?php eT("Preview question group"); ?>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" style="min-width : 252px;">
                            <?php foreach ($languagelist as $tmp_lang): ?>
                                <li>
                                    <a target="_blank"
                                        href="<?= $this->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $tmp_lang); ?>">
                                        <?= getLanguageNameFromCode($tmp_lang, false); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- preview question -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="icon-do"></span>
                            <?php eT("Preview question"); ?>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" style="min-width : 252px;">
                            <?php foreach ($languagelist as $tmp_lang): ?>
                                <li>
                                    <a target="_blank"
                                        href='<?= $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $gid . "/qid/" . $qid . "/lang/" . $tmp_lang); ?>'>
                                        <?= getLanguageNameFromCode($tmp_lang, false); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php else:?>
                    <!-- Preview/Execute survey -->
                    <a class="btn btn-default  btntooltip selector__topbar--previewSurvey"
                        href="<?php echo $this->createUrl("survey/index/sid/$surveyid/newtest/Y/lang/$oSurvey->language"); ?>"
                        role="button" accesskey='d' target='_blank'>
                        <span class="icon-do"></span>
                        <?php if ($oSurvey->active=='N'):?>
                        <?php eT('Preview survey');?>
                        <?php else: ?>
                        <?php eT('Execute survey');?>
                        <?php endif;?>
                    </a>

                    <!-- preview question -->
                    <a class="btn btn-default"
                        href='<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $gid . "/qid/" . $qid); ?>'
                        role="button" target="_blank">
                        <span class="icon-do"></span>
                        <?php eT("Preview");?>
                    </a>

                    <!-- Preview simple langue -->
                    <a class="btn btn-default"
                        href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>"
                        role="button" target="_blank">
                        <span class="icon-do"></span>
                        <?php eT("Preview question group");?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Right Buttons (shown for questions and questiongroups) -->
        <div id="questiongroupbar--savebuttons" class="text-right ls-flex-item">
            <!-- Save buttons -->
            <?php if (isset($questiongroupbar['savebutton']['form']) && (!isset($copying) || !$copying)): ?>
                <a class="btn btn-success" href="#" role="button" id="save-button">
                    <i class="fa fa-floppy-o"></i>
                    <?php eT("Save");?>
                </a>
            <?php endif; ?>

            <!-- Save and close -->
            <?php if (isset($questiongroupbar['saveandclosebutton'])):?>
                <a id="save-and-close-button" class="btn btn-default" role="button">
                    <i class="fa fa-check-square"></i>
                    <?php eT("Save and close");?>
                </a>
            <?php endif; ?>

            <!-- Close -->
            <?php if (isset($questiongroupbar['closebutton']['url'])):?>
                <a class="btn btn-danger" id="close-button"
                    href="<?php echo $questiongroupbar['closebutton']['url']; ?>"
                    role="button">
                    <span class="fa fa-close"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>

            <!-- return -->
            <?php if (isset($questiongroupbar['returnbutton']['url'])):?>
                <a class="btn btn-default"
                    href="<?php echo $questiongroupbar['returnbutton']['url']; ?>"
                    role="button">
                    <span class="fa fa-step-backward"></span>
                    <?php echo $questiongroupbar['returnbutton']['text'];?>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>
