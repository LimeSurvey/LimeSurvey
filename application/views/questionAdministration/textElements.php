<!-- Text element tabs -->
<ul class="nav nav-tabs me-auto" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" href="#question-tab" aria-controls="question-tab" role="tab" data-bs-toggle="tab">
            <?= gT('Question'); ?>
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="#question-help-tab" aria-controls="question-help-tab" role="tab" data-bs-toggle="tab">
            <?= gT('Help'); ?>
        </a>
    </li>
    <?php if ($showScriptField): ?>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="#script-field-tab" aria-controls="script-field-tab" role="tab" data-bs-toggle="tab">
                <?= gT('Script'); ?>
            </a>
        </li>
    <?php endif; ?>
    <li class="nav-item ms-auto">
        <!-- Language selector -->
        <?php
        $this->renderPartial(
            "languageselector",
            ['oSurvey' => $oSurvey]
        ); ?>
    </li>
</ul>
<div class="tab-content bg-white ps-2 pe-2">
    <!-- Question text tab content -->
    <div role="tabpanel" class="tab-pane show active" id="question-tab">
        <?php foreach($oSurvey->allLanguages as $lang): ?>
        <div class="lang-hide lang-<?= $lang; ?>" style="<?= $lang != $oSurvey->language ? 'display: none;' : '' ?>">
            <div class="mb-3">
                <div class="input-group w-100">
                    <?= CHtml::textArea(
                        "questionI10N[$lang][question]",
                        $question->questionl10ns[$lang]->question ?? '',
                        [
                            'class' => 'form-control',
                            'cols' => '60',
                            'rows' => '8',
                            'id' => "question_{$lang}",
                            'data-contents-dir' => getLanguageRTL($lang) ? 'rtl' : 'ltr',
                            'placeholder' => gT('Enter your question here...'),
                        ]
                    ); ?>
                    <?= getEditor(
                        'question-text',//"question_" . $lang, //this is important for LimereplacementfieldsController function getReplacementFields(...)!
                        "question_" . $lang,
                        "[".gT("Question:","js")."](".$lang.")",
                        $oSurvey->sid,
                        $question->gid ?? 0,
                        $question->qid ?? 0,
                        'editquestion');
                    ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Question help tab content -->
    <div role="tabpanel" class="tab-pane" id="question-help-tab">
        <?php foreach($oSurvey->allLanguages as $lang): ?>
        <div class="lang-hide lang-<?= $lang; ?>" style="<?= $lang != $oSurvey->language ? 'display: none;' : '' ?>">
            <div class="mb-3">
                <div class="input-group w-100">
                    <?= CHtml::textArea(
                        "questionI10N[$lang][help]",
                        $question->questionl10ns[$lang]->help ?? '',
                        [
                            'class' => 'form-control',
                            'cols' => '60',
                            'rows' => '4',
                            'id' => "help_{$lang}",
                            'data-contents-dir' => getLanguageRTL($lang) ? 'rtl' : 'ltr',
                            'placeholder' => gT('Enter some help text if your question needs some explanation here...'),
                        ]
                    ); ?>
                    <?= getEditor(
                        "help_".$lang,
                        "help_".$lang,
                        "[".gT("Help:", "js")."](".$lang.")",
                        $oSurvey->sid,
                        $question->gid ?? 0,
                        $question->qid ?? 0,
                        $action = ''
                    ); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if ($showScriptField): ?>
        <!-- Script tab content -->
        <div role="tabpanel" class="tab-pane" id="script-field-tab">
            <?php foreach($oSurvey->allLanguages as $lang): ?>
            <div class="lang-hide lang-<?= $lang; ?>" style="<?= $lang != $oSurvey->language ? 'display: none;' : '' ?>">
                <div class="mb-3">
                    <?php
                    $scriptFieldId = CHtml::getIdByName("questionI10N[{$lang}][script]");
                    $scriptModalTitle = gT('Script') . ' - ' . getLanguageNameFromCode($lang, false);
                    ?>
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                        <div>
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm script-editor-fullscreen"
                                data-target-field-id="<?= CHtml::encode($scriptFieldId); ?>"
                                data-modal-title="<?= CHtml::encode($scriptModalTitle); ?>"
                            >
                                <span class="ri-fullscreen-line"></span>
                                <?= gT('Open full-screen editor'); ?>
                            </button>
                        </div>
                        <?php if ($lang == $oSurvey->language): ?>
                            <div class="form-check mb-0">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="question[same_script]"
                                    id="same_script"
                                    value="1"
                                    <?php if($question->same_script): ?>checked="checked"<?php endif; ?>
                                />
                                <label class="form-check-label" for="same_script">
                                    <?= gT('Use for all languages'); ?>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($lang != $oSurvey->language): ?>
                        <?php
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'text' => gT('The script for this language will not be used because "Use for all languages" is set on the base language\'s script.'),
                            'type' => 'warning',
                            'htmlOptions' => ['class' => 'same-script-alert d-none']
                        ]);
                        ?>
                    <?php endif; ?>

                    <?= CHtml::textArea(
                        "questionI10N[$lang][script]",
                        $question->questionl10ns[$lang]->script,
                        [
                            'id' => $scriptFieldId,
                            'rows' => '10',
                            'cols' => '20',
                            'data-filetype' => 'javascript',
                            'class' => 'ace form-control',
                            'style' => 'width: 100%',
                            'data-lang' => "$lang",
                            'readonly' => !App()->user->isScriptUpdateAllowed()
                        ]
                    ); ?>
                    <p class="alert well">
                        <?= gT("This optional script field will be wrapped, so that the script is correctly executed after the question is displayed."); ?>
                        <?= !App()->user->isScriptUpdateAllowed() ? gT("You do not have sufficient permissions to update the script.") : ""; ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
