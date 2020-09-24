<div class="col-12">
    <div class="panel panel-default col-12 question-option-general-container">
        <div class="panel-heading">Text elements</div>
        <div class="panel-body">
            <div class="col-12 ls-space margin all-5 scope-contains-ckeditor">
                <div class="ls-flex-row">
                    <div class="ls-flex-item grow-2 text-left">
                        <label class="col-sm-12"><?= gT('Question'); ?></label>
                    </div>
                </div>
                <div class="htmleditor input-group">
                    <?= CHtml::textArea(
                        "question_{$oSurvey->language}",
                        $oQuestion->questionl10ns[$oSurvey->language]->question ?? '',
                        array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"question_{$oSurvey->language}")
                    ); ?>
                    <?= getEditor(
                        "question-text",
                        "question_".$oSurvey->language,
                        "[".gT("Question:","js")."](".$oSurvey->language.")",
                        $oSurvey->sid,
                        $oQuestion->gid ?? 0,
                        $oQuestion->sid ?? 0,
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
                        "help_".$oSurvey->language,
                        $oQuestion->questionl10ns[$oSurvey->language]->help ?? '',
                        array('class'=>'form-control','cols'=>'60','rows'=>'4','id'=>"help_{$oSurvey->language}")
                    ); ?>
                    <?= getEditor(
                        "question-help",
                        "help_".$oSurvey->language,
                        "[".gT("Help:", "js")."](".$oSurvey->language.")",
                        $oSurvey->sid,
                        $oQuestion->gid ?? 0,
                        $oQuestion->qid ?? 0,
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
                        name="selector--scriptForAllLanguages" 
                        id="selector--scriptForAllLanguages"
                        v-model="scriptForAllLanugages"
                    />&nbsp;
                    <label for="selector--scriptForAllLanguages">
                        <?= gT('Set for all languages'); ?>
                    </label>
                </div>

                <?= CHtml::textArea(
                    'editscript',
                    !empty($editfile) ? file_get_contents($editfile) : '',
                    array(
                        'id' => 'editscript',
                        'rows' => '10',
                        'cols' => '20',
                        'data-filetype' => 'javascript',
                        'class' => 'ace default', // . $sTemplateEditorMode,
                    )
                ); ?>
                <p class="alert well">
                    <?= gt("This optional script field will be wrapped, so that the script is correctly executed after the question is on the screen. If you do not have the correct permissions, this will be ignored"); ?>
                </p>
            </div>
        </div>
    </div>
</div> 
