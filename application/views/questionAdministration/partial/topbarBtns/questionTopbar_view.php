<?php
/**
 * Question Editor toolbar layout.
 * This is used instead of the base layout because two toolbars must be rendered at the same time (one of them hidden)
 * @var Question $oQuestion
 */

$aData = get_defined_vars();
$leftSideContentSummary = $this->renderPartial('/questionAdministration/partial/topbarBtns/questionTopbarLeft_view', get_defined_vars(), true);
$leftSideContentEditor = $this->renderPartial('/questionAdministration/partial/topbarBtns/editQuestionTopbarLeft_view', get_defined_vars(), true);
$rightSideContent = $this->renderPartial('/questionAdministration/partial/topbarBtns/editQuestionTopbarRight_view', get_defined_vars(), true);
$rightSideContentSummary = $this->renderPartial('/questionAdministration/partial/topbarBtns/questionSummaryTopbarRight_view', get_defined_vars(), true);

App()->getClientScript()->registerScriptFile(
    App()->getConfig('adminscripts') . 'topbar.js',
    CClientScript::POS_END
);
?>

<!-- Question Top Bar -->
<div class="topbar sticky-top" id="pjax-content">
    <div class="container-fluid">
            <?php if ($oQuestion->qid !== 0) : ?>
                <div id="question-summary-topbar"
                     class='row' <?= empty($tabOverviewEditor) || $tabOverviewEditor === 'editor' ? 'style="display: none;"' : "" ?>>
                    <!-- Title or breadcrumb -->
                    <div class="ls-breadcrumb col-12">
                        <h1 role="presentation"><?= $breadcrumb ?></h1>
                    </div>
                    <!-- Left Side -->
                    <div class="ls-topbar-buttons col">
                        <?= $leftSideContentSummary ?>
                    </div>

                    <div class="ls-topbar-buttons col-md-auto float-end text-end">
                        <?= $rightSideContentSummary ?>
                    </div>
                </div>
            <?php endif; ?>
            <div id="question-create-edit-topbar"
                 class='row' <?= !empty($tabOverviewEditor) && $tabOverviewEditor !== 'editor' ? 'style="display: none;"' : "" ?>>
                <!-- Title or breadcrumb -->
                <div class="ls-breadcrumb col-12">
                    <h1 role="presentation"><?= $breadcrumb ?></h1>
                </div>
                <!-- Left Side -->
                <div class="ls-topbar-buttons col">
                    <?= $leftSideContentEditor ?>
                </div>

                <!-- Right Side -->
                <div class="ls-topbar-buttons col-md-auto float-end text-end">
                    <?= $rightSideContent ?>
                </div>
            </div>
    </div>
</div>
