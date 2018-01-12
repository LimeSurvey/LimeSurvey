<?php
/**
 * List with comment, list layout, Html
 *
 * @var $name
 * @var $id
 * @var $value
 * @var $check_ans
 * @var $checkconditionFunction
 * @var $labeltext
 * @var $li_classes
 */
?>
<!-- answer_row -->
<li id='javatbd<?php echo $name.$value; ?>' class="form-group <?php echo $li_classes; ?>">
    <input
        type="radio"
        name="<?php echo $name; ?>"
        id="<?php echo $id; ?>"
        value="<?php echo $value; ?>"
        <?php echo $check_ans; ?>
        onclick="<?php echo $checkconditionFunction; ?>"
    />
    <label for="<?php echo $id; ?>" class="radio-label control-label"><?php echo $labeltext;?></label>
</li>
<!-- end of answer_row -->
