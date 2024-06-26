<?php
/**
 * Add a group to survey
 * @var AdminController $this
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('addQuestionGroup');
?>

<!-- addGroup -->
<script type='text/javascript'>
    var sEnterTitle = '<?php eT('Error: You have to enter a group title for each language.', 'js'); ?>';
</script>

<?php echo PrepareEditorScript(false, $this);
$active = 1; ?>
<div id='edit-survey-text-element' class='side-body'>
    <div class="pagetitle h3"><?php eT("Add question group"); ?></div>
    <div class="row">
        <div class="col-12">
            <!-- Tabs -->
            <ul class="nav nav-tabs">
                <?php foreach ($grplangs as $grouplang): ?>
                    <li role="presentation" class="nav-item">
                        <a class="nav-link <?php if ($active) {
                            echo 'active';
                            $active = 0;
                        } ?>" role="tab" data-bs-toggle="tab" href="#<?php echo $grouplang; ?>">
                            <?= getLanguageNameFromCode($grouplang, false) . " " . (($grouplang == $baselang) ? "(" . gT("Base language") . ")" : "") ?>
                        </a>
                    </li>
                <?php endforeach; ?>

            </ul>
            <!-- form -->
            <?php echo CHtml::form(array("questionGroupsAdministration/saveQuestionGroupData/sid/{$surveyid}"),
                'post',
                array('id' => 'newquestiongroup', 'name' => 'newquestiongroup', 'class' => 'form30 ')
            ); ?>
            <input type="hidden" name="questionGroup[sid]" id="questionGroup[sid]" value="<?= $surveyid ?>">
            <!-- tab content -->
            <div class="tab-content bg-white ps-2 pe-2 pb-1">
                <?php $active = 1;
                foreach ($grplangs as $grouplang): ?>
                    <!-- Lang Content -->
                    <div id="<?php echo $grouplang; ?>" class="tab-pane fade <?php if ($active) {
                        echo 'show active';
                        $active = 0;
                    } ?> ">
                        <div>
                            <!-- Title -->
                            <div class="mb-3">
                                <label class="form-label " for='group_name_<?php echo $grouplang; ?>'><?php eT("Title:"); ?></label>
                                <div class="">
                                    <input class="form-control group_title" type='text' size='80' maxlength='200' name='questionGroupI10N[<?= $grouplang ?>][group_name]'
                                           id='group_name_<?php echo $grouplang; ?>'/></li>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label " for='description_<?php echo $grouplang; ?>'><?php eT("Description:"); ?></label>
                                <div class=" input-group">
                                    <?php echo CHtml::textArea("questionGroupI10N[{$grouplang}][description]",
                                        "",
                                        array('class' => 'form-control', 'cols' => '60', 'rows' => '8', 'id' => "description_{$grouplang}")
                                    ); ?>
                                    <?php echo getEditor("group-desc", "description_" . $grouplang, "[" . gT("Description:", "js") . "](" . $grouplang . ")", $surveyid, '', '', $action); ?>
                                </div>
                            </div>

                            <?php if ($grouplang == $baselang) { ?>
                                <!-- Base Lang -->

                                <!-- Randomization group -->
                                <div class="mb-3">
                                    <label class="form-label " for='randomization_group'><?php eT("Randomization group:"); ?></label>
                                    <div class="">
                                        <input class="form-control" type='text' size='20' maxlength='20' name='questionGroup[randomization_group]' id='randomization_group'/>
                                    </div>
                                </div>

                                <!-- Relevance equation -->
                                <div class="mb-3">
                                    <label class="form-label " for='grelevance'><?php eT("Condition:"); ?></label>
                                    <div class="input-group">
                                        <div class="input-group-text">{</div>
                                        <textarea cols='1' class="form-control" rows='1' id='grelevance' name='questionGroup[grelevance]'></textarea>
                                        <div class="input-group-text">}</div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Save question group -->
                        <p>
                            <input type='submit' class="d-none" value='<?php eT("Save question group"); ?>' />
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php echo CHtml::endForm() ?>
        </div>
    </div>
</div>

<?php
// Switch topbar to "extended" mode.
// In this case (question group topbar), the extended mode only includes the save/close buttons
Yii::app()->getClientScript()->registerScript(
    "AddGroup_topbar_switch",
    'window.EventBus.$emit("doFadeEvent", true);',
    LSYii_ClientScript::POS_END
);
?>
