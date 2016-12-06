<?php
/**
 * List DropDown select Html
 *
 * @var $sOptions : the select options, generated with the view item_options.php
 * @var $sOther : the other input field, generated with the view item_noanswer.php
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
<div class="row answer-dropdown">
    <div class="col-sm-4">
        <div class="answer-dropdown__inner">
            <label for="answer<?php echo $name; ?>" class="hide label">
                <?php eT('Please choose'); ?>
            </label>
            <select
                    class="form-control list-question-select answer-dropdown__select"
                    name="<?php echo $name; ?>"
                    id="answer<?php echo $name; ?>"
                <?php echo ($dropdownSize) ? "size=$dropdownSize" : ""; ?>
                    onchange="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type);<?php echo $select_show_hide; ?>"
            >
                <?php
                // rows/option.php
                echo $sOptions;
                ?>

            </select>

            <input
                    type="hidden"
                    name="java<?php echo $name; ?>"
                    id="java<?php echo $name; ?>"
                    value="<?php echo $value; ?>"
            />

            <?php
            // rows/othertext.php
            echo $sOther;
            ?>
        </div>
    </div>
</div>
<!-- end of answer  -->
