<?php
    // Tools dropdown button
    $toolsDropdownItems = $this->render('includes/groupToolsDropdownItems', get_defined_vars(), true);
?>
<?php if (!empty(trim($toolsDropdownItems))): ?>
    <!-- Tools  groupTopbarLeft-->
    <div class="d-inline-flex">

        <!-- Main button dropdown -->
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-tools-button',
            'id' => 'ls-tools-button',
            'text' => gT('Tools'),
            'menu' => true,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]); ?>

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
