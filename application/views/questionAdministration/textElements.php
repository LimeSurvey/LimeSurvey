<div class="col-12">
    <div class="panel panel-default col-12 question-option-general-container">
        <div class="panel-heading">Text elements</div>

        <?php foreach($oSurvey->allLanguages as $lang): ?>
        <div class="lang-hide lang-<?= $lang; ?>">

            <div class="panel-body">
                <div class="col-12 ls-space margin all-5 scope-contains-ckeditor">
                    <div class="ls-flex-row">
                        <div class="ls-flex-item grow-2 text-left">
                            <label class="col-sm-12"><?= gT('Question'); ?></label>
                        </div>
                    </div>
                    <div class="htmleditor input-group">
                        <?= CHtml::textArea(
                            "questionI10N[$lang][question]",
                            $question->questionl10ns[$lang]->question ?? '',
                            array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"question_{$lang}")
                        ); ?>
                        <?= getEditor(
                            "questionI10N[$lang][question]",
                            "question_" . $lang,
                            "[".gT("Question:","js")."](".$lang.")",
                            $oSurvey->sid,
                            $question->gid ?? 0,
                            $question->sid ?? 0,
                            $action = '');
                        ?>
                    </div>
                </div>
                <div class="col-12 ls-space margin all-5 scope-contains-ckeditor">
                    <div class="ls-flex-row">
                        <div class="ls-flex-item grow-2 text-left">
                            <label class="col-sm-12"><?= gT('Help:'); ?></label>
                        </div>
                    </div>
                    <div class="htmleditor input-group">
                        <?= CHtml::textArea(
                            "questionI10N[$lang][help]",
                            $question->questionl10ns[$lang]->help ?? '',
                            array('class'=>'form-control','cols'=>'60','rows'=>'4','id'=>"help_{$lang}")
                        ); ?>
                        <?= getEditor(
                            "questionI10N[$lang][help]",
                            "help_".$lang,
                            "[".gT("Help:", "js")."](".$lang.")",
                            $oSurvey->sid,
                            $question->gid ?? 0,
                            $question->qid ?? 0,
                            $action = ''
                        ); ?>
                    </div>
                </div>
                <div style="height: 300px;">
                    <label class="col-sm-6">
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

                    <?= CHtml::textArea(
                        "questionI10N[$lang][script]",
                        !empty($editfile) ? file_get_contents($editfile) : '',
                        [
                            'id' => "questionI10N[{$lang}][script]",
                            'rows' => '10',
                            'cols' => '40',
                            'data-filetype' => 'javascript',
                            'class' => 'ace default', // . $sTemplateEditorMode,
                        ]
                    ); ?>
                    <p class="alert well">
                        <?= gt("This optional script field will be wrapped, so that the script is correctly executed after the question is on the screen. If you do not have the correct permissions, this will be ignored"); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div> 
