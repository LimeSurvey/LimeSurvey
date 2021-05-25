<div class="col-12">
    <div class="panel panel-default col-12 question-option-general-container">
        <div class="panel-heading">Text elements</div>

        <?php foreach($oSurvey->allLanguages as $lang): ?>
        <div class="lang-hide lang-<?= $lang; ?>">

            <div class="panel-body">
                <div class="form-group scope-contains-ckeditor">
                    <div class="ls-flex-row">
                        <div class="ls-flex-item grow-2 text-left">
                            <label for="<?= "question_{$lang}" ?>" class="col-sm-12"><?= gT('Question'); ?></label>
                        </div>
                    </div>
                    <div class="htmleditor input-group">
                        <?= CHtml::textArea(
                            "questionI10N[$lang][question]",
                            $question->questionl10ns[$lang]->question ?? '',
                            [
                                'class' => 'form-control',
                                'cols' => '60',
                                'rows' => '8',
                                'id' => "question_{$lang}",
                                'data-contents-dir' => getLanguageRTL($lang) ? 'rtl' : 'ltr'
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
                <div class="form-group scope-contains-ckeditor">
                    <div class="ls-flex-row">
                        <div class="ls-flex-item grow-2 text-left">
                            <label for="<?= "help_{$lang}" ?>" class="col-sm-12"><?= gT('Help:'); ?></label>
                        </div>
                    </div>
                    <div class="htmleditor input-group">
                        <?= CHtml::textArea(
                            "questionI10N[$lang][help]",
                            $question->questionl10ns[$lang]->help ?? '',
                            [
                                'class' => 'form-control',
                                'cols' => '60',
                                'rows' => '4',
                                'id' => "help_{$lang}",
                                'data-contents-dir' => getLanguageRTL($lang) ? 'rtl' : 'ltr'
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
                <?php if ($showScriptField): ?>
                    <div class="form-group">
                        <div class="row">
                            <label for="<?= CHtml::getIdByName("questionI10N[{$lang}][script]") ?>" class="col-sm-6">
                                <?= gT('Script'); ?>
                            </label>
                            <div class="col-sm-6 text-right">
                                <input 
                                    type="checkbox" 
                                    name="scriptForAllLanguages"
                                    id="selector--scriptForAllLanguages"
                                    v-model="scriptForAllLanugages"
                                />&nbsp;
                                <label for="selector--scriptForAllLanguages">
                                    <?= gT('Set for all languages'); ?>
                                </label>
                            </div>
                        </div> 

                        <?= CHtml::textArea(
                            "questionI10N[$lang][script]",
                            $question->questionl10ns[$lang]->script,
                            [
                                'id' => CHtml::getIdByName("questionI10N[{$lang}][script]"),
                                'rows' => '10',
                                'cols' => '20',
                                'data-filetype' => 'javascript',
                                'class' => 'ace form-control',
                                'style' => 'width: 100%'
                            ]
                        ); ?>
                        <p class="alert well">
                            <?= gT("This optional script field will be wrapped, so that the script is correctly executed after the question is on the screen. If you do not have the correct permissions, this will be ignored"); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div> 
