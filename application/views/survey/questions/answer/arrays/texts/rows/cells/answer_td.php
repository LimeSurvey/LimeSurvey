<?php
/**
 * Answer cell
 *
 * @var $ld
 * @var $myfname2
 * @var $labelText $labelans[$thiskey]
 * @var $kpclass
 * @var $maxlength
 * @var $inputwidth
 * @var $value
 */
?>

<!-- answer_td -->
<td class="answer_cell_<?php echo $ld;?> answer-item text-item<?php echo ($error) ? " has-error" : ""; ?>">
    <label class="ls-label-xs-visibility" for="answer<?php echo $myfname2; ?>">
        <?php echo $labelText;?>
    </label>
    <input
        type="text"
        name="<?php echo $myfname2; ?>"
        id="answer<?php echo $myfname2; ?>"
        class="form-control <?php echo $kpclass; ?>"
        <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?>
        <?php echo ($maxlength ? 'maxlength='.$maxlength: ''); ?>
        value="<?php echo $value;?>"
        data-number='<?php echo $isNumber; ?>'
        data-integer='<?php echo $isInteger; ?>'
    />
    <input
        type="hidden"
        name="java<?php echo $myfname2;?>"
        id="java<?php echo $myfname2; ?>"
    />
</td>
<!-- end of answer_td -->
