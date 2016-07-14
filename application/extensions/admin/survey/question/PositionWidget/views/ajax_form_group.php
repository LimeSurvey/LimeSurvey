<?php
/**
 * Position Widget Ajax Form Group View
 * Display a hidden input that will be used to load dynamically via ajax the position widget
 *
 */
?>

<!-- PositionWidget : ajax_form_group -->

<input
    type="hidden"
    id="question_position_container"
    data-gid="<?php echo $this->oQuestionGroup->gid; ?>"
    data-url="<?php echo App()->createUrl($this->reloadAction);?>"
    data-classes="<?php echo $this->classes;?>"
    data-group-selector-id = "<?php echo $this->dataGroupSelectorId; ?>"
/>

<!-- PositionWidget : end of ajax_form_group -->
