<?php
/**
 * Question Editor toolbar layout.
 * This is used instead of the base layout because two toolbars must be rendered at the same time (one of them hidden)
 * @var Question $oQuestion
 */

$aData = get_defined_vars();
$leftSideContentSummary = $this->render('includes/questionTopbarLeft_view', get_defined_vars(), true);
$leftSideContentEditor = $this->render('includes/editQuestionTopbarLeft_view', get_defined_vars(), true);
$rightSideContent = $this->render('includes/editQuestionTopbarRight_view', get_defined_vars(), true);
$rightSideContentSummary = $this->render('includes/questionSummaryTopbarRight_view', get_defined_vars(), true);
?>

<!-- Question Top Bar -->
<div class='menubar surveybar' id="<?= !(empty($topbarId)) ? $topbarId : 'surveybarid' ?>">
    <div class="container-fluid">
        <?php if ($oQuestion->qid !== 0) : ?>
            <div id="question-summary-topbar" class='row'>
                <!-- Left Side -->
                <div class="col-lg-8">
                    <?= $leftSideContentSummary ?>
                </div>

                <div class="col-lg-4 pull-right text-end">
                    <?= $rightSideContentSummary ?>
                </div>
            </div>
        <?php endif; ?>
        <div id="question-create-edit-topbar" class='row' style="display: none;">
            <!-- Left Side -->
            <div class="<?= !empty($rightSideContent) ? 'col-lg-6' : 'col-12' ?>">
                <?= $leftSideContentEditor ?>
            </div>

            <!-- Right Side -->
            <div class="<?= !empty($leftSideContentEditor) ? 'col-lg-6' : 'col-12' ?> pull-right text-end">
                <?= $rightSideContent ?>
            </div>
        </div>
    </div>
</div>
