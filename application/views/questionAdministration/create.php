<!-- Create form for question -->
<div class="side-body">
    <div class="container-fluid">
        <?php echo CHtml::form(
            ['questionAdministration/saveQuestionData'],
            'post',
            ['id'    => 'edit-question-form']
        ); ?>
            <input type="hidden" name="sid" value="<?= $oSurvey->sid; ?>" />
            <input type="hidden" name="question[qid]" value="0" />
            <div id="advancedQuestionEditor">
                <div class="container-center scoped-new-questioneditor">
                    <div class="pagetitle h3 scoped-unset-pointer-events">
                        <x-test id="action::addQuestion"></x-test>
                        <?php if ($question->qid === 0): ?>
                            <?= gT('Create question'); ?>
                        <?php else: ?>
                            <?= gT('Edit question'); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Question code and question type selector -->
                    <div class="row">
                        <?php $this->renderPartial(
                            "codeAndType",
                            [
                                'oSurvey'             => $oSurvey,
                                'question'            => $question,
                                'aStructureArray'     => $aQuestionTypeGroups,
                                'questionTypes'       => $aQuestionTypeStateList,
                                'aQuestionTypeGroups' => $aQuestionTypeGroups
                            ]
                        ); ?>
                    </div>

                    <!-- Language selector -->
                    <div class="row">
                        <?php $this->renderPartial(
                            "languageselector",
                            ['oSurvey' => $oSurvey]
                        ); ?>
                    </div>

                    <div class="row">
                        <div class="col-lg-7">
                            <!-- Text elements -->
                            <?php $this->renderPartial(
                                "textElements",
                                [
                                    'oSurvey'         => $oSurvey,
                                    'aStructureArray' => $aQuestionTypeGroups,
                                    'questionTypes'   => $aQuestionTypeStateList,
                                ]
                            ); ?>
                        </div>

                        <!-- General settings -->
                        <div class="col-lg-5">
                            <div class="ls-flex scope-set-min-height scoped-general-settings">
                                <?php $this->renderPartial("generalSettings", ['generalSettings'  => $generalSettings]); ?>
                            </div>
                        </div>
                    </div>

                    <div class="ls-flex ls-flex-row scoped-advanced-settings-block">
                        <?php $this->renderPartial(
                            "advancedSettings",
                            [
                                'question'        => $question,
                                'oSurvey'          => $oSurvey,
                                'advancedSettings' => $advancedSettings,
                            ]
                        ); ?>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<!-- TODO: Move to where? -->
<script>
$(document).on("ready pjax:scriptcomplete", function () {
    // Hide all languages except main.
    $('.lang-hide').hide();
    $('.lang-<?= $oSurvey->language; ?>').show();
});
</script>
