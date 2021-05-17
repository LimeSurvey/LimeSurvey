<?php
/**
 * Question Editor toolbar layout.
 * 
 * This is used instead of the base layout because two toolbars must be rendered at the same time (one of them hidden)
 * 
 */
$aData = get_defined_vars();
$leftSideContentSummary = $this->render('includes/questionTopbarLeft_view', get_defined_vars(), true);
$rightSideContentSummary = $this->render(
    'includes/questionTopbarRight_view',
    array_merge(
        get_defined_vars(),
        [
            'showEditButton' => true,
            'showDeleteButton' => true,
            'showSaveButton' => false,
            'showSaveAndCloseButton' => false,
            'showCloseButton' => false,
        ]
    ),
    true
);
$leftSideContentEditor = $this->render('includes/editQuestionTopbarLeft_view', get_defined_vars(), true);
$rightSideContentEditor = $this->render('includes/editQuestionTopbarRight_view', get_defined_vars(), true);

?>

<div class='menubar surveybar' id="<?= !(empty($topbarId)) ? $topbarId : 'surveybarid' ?>">
    <?php if ($oQuestion->qid !== 0): ?>
        <div id="question-summary-topbar" class='row container-fluid'>
            <!-- Left Side -->
            <div class="<?= !empty($rightSideContentSummary) ? 'col-md-8' : 'col-md-12'?>">
                <?= $leftSideContentSummary ?>
            </div>

            <!-- Right Side -->
            <div class="<?= !empty($leftSideContentSummary) ? 'col-md-4' : 'col-md-12'?> pull-right text-right">
                <?= $rightSideContentSummary ?>
            </div>
        </div>
    <?php endif; ?>
    <div id="question-create-edit-topbar" class='row container-fluid' style="display: none">
        <!-- Left Side -->
        <div class="<?= !empty($rightSideContentEditor) ? 'col-md-8' : 'col-md-12'?>">
            <?= $leftSideContentEditor ?>
        </div>

        <!-- Right Side -->
        <div class="<?= !empty($leftSideContentEditor) ? 'col-md-4' : 'col-md-12'?> pull-right text-right">
            <?= $rightSideContentEditor ?>
        </div>
    </div>
</div>
