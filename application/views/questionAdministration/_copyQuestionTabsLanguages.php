<?php

/**
 * This view displays the tabs for the question creation
 *
 * @var QuestionAdministrationController $this
 * @var Survey $oSurvey
 * @var Question $oQuestion
 */
?>
<?php PrepareEditorScript(true, $this); ?>
<?php if ($oQuestion->title) {
    $sPattern = "^([a-zA-Z][a-zA-Z0-9]*|{$oQuestion->title})$";
} else {
    $sPattern = "^[a-zA-Z][a-zA-Z0-9]*$";
} ?>

<!-- Question Code -->
<div class="form-group">
    <label class=" control-label"  for='title'><?php eT("Code:"); ?></label>
    <div class="">
        <?php echo CHtml::textField(
            "title",
            $oQuestion->title . 'Copy',
            array('class'=>'form-control','size'=>"20",'maxlength'=>'20','pattern'=>$sPattern,"autofocus"=>"autofocus",'id'=>"title")
        );
        ?>
        <span class='text-warning'><?php  eT("Required"); ?> </span>
        <p id="question-code-unique-warning" class="hidden text-warning"><?= gT('Question codes must be unique.'); ?></p>
    </div>
</div>

<!-- New question language tabs -->
<ul class="nav nav-tabs" style="margin-right: 8px;" >
    <li role="presentation" class="active">
        <a role="tab" data-toggle="tab" href="#<?php echo $oSurvey->language; ?>">
            <?php echo getLanguageNameFromCode($oSurvey->language,false); ?> (<?php eT("Base language"); ?>)
        </a>
    </li>
</ul>

<!-- Editors for each languages -->
<div class="tab-content" >

    <!-- Base Language tab-pane -->
    <div id="<?php echo $oSurvey->language; ?>" class="tab-pane fade in active">

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
                        $oQuestion->questionl10ns[$oSurvey->language]->question,
                        array('class'=>'form-control','cols'=>'60','rows'=>'5','id'=>"question_{$oSurvey->language}",
                        'disabled' => "disabled")
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id='questionactioncopy' class='extra-action'>
    <button type='submit' class="btn btn-primary saveandreturn hidden"  name="redirection" value="edit"><?php eT("Save") ?> </button>
    <input type='submit' value='<?php eT("Save and close"); ?>'  class="btn btn-default hidden"/>
</div>
