<?php
/**
 * @var $myfname
 * @var $answertext
 * @var $value
 */
?>
<tr id="javatbd<?php echo $myfname;?>" class="well answers-list radio-list array<?php echo $zebra; ?>">
    <th class="answertext">
        <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $answertext; ?>
            </div>
        <?php else:?>
            <?php echo $answertext; ?>
        <?php endif; ?>

        <input
            type="hidden"
            name="java<?php echo $myfname; ?>"
            id="java<?php echo $myfname;?>"
            value="<?php echo $value;?>"
        />
    </th>
