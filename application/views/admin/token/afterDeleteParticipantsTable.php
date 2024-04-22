<?php
    /**
     * View which will appear, if the particpants table is deleted.
     */
?>
<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <h1 class="h1 pagetitle"> 
        <?= gT("Survey participants table deleted") ?> :
        <small class="d-block"><?= viewHelper::flatEllipsizeText($oSurvey->currentLanguageSettings->surveyls_title, TRUE, 60, '…') . " (" . gT("ID") . " " . $oSurvey->sid . ")"; ?></small>
    </h1>
    <div class="row welcom survey-action">
        <div class="col-12 content-right">
            <div class="card card-primary">
                <p>
                    <?php eT('The survey participants table has been deleted and your survey has been switched back to open-access mode. Participants no longer require an access code to access the survey.'); ?>
                    <br /> <br />
                    <?php eT('A backup of this table has been made, which can only be accessed by your site administrator.'); ?>
                    <br />
                    <?php echo '(' . $backupTableName . ')'; ?>
                    <br /> <br />
                    <?php eT("You can switch back to closed-access mode at any time. Navigate to Settings --> Survey participants and click on the 'Switch to closed-access mode' button."); ?>
                    <br /><br />
                    <a
                        class="btn btn-outline-secondary"
                        href="<?php echo $this->createUrl("surveyAdministration/view/surveyid/{$iSurveyId}"); ?>"
                    ><?php eT('Main Admin Screen'); ?></a>
            </div>
        </div>
    </div>
</div>
