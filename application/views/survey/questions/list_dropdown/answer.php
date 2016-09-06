<?php
/**
 * List DropDown select Html
 *
 * @var $sOptions         : the select options, generated with the view item_options.php
 * @var $sOther           : the other input field, generated with the view item_noanswer.php
 *
 * @var $name
 * @var $dropdownSize
 * @var $checkconditionFunction
 * @var $select_show_hide
 *
 */
?>

<!-- List Dropdown -->

<!-- answer-->

<p class="question answer-item dropdown-item form-group form-inline">
    <label for="answer<?php echo $name; ?>" class="sr-only label">
        <?php eT('Please choose'); ?>
    </label>

    <select
            class="form-control"
            name="<?php echo $name; ?>"
            id="answer<?php echo $name; ?>"
            <?php echo $dropdownSize; ?>
            onchange="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type);"
    >
        <?php
            // rows/option.php
            echo $sOptions;
        ?>

    </select>

    <?php
        // rows/othertext.php
        echo $sOther;
    ?>

    <input
        type="hidden"
        name="java<?php echo $name; ?>"
        id="java<?php echo $name; ?>"
        value="<?php echo $value; ?>"
    />


</p>
<!-- end of answer  -->
