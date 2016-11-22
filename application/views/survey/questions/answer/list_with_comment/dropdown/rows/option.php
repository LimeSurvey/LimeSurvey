<?php
/**
 * List with comment, dropdown style, item Html
 * @var $value          $ansrow['code']
 * @var $check_ans      $check_ans
 * @var $option_text    $ansrow['answer']
 * @var $classes
 */
?>
    <option class="<?php if(isset($classes)){echo $classes;}?>" value="<?php echo $value;?>" <?php echo $check_ans;?>>
        <?php echo $option_text;?>
    </option>
