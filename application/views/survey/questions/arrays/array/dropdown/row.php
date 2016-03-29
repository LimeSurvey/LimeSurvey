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
<tr id="javatbd<?php echo $myfname;?>" class="well question-item answer-item dropdown-item">
    <th class="answertext" style="padding: 1em;">
        <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $answertext; ?>
            </div>
        <?php else:?>
            <label for="answer<?php echo $myfname;?>">
                <?php echo $answertext; ?>
            </label>
        <?php endif; ?>
        <input
            type="hidden"
            name="java<?php echo $myfname; ?>"
            id="java<?php echo $myfname;?>"
            value="<?php echo $value;?>"
        />
    </th>
    <td>
        <select class="form-control" name="<?php echo $myfname; ?>" id="answer<?php echo $myfname; ?>" onchange="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type);">
            <?php foreach($options as $option):?>
                <option value="<?php echo $option['value'];?>" <?php echo $option['selected'];?>>
                    <?php echo $option['text'];?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <?php if($thRight):?>
        <th class="answertextright">
            <?php echo $answertext;?>
        </th>
    <?php endif; ?>

    <?php if($tdRight):?>
        <td class="answertextright">&nbsp;</td>
    <?php endif;?>
</tr>
