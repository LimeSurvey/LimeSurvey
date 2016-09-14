<?php
/**
 * Generate a row for the table
 *
 * @var $answer_tds  : the cells of each row, generated with the view rows/cells/*.php
 * @var $myfname
 * @var $error
 * @var $answertext
 * @var $value
 * @var $rightTd
 * @var $rightTdEmpty
 * @var answerwidth
 * @var $formatedRowTotal
 */
?>

<!-- answer_row -->
<tr id="javatbd<?php echo $myfname;?>" class="subquestion-list questions-list array<?php echo $zebra; ?><?php if($error){ echo " has-error";} ?>" role="radiogroup" aria-labelledby="answertext<?php echo $myfname;?>">
    <th id="answertext<?php echo $myfname;?>" class="answertext control-label">
        <?php echo $answertext; ?>
        <input
            type="hidden"
            name="java<?php echo $myfname;?>"
            id="java<?php echo $myfname;?>"
            value="<?php echo $value;?>"
        />
    </th>

    <!-- all cells for this row -->
    <?php echo $answer_tds;?>

    <!-- Total -->
    <?php if($rightTd): ?>
        <?php if($rightTdEmpty): ?>
            <td class="answertextright">&nbsp;</td>
        <?php else: ?>
            <td class="answertextright">
                <?php echo $answertext; ?>
            </td>
        <?php endif; ?>
    <?php endif;?>

    <!-- Formated total -->
    <?php echo $formatedRowTotal; ?>
</tr>
<!-- end of answer_row -->
