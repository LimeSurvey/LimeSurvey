<?php
/**
 * @var SurveyAdministrationController $this
 * @var Survey $survey
 * @var int $surveyid
 */

?>
<div class='side-body'>
    <div class="row">
        <div class="col-12 content-right">
        <br/>

        <div class="jumbotron message-box">
                <h2><?php eT("Delete survey"); ?></h2>
                <p class="lead"><?php eT("Warning"); ?></p>
                <p>
                    <strong><?php eT("You are about to delete this survey"); ?> (<?php echo $surveyid; ?>)</strong><br /><br />
                    <?php eT("This process will delete this survey, and all related groups, questions answers and conditions."); ?><br /><br />
                    <?php eT("It will also delete any resources/files that have been uploaded for this survey."); ?><br /><br />
                    <?php eT("We recommend that before you delete this survey you export the entire survey from the main administration screen."); ?>

                    <?php if($survey->hasTokensTable):?>
                        <span class="text-danger"><?php eT("This survey is active and a responses table exists. If you delete this survey, these responses (and files) will be deleted. We recommend that you export the responses before deleting this survey."); ?></span><br /><br />
                    <?php endif; ?>


                    <?php if ($survey->hasTokensTable): ?>
                        <span class="text-danger"><?php eT("This survey has an associated participant list. If you delete this survey this participant list will be deleted. We recommend that you export or backup these participants before deleting this survey."); ?><br /><br />
                    <?php endif; ?>

                </p>
                <p>
                    <?php echo CHtml::beginForm($this->createUrl("surveyAdministration/delete/surveyid/{$surveyid}"), 'post');?>
                        <input type='hidden' name='delete' value='yes'>
                        <input type='submit'  class="btn btn-lg btn-warning" value='<?php eT("Delete survey"); ?>'>
                        <input type='button'  class="btn btn-lg btn-cancel" value='<?php eT("Cancel"); ?>' onclick="window.open('<?php echo Yii::app()->createUrl("surveyAdministration/view/surveyid/$surveyid"); ?>', '_top')" />
                    <?php echo CHtml::endForm(); ?>
                </p>
        </div>

        </div>
    </div>
</div>
