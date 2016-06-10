<?php
/**
 * Ranking question, item Html
 * @var $value
 * @var $selected
 * @var $classes
 * @var $id
 * @var $optiontext
 */
?>

<li class="select-item">
    <label for="answer<?php echo $myfname;?>">
        <?php echo $labeltext;?>
    </label>

    <select  class='form-control' name="<?php echo $myfname;?>" id="answer<?php echo $myfname;?>">
        <?php foreach($options as $option): ?>
            <option value="<?php echo $option['value'];?>" <?php echo $option['selected'];?> class='<?php echo $option['classes']?>'>
                <?php echo $option['optiontext'];?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Hidden form: maybe can be replaced with ranking.js -->
    <input type="hidden" id="java<?php echo $myfname;?>" disabled="disabled" value="<?php echo $thisvalue; ?>"/>
</li>
