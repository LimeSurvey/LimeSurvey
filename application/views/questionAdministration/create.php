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
                        <?= gT('Create question'); ?>
                    </div>

                    <!-- Question code and question type selector -->
                    <div class="row">
                        <?php $this->renderPartial(
                            "codeAndType",
                            [
                                'oSurvey'             => $oSurvey,
                                'oQuestion'           => $oQuestion,
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
                                'oQuestion'        => $oQuestion,
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
