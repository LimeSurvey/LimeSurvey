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
                        gT("Alternatively, use the move up and move down buttons.") . ' ' .
                        ($surveyActivated ? gT("Survey is activated, you can not move a question to another group.") : "") . ' ' .
                        gT("After you are done, please click the 'Save' button to save your changes."),
                    'type' => 'info',
            ]);
            ?>
        </div>
        <div class='col-md-4'>
            <button id='organizer-collapse-all' class='btn btn-outline-secondary'><span class='ri-contract-up-down-line'></span>&nbsp;<?php eT("Collapse all"); ?></button>
            <button id='organizer-expand-all' class='btn btn-outline-secondary'><span class='ri-expand-up-down-line'></span>&nbsp;<?php eT("Expand all"); ?></button>
        </div>
    </div>

    <div class='movableList'>
        <ol class="organizer group-list list-unstyled" data-level='group' data-disableparentchange='<?= intval($surveyActivated) ?>'>
            <?php
            foreach ($aGroupsAndQuestions as $aGroupAndQuestions) { ?>
                <?php $groupName = trim(strip_tags((string) $aGroupAndQuestions['group_text'])); ?>
                <li id='list_g<?php echo $aGroupAndQuestions['gid']; ?>' class='card mjs-nestedSortable-expanded mt-2' data-level='group'>
                    <div class="h2 card-header bg-white d-flex align-items-center">
                        <button type="button" class='btn btn-outline-secondary btn-xs ri-arrow-down-s-fill disclose' aria-expanded="true" aria-label="<?= gT('Collapse group') ?>" data-label-expand="<?= gT('Expand group') ?>" data-label-collapse="<?= gT('Collapse group') ?>">
                            <span class="caret"></span>
                        </button>
                        &nbsp;
                        <span class="flex-grow-1 organizer-group-name"><?= ellipsize($aGroupAndQuestions['group_text'], 80) ?></span>
                        <span class="btn-group btn-group-sm ms-2">
                            <button type="button" class="btn btn-outline-secondary organizer-move" data-direction="up" aria-label="<?= CHtml::encode(sprintf(gT('Move group "%s" up', 'unescaped'), $groupName)) ?>"><span class="ri-arrow-up-line" aria-hidden="true"></span></button>
                            <button type="button" class="btn btn-outline-secondary organizer-move" data-direction="down" aria-label="<?= CHtml::encode(sprintf(gT('Move group "%s" down', 'unescaped'), $groupName)) ?>"><span class="ri-arrow-down-line" aria-hidden="true"></span></button>
                        </span>
                    </div>
                    <?php if (isset($aGroupAndQuestions['questions'])) { ?>
                        <ol class='question-list list-unstyled card-body' data-level='question'>
                            <?php
                            foreach ($aGroupAndQuestions['questions'] as $aQuestion) { ?>
                                <li id='list_q<?php echo $aQuestion['qid']; ?>' class='well well-sm no-nest' data-level='question'>
                                    <div class="d-flex align-items-center">
                                        <span class="flex-grow-1">
                                            <b><a href='<?php echo Yii::app()->getController()->createUrl('questionAdministration/view/surveyid/' . $surveyid . '/gid/' . $aQuestion['gid'] . '/qid/' . $aQuestion['qid']); ?>'><?php echo $aQuestion['title']; ?></a></b>:
                                            <?php echo ellipsize($aQuestion['question'], 80); ?>
                                        </span>
                                        <span class="btn-group btn-group-sm ms-2">
                                            <button type="button" class="btn btn-outline-secondary organizer-move" data-direction="up" aria-label="<?= CHtml::encode(sprintf(gT('Move question "%s" up', 'unescaped'), $aQuestion['title'])) ?>"><span class="ri-arrow-up-line" aria-hidden="true"></span></button>
                                            <button type="button" class="btn btn-outline-secondary organizer-move" data-direction="down" aria-label="<?= CHtml::encode(sprintf(gT('Move question "%s" down', 'unescaped'), $aQuestion['title'])) ?>"><span class="ri-arrow-down-line" aria-hidden="true"></span></button>
                                        </span>
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
    <div id="organizer-live-region" class="visually-hidden" role="status" aria-live="polite"
        data-moved-position="<?= CHtml::encode(gT('Moved to position %1$s of %2$s.', 'unescaped')) ?>"
        data-moved-group="<?= CHtml::encode(gT('Moved to group "%1$s", position %2$s of %3$s.', 'unescaped')) ?>"></div>

    <?php echo CHtml::form(array("surveyAdministration/organize/surveyid/{$surveyid}"), 'post', array('id' => 'frmOrganize', 'style' => 'height:40px')); ?>
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
