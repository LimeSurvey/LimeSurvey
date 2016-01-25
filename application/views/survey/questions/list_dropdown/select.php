<?php
/**
 * List DropDown select header Html
 * @var $name       $ia[1]
 * @var $dropdownSize
 * @var $checkconditionFunction $checkconditionFunction.'(this.value, this.name, this.type);'.$sselect_show_hide
 */
?>
<p class="question answer-item dropdown-item">
    <label for="answer<?php echo $name; ?>" class="hide label">
        <?php eT('Please choose'); ?>
    </label>

    <select
            class="form-control list-question-select"
            name="<?php echo $name; ?>"
            id="answer<?php echo $name; ?>"
            <?php echo $dropdownSize; ?>
            onchange="<?php echo $checkconditionFunction; ?>"
    >
