<!-- Show summary information -->
<?php if ($hasResponsesReadPermission): ?>
    <a class="btn btn-outline-secondary pjax"
       href='<?php echo Yii::App()->createUrl("responses/index/", ['surveyId' => $oSurvey->sid]); ?>' role="button">
        <span class="fa fa-list-alt"></span>
        <?php eT("Summary"); ?>
    </a>
<?php endif; ?>

<?php if ($hasResponsesReadPermission): ?>
    <!-- Display Responses -->
    <a class="btn btn-outline-secondary pjax"
       href='<?php echo Yii::App()->createUrl("responses/browse/", ['surveyId' => $oSurvey->sid]); ?>' role="button">
        <span class="fa fa-list"></span>
        <?php eT("Display responses"); ?>
    </a>
<?php endif; ?>


<!-- Dataentry Screen for Survey -->
<?php if ($hasResponsesCreatePermission): ?>
    <a class="btn btn-outline-secondary"
       href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/view/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="fa fa-keyboard-o"></span>
        <?php eT("Data entry"); ?>
    </a>
<?php endif; ?>

<?php if ($hasStatisticsReadPermission): ?>
    <!-- Get statistics from these responses -->
    <a class="btn btn-outline-secondary"
       href='<?php echo Yii::App()->createUrl("admin/statistics/sa/index/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="fa fa-bar-chart"></span>
        <?php eT("Statistics"); ?>
    </a>

    <!-- Get time statistics from these responses -->
    <?php if ($isTimingEnabled == "Y"): ?>
        <a class="btn btn-outline-secondary"
           href='<?php echo Yii::App()->createUrl("responses/time/", ['surveyId' => $oSurvey->sid]); ?>' role="button">
            <span class="fa fa-clock-o"></span>
            <?php eT("Timing statistics"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>


<!-- Export -->
<?php if ($hasResponsesExportPermission): ?>
    <div class="d-inline-flex">
    <?php
    $exportDropdownItems = $this->render('includes/responsesExportDropdownItems', get_defined_vars(), true);
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-tools-button',
        'id' => 'ls-tools-button',
        'text' => gT('Export'),
        'icon' => 'icon-export',
        'isDropDown' => true,
        'dropDownContent' => $exportDropdownItems,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]);
    ?>
    </div>
<?php endif; ?>


<!-- Import -->
<?php if ($hasResponsesCreatePermission): ?>
    <div class="d-inline-flex">
    <?php
    $importDropdownItems = $this->render('includes/responsesImportDropdownItems', get_defined_vars(), true);
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-tools-button',
        'id' => 'ls-tools-button',
        'text' => gT('Import'),
        'icon' => 'icon-import',
        'isDropDown' => true,
        'dropDownContent' => $importDropdownItems,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]);
    ?>
    </div>
<?php endif; ?>


<!-- View Saved but not submitted Responses -->
<?php if ($hasResponsesReadPermission): ?>
    <a class="btn btn-outline-secondary"
            href='<?php echo Yii::App()->createUrl("admin/saved/sa/view/surveyid/$oSurvey->sid"); ?>' role="button">
        <span class="icon-saved"></span>
        <?php eT("View Saved but not submitted Responses"); ?>
    </a>
<?php endif; ?>


<!-- Iterate survey -->
<?php if ($hasResponsesDeletePermission): ?>
    <?php if (!$oSurvey->isAnonymized && $oSurvey->isTokenAnswersPersistence): ?>
        <a class="btn btn-outline-secondary"
                href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/iteratesurvey/surveyid/$oSurvey->sid"); ?>'
                role="button">
            <span class="fa fa-repeat"></span>
            <?php eT("Iterate survey"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<!-- Batch deletion -->
<?php if ($hasResponsesDeletePermission): ?>
    <a
        id="response-batch-deletion"
        role="button"
        href="<?php echo Yii::App()->createUrl("responses/delete/", ["surveyId" => $oSurvey->sid]); ?>"
        data-post="{}"
        data-show-text-area="true"
        data-use-ajax="true"
        data-grid-id="responses-grid"
        data-grid-reload="true"
        data-text="<?php eT('Enter a list of response IDs that are to be deleted, separated by comma.') ?><br/><?= gT('Please note that if you delete an incomplete response during a running survey, the participant will not be able to complete it.'); ?>"
        title="<?php eT('Batch deletion') ?>"
        class="btn btn-outline-secondary selector--ConfirmModal">

        <span class="fa fa-trash text-danger"></span>
        <?php eT("Batch deletion"); ?>
    </a>
<?php endif; ?>
