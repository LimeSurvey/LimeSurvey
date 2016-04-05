<?php
/**
 * Multiple Choice Html : item row
 *
 * @var $name
 * @var $code
 * @var $answer
 * @var $checkedState
 * @var $myfname
 */
?>

<!-- answer_row -->
<div id='javatbd<?php echo $myfname; ?>' class='form-group answer-item radio-item' <?php echo $sDisplayStyle; ?> >
    <input
        class="radio"
        type="radio"
        value="<?php echo $code; ?>"
        name="<?php echo $name; ?>"
        id="answer<?php echo $name.$code; ?>"
        <?php echo $checkedState;?>
        onclick="if (document.getElementById('answer<?php echo $name; ?>othertext') != null) document.getElementById('answer<?php echo $name; ?>othertext').value='';checkconditions(this.value, this.name, this.type)"
     />
    <label for="answer<?php echo $name.$code; ?>" class="control-label radio-label">
         <?php echo $answer; ?>
    </label>
</div>
<!-- end of answer_row -->
