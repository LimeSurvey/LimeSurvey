<?php
/**
 * Multiple Choice Html : item row
 *
 * @var $ia
 * @var $ansrow
 * @var $nbColLabelXs
 * @var $nbColLabelLg
 * @var $nbColInputLg
 * @var $nbColInputXs
 * @var $checkedState
 * @var $myfname
 */
?>
<div class="col-sm-12">
    <div id='javatbd<?php echo $myfname; ?>' class='form-group answer-item radio-item' <?php echo $sDisplayStyle; ?> >
        <label for="answer<?php echo $ia[1].$ansrow['code']; ?>" class="control-label label-radio">
            <input
                class="radio"
                type="radio"
                value="<?php echo $ansrow['code']; ?>"
                name="<?php echo $ia[1]; ?>"
                id="answer<?php echo $ia[1].$ansrow['code']; ?>"
                <?php echo $checkedState;?>
                onclick="if (document.getElementById('answer<?php echo $ia[1]; ?>othertext') != null) document.getElementById('answer<?php echo $ia[1]; ?>othertext').value='';checkconditions(this.value, this.name, this.type)"
             />
             <span>
                 <?php echo $ansrow['answer']; ?>
            </span>
        </label>
    </div>
</div>
