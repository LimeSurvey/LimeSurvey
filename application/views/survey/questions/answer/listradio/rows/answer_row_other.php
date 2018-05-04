<?php
/**
 * List radio Html : item 'other' row
 *
 * @var $name
 * @var $answer_other
 * @var $sDisplayStyle
 * @var $sDisable
 * @var $myfname
 * @var $othertext
 * @var $checkedState
 * @var $kpclass
 * @var $sValue
 * @var $oth_checkconditionFunction
 * @var $checkconditionFunction
 */
?>

<!-- answer_row_other -->
<li id='javatbd<?php echo $myfname; ?>' class='form-group answer-item radio-text-item form-inline' <?php echo $sDisplayStyle; ?> >
    <!-- Checkbox + label -->
    <div class="form-group othertext-label-checkox-container radio-item">
        <input
        type="radio"
        value="-oth-"
        name="<?php echo $name; ?>"
        id="SOTH<?php echo $name;?>"
        <?php echo $checkedState;?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
        />

        <label for="SOTH<?php echo $name; ?>" class="control-label label-radio" id="label-id-<?php echo $name; ?>"><?php echo LimeExpressionManager::ProcessStepString($othertext); ?></label>
    </div>

    <!-- comment -->
    <div class="form-group text-item other-text-item">
        <input
        type="text"
        class="form-control <?php echo $kpclass; ?> input-sm"
        id="answer<?php echo $name; ?>othertext"
        name="<?php echo $name; ?>other"
        title="<?php eT('Other'); ?>"
        <?php echo $answer_other;?>
        onkeyup="if($.trim($(this).val())!=''){ $('#SOTH<?php echo $name; ?>').click(); };"
        aria-labelledby="label-id-<?php echo $name; ?>"
        />
    </div>
</li>
<!-- end of answer_row_other -->
