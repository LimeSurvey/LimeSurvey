<?php

/**
 * Presentation panel
 * @var AdminController $this
 * @var Survey $oSurvey
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyPresentationOptions');


App()->getClientScript()->registerScript(
    "presentation-panel-variables",
    "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '" . gT("If you are using surveys with a closed participant group or notifications emails you need to set an administrator email address.", 'js') . "'
    var sURLParameters = '';
    var sAddParam = '';
",
    LSYii_ClientScript::POS_BEGIN
);
?>

<?php

$optionsQuestionIndex = array(
    0 => gT('Disabled', 'unescaped'),
    1 => gT('Incremental', 'unescaped'),
    2 => gT('Full', 'unescaped')
);
if ($bShowInherited) {
    $optionsQuestionIndex['-1'] = $oSurveyOptions->questionindex . " ᴵ";
}

$sEffectiveShowNoAnswer = isset($oSurvey->oOptions->shownoanswer)
    ? $oSurvey->oOptions->shownoanswer
    : $oSurvey->shownoanswer;
$bDisableNoAnswerPreselection = $sEffectiveShowNoAnswer !== 'Y';
$mInheritedShowNoAnswer = $oSurvey->shownoanswer === 'I'
    ? $sEffectiveShowNoAnswer === 'Y'
    : null;

App()->getClientScript()->registerScript(
    'toggle-no-answer-preselection',
    "
    (function () {
        var inheritedShowNoAnswer = " . CJavaScript::encode($mInheritedShowNoAnswer) . ";
        var showNoAnswerInputs = document.querySelectorAll('input[name=\"shownoanswer\"]');
        var preselectInputs = document.querySelectorAll('#preselectnoanswer input[name=\"preselectnoanswer\"]');
        var preservedValue = document.getElementById('preselectnoanswer-disabled-value');
        var preselectGroup = document.getElementById('preselectnoanswer');
        var disabledMessage = " . CJavaScript::encode(gT('Enable “No answer” to configure this setting.')) . ";

        function updateNoAnswerPreselectionState() {
            var selectedShowNoAnswer = document.querySelector('input[name=\"shownoanswer\"]:checked');
            var showNoAnswer = selectedShowNoAnswer && (
                selectedShowNoAnswer.value === 'Y'
                || (selectedShowNoAnswer.value === 'I' && inheritedShowNoAnswer !== false)
            );
            var selectedPreselection = document.querySelector(
                '#preselectnoanswer input[name=\"preselectnoanswer\"]:checked'
            );

            preselectInputs.forEach(function (input) {
                input.disabled = !showNoAnswer;
            });

            if (preservedValue) {
                preservedValue.disabled = Boolean(showNoAnswer);
                if (selectedPreselection) {
                    preservedValue.value = selectedPreselection.value;
                }
            }

            if (preselectGroup) {
                preselectGroup.setAttribute('aria-disabled', String(!showNoAnswer));
                preselectGroup.setAttribute('title', showNoAnswer ? '' : disabledMessage);
            }
        }

        showNoAnswerInputs.forEach(function (input) {
            input.addEventListener('change', updateNoAnswerPreselectionState);
        });
        updateNoAnswerPreselectionState();
    }());
    ",
    LSYii_ClientScript::POS_READY
);

// The question index always allows jumping back to previous groups, so "Allow backward navigation" then only shows or hides the "Previous" button
$iEffectiveQuestionIndex = (int) (isset($oSurvey->oOptions->questionindex)
    ? $oSurvey->oOptions->questionindex
    : $oSurvey->questionindex);
$bQuestionIndexEnabled = $iEffectiveQuestionIndex > 0;
$mInheritedQuestionIndex = $oSurvey->questionindex == -1
    ? $iEffectiveQuestionIndex
    : null;
$sAllowPrevLabel = gT('Allow backward navigation');
$sShowPrevLabel = gT('Show “Previous” button');

App()->getClientScript()->registerScript(
    'toggle-allow-prev-label',
    "
    (function () {
        var inheritedQuestionIndex = " . CJavaScript::encode($mInheritedQuestionIndex) . ";
        var questionIndexInputs = document.querySelectorAll('input[name=\"questionindex\"]');
        var allowPrevLabel = document.getElementById('allowprev-label');
        var allowPrevGroup = document.getElementById('allowprev');
        var allowPrevHelp = document.getElementById('allowprev-help');
        var allowPrevText = " . CJavaScript::encode($sAllowPrevLabel) . ";
        var showPrevText = " . CJavaScript::encode($sShowPrevLabel) . ";

        function updateAllowPrevLabel() {
            var selectedQuestionIndex = document.querySelector('input[name=\"questionindex\"]:checked');
            var questionIndex = 0;
            if (selectedQuestionIndex) {
                questionIndex = selectedQuestionIndex.value === '-1'
                    ? Number(inheritedQuestionIndex)
                    : Number(selectedQuestionIndex.value);
            }
            var labelText = questionIndex > 0 ? showPrevText : allowPrevText;

            if (allowPrevLabel) {
                allowPrevLabel.textContent = labelText;
            }
            if (allowPrevGroup) {
                allowPrevGroup.setAttribute('aria-label', labelText);
            }
            if (allowPrevHelp) {
                allowPrevHelp.classList.toggle('d-none', questionIndex <= 0);
            }
        }

        questionIndexInputs.forEach(function (input) {
            input.addEventListener('change', updateAllowPrevLabel);
        });
        updateAllowPrevLabel();
    }());
    ",
    LSYii_ClientScript::POS_READY
);
?>

<!-- Presentation panel -->
<div id='presentation-panel'>
    <div class="row">
        <h1>
            <?php eT('Display'); ?>
        </h1>
        <div class="col-12 col-lg-6">
            <!-- Show "No answer" -->
            <div class="mb-3">
                <label class="form-label" for="shownoanswer"><?php eT('Show “No answer”'); ?></label>
                <div class="">
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'shownoanswer',
                            'checkedOption' => $oSurvey->shownoanswer,
                            'ariaLabel' => gT('Show “No answer”'),
                            'selectOptions' => ($bShowInherited) ?
                                array_merge($optionsOnOff, array('I' => $oSurveyOptions->shownoanswer . " ᴵ")) : $optionsOnOff
                        )
                    );
                                                                ?>
                </div>
            </div>

            <!-- Preselect "No answer" -->
            <div class="mb-3">
                <label class="form-label" for="preselectnoanswer"><?php eT('Preselect “No answer”'); ?></label>
                <div>
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'preselectnoanswer',
                            'checkedOption' => $oSurvey->preselectnoanswer,
                            'ariaLabel' => gT('Preselect “No answer”'),
                            'selectOptions' => ($bShowInherited) ?
                                array_merge(
                                    $optionsOnOff,
                                    array('I' => $oSurveyOptions->preselectnoanswer . " ᴵ")
                                ) : $optionsOnOff,
                            'htmlOptions' => array(
                                'disabled' => $bDisableNoAnswerPreselection,
                                'title' => $bDisableNoAnswerPreselection
                                    ? gT('Enable “No answer” to configure this setting.')
                                    : '',
                            )
                        )
                    ); ?>
                    <?= CHtml::hiddenField(
                        'preselectnoanswer',
                        $oSurvey->preselectnoanswer,
                        array(
                            'id' => 'preselectnoanswer-disabled-value',
                            'disabled' => !$bDisableNoAnswerPreselection,
                        )
                    ) ?>
                </div>
            </div>

            <!-- Show "There are X questions in this survey" -->
            <div class="mb-3">
                <label class="form-label" for="showxquestions">
                    <?php eT('Show “There are X questions in this survey”'); ?></label>
                <div class="">
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'showxquestions',
                            'checkedOption' => $oSurvey->showxquestions,
                            'ariaLabel' => gT('Show “There are X questions in this survey”'),
                            'selectOptions' => ($bShowInherited) ?
                                array_merge($optionsOnOff, array('I' => $oSurveyOptions->showxquestions . " ᴵ")) : $optionsOnOff
                        )
                    );
                    ?>
                </div>
            </div>

            <?php
            $sel_showgri = array('B' => '', 'D' => '', 'N' => '', 'X' => '', 'I' => '');
            if (isset($oSurvey->showgroupinfo)) {
                $set_showgri               = $oSurvey->showgroupinfo;
                $sel_showgri[$set_showgri] = ' selected="selected"';
            }
            if (
                empty($sel_showgri['B']) && empty($sel_showgri['D']) && empty($sel_showgri['N'])
                && empty($sel_showgri['X']) && empty($sel_showgri['I'])
            ) {
                $sel_showgri['B'] = ' selected="selected"';
            }
            ?>


            <!-- Show group name and/or group description -->
            <div class="mb-3">
                <label class="form-label" for="showgroupinfo"><?php eT('Group name and description'); ?></label>
                <div class="col-12 col-lg-8">
                    <select id="showgroupinfo" name="showgroupinfo" class="form-select">
                        <?php if ($bShowInherited) { ?>
                            <option value="I" <?php echo $sel_showgri['I']; ?>>
                                <?php echo $oSurveyOptions->showgroupinfo . ' ᴵ'; ?></option>
                        <?php } ?>
                        <option value="B" <?php echo $sel_showgri['B']; ?>><?php eT('Show both'); ?></option>
                        <option value="N" <?php echo $sel_showgri['N']; ?>><?php eT('Show group name only'); ?></option>
                        <option value="D" <?php echo $sel_showgri['D']; ?>><?php eT('Show group description only'); ?></option>
                        <option value="X" <?php echo $sel_showgri['X']; ?>><?php eT('Hide both'); ?></option>
                    </select>
                    <?php unset($sel_showgri, $set_showgri); ?>
                </div>
            </div>

            <?php
            $sel_showqnc = array('B' => '', 'C' => '', 'N' => '', 'X' => '', 'I' => '');
            if (isset($oSurvey->showqnumcode)) {
                $set_showqnc               = $oSurvey->showqnumcode;
                $sel_showqnc[$set_showqnc] = ' selected="selected"';
            }
            if (
                empty($sel_showqnc['B']) && empty($sel_showqnc['C']) && empty($sel_showqnc['N'])
                && empty($sel_showqnc['X']) && empty($sel_showqnc['I'])
            ) {
                $sel_showqnc['X'] = ' selected="selected"';
            };
            ?>

            <!-- Show question number and/or code -->
            <div class="mb-3">
                <label class="form-label" for="showqnumcode"><?php eT('Question number and code'); ?></label>
                <div class="col-12 col-lg-8">
                    <select class="form-select" id="showqnumcode" name="showqnumcode">
                        <?php if ($bShowInherited) { ?>
                            <option value="I" <?php echo $sel_showqnc['I']; ?>>
                                <?php echo $oSurveyOptions->showqnumcode . ' ᴵ'; ?></option>
                        <?php } ?>
                        <option value="B" <?php echo $sel_showqnc['B']; ?>><?php eT('Show both'); ?></option>
                        <option value="N" <?php echo $sel_showqnc['N']; ?>><?php eT('Show question number only'); ?></option>
                        <option value="C" <?php echo $sel_showqnc['C']; ?>><?php eT('Show question code only'); ?></option>
                        <option value="X" <?php echo $sel_showqnc['X']; ?>><?php eT('Hide both'); ?></option>
                    </select>
                    <?php unset($sel_showqnc, $set_showqnc); ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <!-- Show welcome screen -->
            <div class="mb-3">
                <label class=" form-label" for='showwelcome'><?php eT("Show welcome screen"); ?></label>
                <div class="">
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'showwelcome',
                            'checkedOption' => $oSurvey->showwelcome,
                            'ariaLabel' => gT('Show welcome screen'),
                            'selectOptions' => ($bShowInherited) ?
                                array_merge($optionsOnOff, array('I' => $oSurveyOptions->showwelcome . " ᴵ")) : $optionsOnOff
                        )
                    );
                                                                ?>
                </div>
            </div>

            <!-- Show progress bar -->
            <div class="mb-3">
                <label class=" form-label" for='showprogress'><?php eT("Show progress bar"); ?></label>
                <div class="">
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'showprogress',
                            'checkedOption' => $oSurvey->showprogress,
                            'ariaLabel' => gT('Show progress bar'),
                            'selectOptions' => ($bShowInherited) ?
                                array_merge($optionsOnOff, array('I' => $oSurveyOptions->showprogress . " ᴵ")) : $optionsOnOff
                        )
                    );
                                                                ?>
                </div>
            </div>

            <!-- Show question index -->
            <div class="mb-3">
                <label class="form-label" for='questionindex'><?php eT("Show question index and allow jumping"); ?></label>
                <div class="">
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'questionindex',
                            'checkedOption' => $oSurvey->questionindex,
                            'ariaLabel' => gT('Show question index and allow jumping'),
                            'selectOptions' => $optionsQuestionIndex
                        )
                    );
                                                                ?>
                </div>
            </div>

        </div>
    </div>

    <div class="row mt-5">
        <h1><?php eT('Navigation') ?></h1>
        <!-- Navigation delay -->
        <div class="col-12 col-lg-6 mb-3">
            <?php $navigationdelay = $oSurvey->navigationdelay; ?>
            <div class="row mb-3">
                <div class="col-12 col-lg-8">
                    <label class="form-label" for='navigationdelay'><?php eT("Navigation delay (seconds):"); ?></label>
                    <?php // NB: Do NOT use === when comparing navigationdelay to '-1', it won't work with Postgresql.?>
                    <input class="form-control inherit-edit <?= ($bShowInherited && $navigationdelay == '-1' ? 'd-none' : 'd-block') ?>"
                           type='text' size='10' id='navigationdelay' name='navigationdelay'
                           value="<?= htmlspecialchars($navigationdelay ?? "") ?>" data-inherit-value="-1" data-saved-value="<?= $navigationdelay ?>"/>
                    <input class="form-control inherit-readonly <?php echo($bShowInherited && $navigationdelay == '-1' ? 'd-block' : 'd-none'); ?>"
                           type='text' size='10' value="<?php echo htmlspecialchars($oSurveyOptions->navigationdelay ?? ""); ?>" readonly/>
                </div>
                <div class="col-12 col-lg-4 <?php echo($bShowInherited ? 'd-block' : 'd-none'); ?>">
                    <label class="form-label col-12" for='navigationdelay'><?php eT("Inherit"); ?></label>
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        [
                            'name' => 'navigationdelaybutton',
                            // NB: Do NOT use === when comparing navigationdelay with '-1', it won't work with Postgresql.
                            'checkedOption' => ($bShowInherited && $navigationdelay == '-1' ? 'Y' : 'N'),
                            'selectOptions' => $optionsOnOff,
                            'htmlOptions' => [
                                'class' => 'text-option-inherit'
                            ]
                        ]
                    ); ?>
                </div>
            </div>
            <!-- Automatically load URL -->
            <div class="mb-3">
                <label class=" form-label" for='autoredirect'><?php eT("Automatically load end URL when survey complete:"); ?></label>
                <div class="">
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'autoredirect',
                            'checkedOption' => $oSurvey->autoredirect,
                            'ariaLabel' => gT('Automatically load end URL when survey complete'),
                            'selectOptions' => ($bShowInherited)
                                ? array_merge($optionsOnOff, array('I' => $oSurveyOptions->autoredirect . " ᴵ")) : $optionsOnOff
                        )
                    );
                                                                ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <!-- Allow backward navigation: -->
            <div class="mb-3">
                <label class=" form-label" id="allowprev-label" for='allowprev'><?= CHtml::encode($bQuestionIndexEnabled ? $sShowPrevLabel : $sAllowPrevLabel) ?></label>
                <div class="">
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'allowprev',
                            'checkedOption' => $oSurvey->allowprev,
                            'ariaLabel' => $bQuestionIndexEnabled ? $sShowPrevLabel : $sAllowPrevLabel,
                            'selectOptions' => ($bShowInherited) ? array_merge($optionsOnOff, array('I' => $oSurveyOptions->allowprev . " ᴵ")) : $optionsOnOff,
                            'htmlOptions' => array(
                                'aria-describedby' => 'allowprev-help',
                            )
                        )
                    );
                                                            ?>
                </div>
                <div id="allowprev-help" class="form-text <?= $bQuestionIndexEnabled ? '' : 'd-none' ?>">
                    <?php eT('Participants can always go back using the question index while it is enabled.'); ?>
                </div>
            </div>
            <!-- Participants may print answers -->
            <div class="mb-3">
                <label class=" form-label" for='printanswers'><?php eT("Participants may print answers:"); ?></label>
                <div class="">
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'printanswers',
                            'checkedOption' => $oSurvey->printanswers,
                            'ariaLabel' => gT('Participants may print answers'),
                            'selectOptions' => ($bShowInherited) ?
                                array_merge($optionsOnOff, array('I' => $oSurveyOptions->printanswers . " ᴵ")) : $optionsOnOff
                        )
                    );
                                                                ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5 mb-3">
        <h2 role="heading" class="h1"><?php eT('Public statistics') ?></h2>
        <div class="col-12 col-lg-4 col-xl-3">
            <!-- Public statistics -->
            <div class="mb-3">
                <label class=" form-label" for='publicstatistics'><?php eT("Public statistics:"); ?></label>
                <div class="">
                    <?php $this->widget(
                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                        array(
                            'name' => 'publicstatistics',
                            'checkedOption' => $oSurvey->publicstatistics,
                            'ariaLabel' => gT('Public statistics'),
                            'selectOptions' => ($bShowInherited) ?
                                array_merge($optionsOnOff, array('I' => $oSurveyOptions->publicstatistics . " ᴵ")) : $optionsOnOff
                        )
                    );
                                                                    ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 col-xl-3 ">
            <!-- Show graphs in public statistics -->
            <div class="mb-3">
                <div>
                    <label class=" form-label" for='publicgraphs'><?php eT("Show graphs in public statistics:"); ?></label>
                </div>
                <?php $this->widget(
                    'ext.ButtonGroupWidget.ButtonGroupWidget',
                    array(
                        'name' => 'publicgraphs',
                        'checkedOption' => $oSurvey->publicgraphs,
                        'ariaLabel' => gT('Show graphs in public statistics:'),
                        'selectOptions' => ($bShowInherited) ?
                            array_merge($optionsOnOff, array('I' => $oSurveyOptions->publicgraphs . " ᴵ")) : $optionsOnOff
                    )
                );
                                                                    ?>
            </div>
        </div>
    </div>

</div>
<?php $this->renderPartial('/surveyAdministration/_inherit_sub_footer'); ?>
