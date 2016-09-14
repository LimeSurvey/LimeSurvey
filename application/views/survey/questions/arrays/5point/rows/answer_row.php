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
<tr id="javatbd<?php echo $myfname;?>" class="answers-list radio-list form-group array<?php echo $zebra; ?><?php if($error){ echo " has-error";} ?>"  <?php echo $sDisplayStyle; ?>  role="radiogroup"  aria-labelledby="answertext<?php echo $myfname;?>">
    <th id="answertext<?php echo $myfname;?>"class="answertext control-label">
        <?php echo $answertext;?>
        <input name="java<?php echo $myfname;?>" id="java<?php echo $myfname;?>" value="<?php echo $value;?>" type="hidden">
    </th>
    <?php
        // defined in rows/cells/*
        echo $answer_tds;
    ?>
</tr>
<!-- end of answer_row -->
