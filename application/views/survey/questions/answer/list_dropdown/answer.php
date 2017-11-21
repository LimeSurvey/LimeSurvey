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

<div class="<?php echo $coreClass ?>  form-group form-inline">
    <select
            class="form-control list-question-select"
            name="<?php echo $name; ?>"
            id="answer<?php echo $name; ?>"
            <?php  echo ($dropdownSize) ? "size=$dropdownSize" : "" ; ?>
            aria-labelledby="ls-question-text-<?php echo $basename; ?>"
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
    <?php
    /* Value for expression manager javascript (use id) ; no need to submit */
    echo \CHtml::hiddenField("java{$name}",$value,array(
        'id' => "java{$name}",
        'disabled' => "disabled",
    ));
    ?>
</div>
<!-- end of answer  -->
