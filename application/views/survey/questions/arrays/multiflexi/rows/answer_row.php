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
<tr id="javatbd<?php echo $myfname;?>" class="<?php echo $coreRowClass;?> <?php echo ($odd) ? "ls-odd" : "ls-even"; ?><?php if($error){ echo " has-error";} ?>" <?php echo $sDisplayStyle;?> role="group" aria-labelledby="answertext<?php echo $myfname;?>">
    <th class="answertext control-label" id="answertext<?php echo $myfname;?>">
        <?php echo $answertext; ?>
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
        <td class="answertextright"><?php echo $answertextright; ?></td>
    <?php endif;?>
</tr>
<!-- end of answer_row -->
