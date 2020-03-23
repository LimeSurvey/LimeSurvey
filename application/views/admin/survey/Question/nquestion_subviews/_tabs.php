<?php
/**
 * This view displays the tabs for the question creation
 *
 * @var AdminController $this
 * @var Survey $oSurvey
 * @var array $oQuestion
 */
?>
<?php PrepareEditorScript(true, $this); ?>
<?php if ($oQuestion->title) {
    $sPattern="^([a-zA-Z][a-zA-Z0-9]*|{$oQuestion->title})$";
} else {
    $sPattern="^[a-zA-Z][a-zA-Z0-9]*$";
} ?>

<!-- New question language tabs -->
<ul class="nav nav-tabs" style="margin-right: 8px;">
    <li role="presentation" class="active">
        <a role="tab" data-toggle="tab" href="#<?php echo $oSurvey->language; ?>">
            <?php echo getLanguageNameFromCode($oSurvey->language, false); ?>
            (<?php eT("Base language"); ?>)
        </a>
    </li>
    <?php foreach ($oSurvey->additionalLanguages as $addlanguage):?>
    <li role="presentation">
        <a data-toggle="tab" href="#<?php echo $addlanguage; ?>">
            <?php echo getLanguageNameFromCode($addlanguage, false); ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<!-- Editors for each languages -->
<div class="tab-content" v-pre>

    <!-- Base Language tab-pane -->
    <div id="<?php echo $oSurvey->language; ?>" class="tab-pane fade in active">

        <!-- Question Code -->
        <div class="form-group">
            <label class=" control-label" for='title'><?php eT("Code:"); ?></label>
            <div class="">
                <?php echo CHtml::textField("title", $oQuestion->title, array('class'=>'form-control','size'=>"20",'maxlength'=>'20','pattern'=>$sPattern,"autofocus"=>"autofocus",'id'=>"title")); ?>
                <span class='text-warning'><?php  eT("Required"); ?>
                </span>
            </div>
        </div>
        <div class="container-center">
            <?php
            App()->twigRenderer->renderQuestion($oQuestion->getCurrentView(), $oQuestion->getCurrentViewData());
        ?>
        </div>
        <!-- Question Help -->
        <div class="form-group">
            <label class=" control-label" for='help_<?php echo $oSurvey->language; ?>'><?php eT("Help:"); ?></label>
            <div class="">
                <div class="htmleditor input-group">
                    <?php echo CHtml::textArea("help_".$oSurvey->language, $oQuestion->questionl10ns[$oSurvey->language]->help, array('class'=>'form-control','cols'=>'60','rows'=>'4','id'=>"help_{$oSurvey->language}")); ?>
                    <?php echo getEditor("question-help", "help_".$oSurvey->language, "[".gT("Help:", "js")."](".$oSurvey->language.")", $surveyid, $gid, $qid, $action); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Other languages tab-panes -->
    <?php if (!$adding):?>
    <?php foreach ($oQuestion->questionl10ns as $sLanguage=>$oQuestionL10n): ?>
    <?php if ($sLanguage==$oSurvey->language) {
            continue;
        } ?>
    <div id="<?php echo $sLanguage; ?>" class="tab-pane fade">
        <div class="form-group">
            <label class=" control-label" for='question_<?php echo $sLanguage; ?>'><?php eT("Question:"); ?></label>
            <div class="">
                <div class="htmleditor input-group">
                    <?php echo CHtml::textArea("question_{$sLanguage}", $oQuestionL10n->question, array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"question_{$sLanguage}")); ?>
                    <?php echo getEditor("question-text", "question_".$sLanguage, "[".gT("Question:", "js")."](".$sLanguage.")", $surveyid, $gid, $qid, $action); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class=" control-label" for='help_<?php echo $sLanguage; ?>'><?php eT("Help:"); ?></label>
            <div class="">
                <div class="htmleditor input-group">
                    <?php echo CHtml::textArea("help_{$sLanguage}", $oQuestionL10n->help, array('class'=>'form-control','cols'=>'60','rows'=>'4','id'=>"help_{$sLanguage}")); ?>
                    <?php echo getEditor("question-help", "help_".$sLanguage, "[".gT("Help:", "js")."](".$sLanguage.")", $surveyid, $gid, $qid, $action); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach;?>
    <?php else:?>

    <?php foreach ($oSurvey->additionalLanguages as $addlanguage): ?>
    <div id="<?php echo $addlanguage; ?>" class="tab-pane fade">

        <div class="form-group">
            <label class=" control-label" for='question_<?php echo $addlanguage; ?>'><?php eT("Question:");?></label>
            <div class="">
                <div class="htmleditor input-group">
                    <?php echo CHtml::textArea("question_{$addlanguage}", "", array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"question_{$addlanguage}")); ?>
                    <?php echo getEditor("question-text", "question_".$addlanguage, "[".gT("Question:", "js")."](".$addlanguage.")", $surveyid, $gid, $qid, $action); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class=" control-label" for='help_<?php echo $addlanguage; ?>'><?php eT("Help:"); ?></label>
            <div class="">
                <div class="htmleditor input-group">
                    <?php echo CHtml::textArea("help_{$addlanguage}", "", array('class'=>'form-control','cols'=>'60','rows'=>'4','id'=>"help_{$addlanguage}")); ?>
                    <?php echo getEditor("question-help", "help_".$addlanguage, "[".gT("Help:", "js")."](".$addlanguage.")", $surveyid, $gid, $qid, $action); ?>
                </div>
            </div>
        </div>

    </div>
    <?php endforeach; ?>

    <?php endif;?>
</div>

<div id='questionactioncopy' class='extra-action'>
    <button type='submit' class="btn btn-primary saveandreturn hidden" name="redirection" value="edit"><?php eT("Save") ?>
    </button>
    <input type='submit' value='<?php eT("Save and close"); ?>'
        class="btn btn-default hidden" />
</div>