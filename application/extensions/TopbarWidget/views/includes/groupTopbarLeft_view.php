<?php
    // Tools dropdown button
    $toolsDropdownItems = $this->render('includes/groupToolsDropdownItems', get_defined_vars(), true);
?>
<?php if (!empty(trim($toolsDropdownItems))): ?>
    <!-- Tools  -->
    <div class="btn-group hidden-xs">

        <!-- Main button dropdown -->
        <button id="ls-question-group-tools-button" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="icon-tools" ></span>
            <?php eT('Tools'); ?>&nbsp;<span class="caret"></span>
        </button>

        <!-- dropdown -->
        <ul class="dropdown-menu">
            <?= $toolsDropdownItems ?>
        </ul>
    </div>
<?php endif; ?>

<?php
/**
 * Include the Survey Preview and Group Preview buttons
 */
$this->render('includes/previewSurveyAndGroupButtons_view', get_defined_vars());
?>
