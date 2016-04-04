<?php
/**
 * List with comment, list layout, Html
 *
 * @var $name                           $ia[1]
 * @var $id                             answer'.$ia[1].$ansrow['code'].'
 * @var $value                          $ansrow['code']
 * @var $check_ans
 * @var $checkconditionFunction         $checkconditionFunction.'(this.value, this.name, this.type)
 * @var $labeltext                      $ansrow['answer']
 * @var $li_classes
 */
?>
<!-- answer_row -->
<div class="answer-item radio-item <?php if(isset($li_classes)){echo $li_classes;}?>">
    <div class='form-group'>
        <input
            type="radio"
            name="<?php echo $name; ?>"
            id="<?php echo $id; ?>"
            value="<?php echo $value; ?>"
            class="radio"
            <?php echo $check_ans; ?>
            onclick="<?php echo $checkconditionFunction; ?>"
        />
        <label for="<?php echo $id; ?>" class="answertext radio-label control-label">
            <?php echo $labeltext;?>
        </label>
    </div>
</div>
<!-- end of answer_row -->
