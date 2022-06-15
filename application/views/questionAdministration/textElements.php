<!-- Text element tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active">
        <a href="#question-tab" aria-controls="question-tab" role="tab" data-toggle="tab">
            <?= gT('Question'); ?>
        </a>
    </li>
    <li role="presentation">
        <a href="#question-help-tab" aria-controls="question-help-tab" role="tab" data-toggle="tab">
            <?= gT('Help'); ?>
        </a>
    </li>
    <?php if ($showScriptField): ?>
        <li role="presentation">
            <a href="#script-field-tab" aria-controls="script-field-tab" role="tab" data-toggle="tab">
                <?= gT('Script'); ?>
            </a>
        </li>
    <?php endif; ?>
    <!-- Language label -->
    <li class="pull-right">
        <?php foreach($oSurvey->allLanguages as $lang): ?>
            <h5 class="lang-hide lang-<?= $lang; ?>" style="<?= $lang != $oSurvey->language ? 'display: none;' : '' ?>">
                <span class="label label-default"><?= strtoupper($lang) ?></span>
            </h5>
        <?php endforeach; ?>
    </li>
</ul>
<div class="tab-content">
    <!-- Question text tab content -->
    <div role="tabpanel" class="tab-pane active" id="question-tab">
        <?php foreach($oSurvey->allLanguages as $lang): ?>
        <div class="lang-hide lang-<?= $lang; ?>" style="<?= $lang != $oSurvey->language ? 'display: none;' : '' ?>">
            <div class="form-group">
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
            <div class="form-group">
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
                <div class="form-group">
                    <?php if ($lang == $oSurvey->language): ?>
                        <div class="row">
                            <div class="col-sm-12 text-right">
                                <input
                                    type="checkbox"
                                    name="question[same_script]"
                                    id="same_script"
                                    value=1
                                    <?php if($question->same_script): ?>
                                        checked = 'checked'
                                    <?php endif; ?>
                                />&nbsp;
                                <label for="same_script">
                                    <?= gT('Use for all languages'); ?>
                                </label>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning same-script-alert hidden"><?= gT('The script for this language will not be used because "Use for all languages" is set on the base language\'s script.') ?></div>
                    <?php endif; ?>

                    <?= CHtml::textArea(
                        "questionI10N[$lang][script]",
                        $question->questionl10ns[$lang]->script,
                        [
                            'id' => CHtml::getIdByName("questionI10N[{$lang}][script]"),
                            'rows' => '10',
                            'cols' => '20',
                            'data-filetype' => 'javascript',
                            'class' => 'ace form-control',
                            'style' => 'width: 100%',
                            'data-lang' => "$lang"
                        ]
                    ); ?>
                    <p class="alert well">
                        <?= gT("This optional script field will be wrapped, so that the script is correctly executed after the question is on the screen. If you do not have the correct permissions, this will be ignored"); ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
