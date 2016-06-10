<?php
/**
 * Generate a row for the table
 *
 * @var $answer_tds      : the cells of each row, generated with the view rows/cells/*.php
 * @var $sDisplayStyle
 * @var $useAnswerWidth
 * @var $answerwidth
 * @var $myfname
 * @var $error
 * @var $row_value
 * @var $answertext
 * @var $answertextright
 * @var $rightTd
 */
?>

<!-- answer_row -->
<tr id="javatbd<?php echo $myfname;?>" class="<?php //TODO: alternation ?> well subquestion-list questions-list array<?php echo $zebra; ?> <?php echo $sDisplayStyle;?>">
    <?php if ($useAnswerWidth): ?>
        <th class="answertext" style='width:<?php echo $answerwidth;?>%;' >
    <?php else: ?>
        <th class="answertext col-xs-12 col-sm-6">
    <?php endif;?>


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
            value="<?php echo $row_value;?>"
        />
    </th>

    <?php
        // Defined in answer_td view
        echo $answer_tds;
    ?>

    <!-- right -->
    <?php if($rightTd): ?>
        <td class="answertextright" style='text-align:left; width: <?php echo $answerwidth; ?>'>
            <?php echo $answertextright; ?>
        </td>
    <?php endif;?>
</tr>
<!-- end of answer_row -->
