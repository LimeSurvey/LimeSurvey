<?php
App()->getClientScript()->registerPackage('jquery-nestedSortable');
App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'organize.js', LSYii_ClientScript::POS_BEGIN);
App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'organize.css');
?>

<div id='edit-survey-text-element' class='side-body'>
    <div class='row'>
        <div class='col-md-8'>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                    'header' => gT("Reordering"),
                    'text' => gT("To reorder questions/questiongroups just drag the question/group with your mouse to the desired position.") . ' ' .
                        ($surveyActivated ? gT("Survey is activated, you can not move a question to another group.") : "") . ' ' .
                        gT("After you are done, please click the 'Save' button to save your changes."),
                    'type' => 'info',
            ]);
            ?>
        </div>
        <div class='col-md-4'>
            <button id='organizer-collapse-all' class='btn btn-outline-secondary'><span class='ri-fullscreen-exit-line'></span>&nbsp;<?php eT("Collapse all"); ?></button>
            <button id='organizer-expand-all' class='btn btn-outline-secondary'><span class='ri-fullscreen-exit-line'></span>&nbsp;<?php eT("Expand all"); ?></button>
        </div>
    </div>

    <div class='movableList'>
        <ol class="organizer group-list list-unstyled" data-level='group' data-disableparentchange='<?= intval($surveyActivated) ?>'>
            <?php
            foreach ($aGroupsAndQuestions as  $aGroupAndQuestions) { ?>
                <li id='list_g<?php echo $aGroupAndQuestions['gid']; ?>' class='card mjs-nestedSortable-expanded mt-2' data-level='group'>

                    <div class="h2 card-header bg-white">
                        <a class='btn btn-outline-secondary btn-xs ri-arrow-down-s-fill disclose ' tabindex="0" aria-label="Click to show/hide children"><span title="Click to show/hide children" class="caret"></span></a>
                        &nbsp;
                        <?php echo ellipsize($aGroupAndQuestions['group_text'], 80); ?>
                    </div>
                    <?php if (isset($aGroupAndQuestions['questions'])) { ?>
                        <ol class='question-list list-unstyled card-body' data-level='question'>
                            <?php
                            foreach ($aGroupAndQuestions['questions'] as $aQuestion) { ?>
                                <li id='list_q<?php echo $aQuestion['qid']; ?>' class='well well-sm no-nest' data-level='question'>
                                    <div>
                                        <b><a href='<?php echo Yii::app()->getController()->createUrl('questionAdministration/view/surveyid/' . $surveyid . '/gid/' . $aQuestion['gid'] . '/qid/' . $aQuestion['qid']); ?>'><?php echo $aQuestion['title']; ?></a></b>:
                                        <?php echo ellipsize($aQuestion['question'], 80); ?>
                                    </div>
                                </li>
                            <?php } ?>
                        </ol>
                    <?php } ?>
                </li>
            <?php
            } ?>
        </ol>
    </div>

    <?php echo CHtml::form(array("surveyAdministration/organize/surveyid/{$surveyid}"), 'post', array('id' => 'frmOrganize', 'style'=>'height:40px')); ?>
    <p>
        <input type='hidden' id='orgdata' name='orgdata' value='' />
        <!-- set close-after-save true for redirecting to listQuestion page after save -->
        <input type='hidden' id='close-after-save' name='close-after-save' value='true' />
        <button class="btn btn-primary float-end" type="submit" id='btnSave'>
            <i class="ri-check-fill"></i>
            <?php echo eT('Save'); ?>
        </button>
    </p>
    </form>
    <!-- If user do a change in the list, and try to leave without saving, he'll be warn with this message -->
    <input type="hidden" value="off" id="didChange" data-message="<?php eT("You didn't save your changes!"); ?>" />
</div>
