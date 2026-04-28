<?php if ($hasSurveyContentUpdatePermission || $hasSurveyContentExportPermission || ($hasSurveyContentCreatePermission && ($oSurvey->active != 'Y')) || $hasSurveyContentReadPermission || !empty($showDeleteButton)) : ?>
    <ul class="dropdown-menu">
        <?php echo $this->renderPartial(
            '/questionAdministration/partial/topbarBtns/questionToolsDropdownItems',
            get_defined_vars(),
            true
        ); ?>
    </ul>
<?php endif; ?>
