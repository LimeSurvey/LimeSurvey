<?php
/**
 * Generate a row for the table
 *
 * @var $answer_tds      : the cells of each row, generated with the view rows/cells/*.php
 * @var $myfname
 * @var $answertext
 * @var $value
 */
tracevar($answerwidth);
?>

<!-- answer_row -->
<tr id="javatbd<?php echo $myfname;?>" class="answers-list radio-list <?php echo ($odd) ? " ls-odd" : " ls-even"; ?><?php echo ($error) ? " ls-error-mandatory has-error" : ""; ?>" role="radiogroup"  aria-labelledby="answertext<?php echo $myfname;?>">
    <th id="answertext<?php echo $myfname;?>" class="answertext control-label<?php echo ($answerwidth==0)? " sr-only":""; ?>">
        <?php echo $answertext;?>
        <input name="java<?php echo $myfname;?>" id="java<?php echo $myfname;?>" value="<?php echo $value;?>" type="hidden">
    </th>
    <?php
        // Defined in answer_td view
        echo $answer_tds;
    ?>
    <?php if ($right_exists): ?>
        <th class='answertextright information-item<?php echo ($answerwidth==0)? " sr-only":""; ?>'><?php echo $answertextright; ?></th>
    <?php endif; ?>

    <?php
        // No answer should come after right text at bipolar question
        echo $no_answer_td;
    ?>

</tr>
<!-- end of answer_row -->
