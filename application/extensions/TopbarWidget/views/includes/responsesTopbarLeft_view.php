<!-- Show summary information -->
<?php if ($hasResponsesReadPermission): ?>
    <button type="button" class="btn btn-outline-secondary pjax" href='<?php echo Yii::App()->createUrl("responses/index/", ['surveyId' => $oSurvey->sid]); ?>' role="button">
        <span class="fa fa-list-alt text-success"></span>
        <?php eT("Summary"); ?>
    </button>
<?php endif;?>

<?php if ($hasResponsesReadPermission): ?>
    <!-- Display Responses -->
    <button type="button" class="btn btn-outline-secondary pjax" href='<?php echo Yii::App()->createUrl("responses/browse/", ['surveyId' => $oSurvey->sid]); ?>' role="button">
        <span class="fa fa-list text-success"></span>
        <?php eT("Display responses"); ?>
    </button>
<?php endif;?>


<!-- Dataentry Screen for Survey -->
<?php if ($hasResponsesCreatePermission): ?>
    <button type="button" class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/view/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="fa fa-keyboard-o text-success"></span>
        <?php eT("Data entry"); ?>
    </button>
<?php endif;?>

<?php if ($hasStatisticsReadPermission): ?>
    <!-- Get statistics from these responses -->
    <button type="button" class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("admin/statistics/sa/index/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="fa fa-bar-chart text-success"></span>
        <?php eT("Statistics"); ?>
    </button>

    <!-- Get time statistics from these responses -->
    <?php if ($isTimingEnabled == "Y"):?>
        <button type="button" class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("responses/time/", ['surveyId' => $oSurvey->sid]); ?>' role="button">
            <span class="fa fa-clock-o text-success"></span>
            <?php eT("Timing statistics"); ?>
    </button>
    <?php endif;?>
<?php endif;?>


<!-- Export -->
<?php if ($hasResponsesExportPermission): ?>
    <div class="btn-group">
        <button type="button" role="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="icon-export text-success"></span>
            <?php eT("Export"); ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">

            <!-- Export results to application -->
            <li>
                <a class="dropdown-item" href='<?php echo Yii::App()->createUrl("admin/export/sa/exportresults/surveyid/$oSurvey->sid"); ?>'>
                    <?php eT("Export responses"); ?>
                </a>
            </li>

            <!-- Export results to a SPSS/PASW command file -->
            <li>
                <a class="dropdown-item" href='<?php echo Yii::App()->createUrl("admin/export/sa/exportspss/sid/$oSurvey->sid"); ?>'>
                    <?php eT("Export responses to SPSS"); ?>
                </a>
            </li>

            <!-- Export a VV survey file -->
            <li>
                <a class="dropdown-item" href='<?php echo Yii::App()->createUrl("admin/export/sa/vvexport/surveyid/$oSurvey->sid"); ?>'>
                    <?php eT("Export a VV survey file"); ?>
                </a>
            </li>

        </ul>
    </div>
<?php endif;?>


<!-- Import -->
<?php if ($hasResponsesCreatePermission): ?>
    <div class="btn-group">
        <button type="button" role="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="icon-import text-success"></span>
            <?php eT("Import"); ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">

            <!-- Import responses from a deactivated survey table -->
            <li>
                <a class="dropdown-item" href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/import/surveyid/$oSurvey->sid"); ?>' role="button">
                    <?php eT("Import responses from a deactivated survey table"); ?>
                </a>
            </li>

            <!-- Import a VV survey file -->
            <li>
                <a class="dropdown-item" href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/vvimport/surveyid/$oSurvey->sid"); ?>' role="button">
                    <?php eT("Import a VV survey file"); ?>
                </a>
            </li>
        </ul>
    </div>
<?php endif;?>


<!-- View Saved but not submitted Responses -->
<?php if ($hasResponsesReadPermission): ?>
    <button type="button" class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("admin/saved/sa/view/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="icon-saved text-success"></span>
        <?php eT("View Saved but not submitted Responses"); ?>
    </button>
<?php endif;?>


<!-- Iterate survey -->
<?php if ($hasResponsesDeletePermission): ?>
    <?php if (!$oSurvey->isAnonymized && $oSurvey->isTokenAnswersPersistence): ?>
        <button type="button" class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/iteratesurvey/surveyid/$oSurvey->sid"); ?>' role="button">
            <span class="fa fa-repeat text-success"></span>
            <?php eT("Iterate survey"); ?>
    </button>
    <?php endif;?>
<?php endif;?>

<!-- Batch deletion -->
<?php if ($hasResponsesDeletePermission): ?>
    <button
        id="response-batch-deletion"
        type="button"
        role="button"
        href="<?php echo Yii::App()->createUrl("responses/delete/", ["surveyId" => $oSurvey->sid]); ?>"
        data-post="{}"
        data-show-text-area="true"
        data-use-ajax="true"
        data-grid-id="responses-grid"
        data-grid-reload="true"
        data-text="<?php eT('Enter a list of response IDs that are to be deleted, separated by comma.')?><br/><?= gT('Please note that if you delete an incomplete response during a running survey, the participant will not be able to complete it.'); ?>"
        title="<?php eT('Batch deletion')?>"
        class="btn btn-outline-secondary selector--ConfirmModal">

        <span class="fa fa-trash text-danger"></span>
            <?php eT("Batch deletion"); ?>
    </button>
<?php endif;?>
