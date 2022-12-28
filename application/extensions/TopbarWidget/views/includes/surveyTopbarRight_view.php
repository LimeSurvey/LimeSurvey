<?php
/**
 * @var string $closeUrl
 * @var string $returnUrl
 */

?>

<!-- White Close button -->
<?php if (!empty($showWhiteCloseButton)) : ?>
    <a class="btn btn-outline-secondary" href="<?php echo $closeUrl ?>" role="button">
        <span class="ri-close-fill"></span>
        <?php eT("Close"); ?>
    </a>
<?php endif; ?>

<!-- Save and Close -->
<?php if (!empty($showSaveAndCloseButton)): ?>
    <a class="btn btn-outline-secondary"
       href="<?php echo $closeUrl; ?>"
       role="button"
       id="save-and-close-button"
       onclick="$(this).addClass('disabled').attr('onclick', 'return false;');">
        <span class="fa fa-saved"></span>
        <?php eT("Save and close"); ?>
    </a>
<?php endif; ?>

<!-- Return -->
<?php if (!empty($showBackButton)): ?>
    <a class="btn btn-outline-secondary" href="<?php echo $returnUrl; ?>" role="button">
        <span class="ri-rewind-fill"></span>
        &nbsp;&nbsp;
        <?php eT('Back') ?>
    </a>
<?php endif; ?>

<!-- Green Save and Close -->
<?php if (!empty($showGreenSaveAndCloseButton)): ?>
    <a class="btn btn-success"
       href="<?php echo $closeUrl; ?>"
       id="save-and-close-button"
       onclick="$(this).addClass('disabled').attr('onclick', 'return false;');">
        <span class="fa fa-saved"></span>
        <?php eT("Save and close"); ?>
    </a>
<?php endif; ?>

<!-- Save -->
<?php if (!empty($showSaveButton)): ?>
    <a id="save-button" class="btn btn-success float-end">
        <i class="ri-check-fill"></i>
        <?php eT("Save"); ?>
    </a>
<?php endif; ?>

<!-- Export -->
<?php if (Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'export')) : ?>
    <?php App()->getController()->renderPartial(
        '/admin/survey/surveybar_displayexport',
        [
            'hasResponsesExportPermission' => $hasResponsesExportPermission,
            'hasTokensExportPermission' => $hasSurveyTokensExportPermission,
            'hasSurveyExportPermission' => $hasSurveyExportPermission,
            'oSurvey' => $oSurvey,
            'onelanguage' => (count($oSurvey->allLanguages) == 1)
        ]
    ); ?>
<?php endif; ?>


<!-- Export -->
<?php if (!empty($showExportButton)): ?>
    <button class="btn btn-success" type="button" name="export-button" id="export-button" data-submit-form=1>
        <span class="ri-download-fill"></span>
        <?php eT("Export"); ?>
    </button>
<?php endif; ?>

<!-- Import -->
<?php if (!empty($showImportButton)): ?>
    <button class="btn btn-success" type="button" name="import-button" id="import-button" data-submit-form=1>
        <span class="ri-upload-fill"></span>
        <?php eT("Import"); ?>
    </button>
<?php endif; ?>

<!-- Close -->
<?php if (!empty($showCloseButton)): ?>
    <a class="btn btn-danger" href="<?php echo $closeUrl; ?>" type="button">
        <span class="ri-close-fill"></span>
        <?php eT("Close"); ?>
    </a>
<?php endif; ?>
