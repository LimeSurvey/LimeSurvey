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
<tr id="javatbd<?php echo $myfname;?>" class="<?php //TODO: alternation ?> well subquestion-list questions-list array<?php echo $zebra; ?>">
    <th class="answertext">
        <?php if($error): ?>
            <div class="label label-danger" role="alert">
                <?php echo $answertext; ?>
            </div>
        <?php else: ?>
            <?php echo $answertext; ?>
        <?php endif;?>
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
            <td class="answertextright" style='text-align:left; width: <?php $answerwidth; ?>%;' >&nbsp;</td>
        <?php else: ?>
            <td class="answertextright" style='text-align:left; width: <?php $answerwidth; ?>%;' >
                <?php echo $answertext; ?>
            </td>
        <?php endif; ?>
    <?php endif;?>

    <!-- Formated total -->
    <?php echo $formatedRowTotal; ?>
</tr>
<!-- end of answer_row -->
