<?php
/**
 * List with comment item Html
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
        <li class="answer-item radio-item <?php if(isset($li_classes)){echo $li_classes;}?>">
            <input
                type="radio"
                name="<?php echo $name; ?>"
                id="<?php echo $id; ?>"
                value="<?php echo $value; ?>"
                class="radio"
                <?php $check_ans; ?>
                onclick="<?php echo $checkconditionFunction; ?>"
            />
            <label for="<?php echo $id; ?>" class="answertext">
                <?php echo $labeltext;?>
            </label>
        </li>
