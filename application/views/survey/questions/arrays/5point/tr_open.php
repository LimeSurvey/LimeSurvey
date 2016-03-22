<?php
/**
 * @var $myfname
 * @var $answerwidth
 * @var $answertext
 * @var $value
 */
?>
<tr id="javatbd<?php echo $myfname;?>" class="well answers-list radio-list"  <?php echo $sDisplayStyle; ?>>
    <th class="answertext" style="width: <?php echo $answerwidth;?>%;">
        <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $answertext;?>
            </div>
        <?php else: ?>
            <?php echo $answertext;?>
        <?php endif;?>
        <input name="java<?php echo $myfname;?>" id="java<?php echo $myfname;?>" value="<?php echo $value;?>" type="hidden">
    </th>
