<?php
/**
 * @var $myfname
 * @var $answertext
 * @var $value
 * @var $error
 * @var $checkconditions
 * @var $options
 * @var $thRight
 * @var $tdRight
 */
?>
<tr id="javatbd<?php echo $myfname;?>" class="question-item answer-item dropdown-item <?php echo ($odd) ? " ls-odd" : " ls-even"; ?><?php echo ($error) ? " ls-error-mandatory has-error" : ""; ?>" >
    <th class="answertext control-label<?php echo ($answerwidth==0)? " sr-only":""; ?>">
        <label for="answer<?php echo $myfname;?>">
            <?php echo $answertext; ?>
        </label>
        <input
            type="hidden"
            name="java<?php echo $myfname; ?>"
            id="java<?php echo $myfname;?>"
            value="<?php echo $value;?>"
        />
    </th>
    <td>
        <select class="form-control" name="<?php echo $myfname; ?>" id="answer<?php echo $myfname; ?>">
            <?php foreach($options as $option):?>
                <option value="<?php echo $option['value'];?>" <?php echo $option['selected'];?>>
                    <?php echo $option['text'];?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <?php if ($right_exists): ?>
        <th class='answertextright information-item'><?php echo $answertextright; ?></th>
    <?php endif; ?>
</tr>
