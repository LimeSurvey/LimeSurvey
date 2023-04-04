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
   <div class="col-6">
    <!-- Question code -->
    <?php $this->renderPartial(
        "questionCode",
        [
            'question' => $oQuestion,
            'newTitle' => $oQuestion->title . "Copy",
            'newQid' => true
        ]
    ); ?>
   </div>
</div>

<div class="row">
    <div class="col-12">
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
    <button role="button" type='submit' class="btn btn-primary saveandreturn d-none" name="redirection" value="edit">
        <?php eT("Save"); ?>
    </button>
    <input type='submit' value='<?php eT("Save and close"); ?>'  class="btn btn-outline-secondary d-none"/>
</div>
