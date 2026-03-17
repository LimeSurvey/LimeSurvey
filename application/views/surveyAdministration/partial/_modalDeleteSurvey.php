<?php

?>

<div id="deleteSurvey_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <?php
            App()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Delete survey')]
            );
            ?>
            <?php echo CHtml::form(
                ['surveyAdministration/delete'],
                'post',
                ['id' => 'deleteSurveyForm']
            ) ?>
            <input type="hidden" id="deleteSurveyId" name="iSurveyID" value="" />
            <div class="modal-body">
                <p class="fw-bold">
                    <?php eT("You are about to delete this survey"); ?> (<?php eT("ID"); ?>: <span id="deleteSurveyIdDisplay"></span>)
                </p>
                <p>
                    <?php eT("This process will delete this survey, and all related groups, questions answers and conditions."); ?><br />
                    <?php eT("It will also delete any resources/files that have been uploaded for this survey."); ?><br />
                    <?php eT("We recommend that before you delete this survey you export the entire survey from the main administration screen."); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary text-light" data-bs-dismiss="modal">
                    <?php eT("Cancel"); ?>
                </button>
                <button type="submit" name="delete" value="yes" class="btn btn-danger text-light">
                    <?php eT("Delete survey"); ?>
                </button>
            </div>
            <?php echo CHtml::endForm(); ?>
        </div>
    </div>
</div>

<script>
    function deleteSurveyConfirm(surveyId) {
        document.getElementById('deleteSurveyId').value = surveyId;
        document.getElementById('deleteSurveyIdDisplay').textContent = surveyId;
        document.getElementById('deleteSurveyForm').action = '<?php echo Yii::app()->createUrl('surveyAdministration/delete'); ?>/surveyid/' + surveyId;
    }
</script>
