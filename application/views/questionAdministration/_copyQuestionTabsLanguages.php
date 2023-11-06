<?php

/**
 * This view displays the tabs for the question creation
 *
 * @var QuestionAdministrationController $this
 * @var Survey $oSurvey
 * @var Question $oQuestion
 */
?>
<div class="row">
    <!-- Question code -->
    <?php $this->renderPartial(
        "questionCode",
        [
            'question' => $oQuestion,
            'newTitle' => $oQuestion->title . "Copy",
            'newQid' => true
        ]
    ); ?>
    <!-- Language selector -->
    <?php $this->renderPartial("languageselector", ['oSurvey' => $oSurvey]); ?>
</div>

<div class="row">
    <div class="col-xs-12">
        <!-- Text elements -->
        <?php $this->renderPartial(
            "textElements",
            [
                'oSurvey'         => $oSurvey,
                'question'        => $oQuestion,
                'showScriptField' => false,
            ]
        ); ?>
    </div>
</div>

<div id='questionactioncopy' class='extra-action'>
    <button type='submit' class="btn btn-primary saveandreturn hidden"  name="redirection" value="edit"><?php eT("Save") ?> </button>
    <input type='submit' value='<?php eT("Save and close"); ?>'  class="btn btn-default hidden"/>
</div>
