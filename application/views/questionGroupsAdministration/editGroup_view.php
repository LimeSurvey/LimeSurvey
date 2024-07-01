<?php
echo PrepareEditorScript(false, $this);
$count = 0;
?>
<div id='edit-group' class='side-body'>
    <div class="row">
        <div class="col-12 content-right">
            <div class="pagetitle h1"><?php eT("Edit Group"); ?></div>
            <ul class="nav nav-tabs" id="edit-group-language-selection">
                <?php foreach ($tabtitles as $i => $eachtitle): ?>
                    <li role="presentation" class="nav-item">
                        <a class="nav-link <?php if ($count == 0) {
                            echo "active";
                            $count++;
                        } ?>" role="tab" data-bs-toggle="tab" href="#editgrp_<?php echo $i; ?>">
                            <?php echo $eachtitle; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php echo CHtml::form(array("questionGroupsAdministration/saveQuestionGroupData/sid/{$surveyid}"), 'post', array('id' => 'frmeditgroup', 'name' => 'frmeditgroup', 'class' => 'form30 ')); ?>
        <input type="hidden" name="questionGroup[gid]" id="questionGroup[gid]"
               value="<?= $oQuestionGroup['gid'] ?>">
        <input type="hidden" name="questionGroup[sid]" id="questionGroup[sid]"
               value="<?= $oQuestionGroup['sid'] ?>">
        <input type="hidden" name="questionGroup[group_order]" id="questionGroup[group_order]"
               value="<?= $oQuestionGroup['group_order'] ?>">
        <div class="tab-content bg-white ps-2 pe-2">
            <?php foreach ($tabtitles as $i => $eachtitle): ?>
                <div id="editgrp_<?php echo $i; ?>" class="tab-pane fade <?php if ($count == 1) {
                    echo "show active";
                    $count++;
                } ?> center-box">
                    <div class="mb-3">
                        <label class="form-label "
                               id="question-group-title-<?= $aGroupData[$i]['language'] ?>"><?php eT("Title:"); ?></label>
                        <div class="">
                            <?php echo CHtml::textField("questionGroupI10N[{$aGroupData[$i]['language']}][group_name]", $aGroupData[$i]['group_name'], array('class' => 'form-control', 'size' => "80", 'maxlength' => '200', 'id' => "group_name_{$aGroupData[$i]['language']}")); ?>
                        </div>
                    </div>
                    <div class="">
                        <label class=" form-label"
                               for="description_<?php echo $aGroupData[$i]['language']; ?>"><?php eT("Description:"); ?></label>
                        <div class="">
                            <div class="htmleditor input-group">
                                <?php echo CHtml::textArea("questionGroupI10N[{$aGroupData[$i]['language']}][description]", $aGroupData[$i]['description'], array('class' => 'form-control', 'cols' => '60', 'rows' => '8', 'id' => "description_{$aGroupData[$i]['language']}")); ?>
                                <?php echo getEditor("group-desc", "description_" . $aGroupData[$i]['language'], "[" . gT("Description:", "js") . "](" . $aGroupData[$i]['language'] . ")", $surveyid, $gid, '', $action); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="bg-white pt-3 ps-2 pe-2 pb-1">
            <div class="mb-3">
                <label class="form-label " id="randomization-group"><?php eT("Randomization group:"); ?></label>
                <div class="">
                    <?php echo CHtml::textField("questionGroup[randomization_group]", $oQuestionGroup['randomization_group'], array('class' => 'form-control', 'size' => "20", 'maxlength' => '20', 'id' => "randomization_group")); ?>
                </div>
            </div>

            <!-- Relevance Equation -->
            <div class="mb-3">
                <label class="form-label " id="relevance-group"><?php eT("Condition:"); ?></label>
                <div class="input-group">
                    <div class="input-group-text">{</div>
                    <?php echo CHtml::textArea("questionGroup[grelevance]", $oQuestionGroup['grelevance'], array('class' => 'form-control', 'cols' => '20', 'rows' => '1', 'id' => "grelevance")); ?>
                    <div class="input-group-text">}</div>
                </div>
            </div>
            <input type="submit" class="btn btn-primary d-none" value="Save" role="button" aria-disabled="false">
        </div>
        <?php echo CHtml::endForm() ?>
    </div>
</div>

<?php
// Reset topbar to "non-extended" mode.
// If this view wasn't loaded by ajax (ex: from the side menu) this wouldn't be necessary
Yii::app()->getClientScript()->registerScript(
    "EditGroup_topbar_switch", 'window.EventBus.$emit("doFadeEvent", false);',
    LSYii_ClientScript::POS_END
);
?>
