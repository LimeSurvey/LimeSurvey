<?php
/**
 * Generate a row for the table
 *
 * @var $answer_tds        : the cells of each row, generated with the view rows/cells/*.php
 * @var $myfname
 * @var $answerwidth
 * @var $answertext
 * @var $value
 */
?>

<!-- answer_row -->
<tr id="javatbd<?php echo $myfname;?>" class="well answers-list radio-list array<?php echo $zebra; ?>"  <?php echo $sDisplayStyle; ?>>
    <th class="answertext <?php echo $textAlignClass;?>" style="width: <?php echo $answerwidth;?>%;">
        <?php if($error): ?>
            <div class="label label-danger" role="alert">
                <?php echo $answertext;?>
            </div>
        <?php else: ?>
            <?php echo $answertext;?>
        <?php endif;?>
        <input name="java<?php echo $myfname;?>" id="java<?php echo $myfname;?>" value="<?php echo $value;?>" type="hidden">
    </th>

    <?php
        // defined in rows/cells/*
        echo $answer_tds;
    ?>

</tr>
<!-- end of answer_row -->
