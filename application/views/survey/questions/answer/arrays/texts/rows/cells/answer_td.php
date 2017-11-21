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
    <?php
    /* Value for expression manager javascript (use id) ; no need to submit */
    echo \CHtml::textField($myfname2,$value,array(
        'id' => "answer{$myfname2}",
        'class' => "form-control {$kpclass}",
        'size' => ($inputsize ? $inputsize : null),
        'maxlength' => ($maxlength ? $maxlength : null),
        'data-number' => $isNumber,
        'data-integer' => $isInteger,
    ));
    ?>
    <?php
    /* Value for expression manager is needed here ? Unsure (20170518) */
    echo \CHtml::hiddenField("java{$myfname2}",$value,array(
        'id' => "java{$myfname2}",
        'disabled' => true,
    ));
    ?>
</td>
<!-- end of answer_td -->
