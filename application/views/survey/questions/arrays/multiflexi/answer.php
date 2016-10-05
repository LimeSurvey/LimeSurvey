<?php
/**
 * Global container for the answer
 *
 * @var $sRows           : the rows, generated with the view rows/answer_row.php
 * @var $answertypeclass
 * @var $extraclass
 * @var $answerwidth
 * @var $labelans
 * @var $cellwidth
 * @var $right_exists
 * @var $textAlignment
 *
 */
?>
<!-- answer -->
<table class="table question subquestion-list questions-list table-bordered <?php echo $answertypeclass; ?>-list <?php echo $extraclass; ?>">

    <colgroup class="col-responses">
        <col class="answertext" style='width: <?php echo $answerwidth;?>%;'/>

        <?php foreach ($labelans as $i=>$ld):?>
            <col class="<?php // TODO: array2 alternation ?>" style='width: <?php echo $cellwidth;?>%;'/>
        <?php endforeach;?>

        <?php if ($right_exists):?>
            <col class="answertextright <?php // TODO: array2 alternation ?>" style='width: <?php echo $answerwidth;?>%;' />
        <?php endif;?>
    </colgroup>

    <thead>
        <tr aria-hidden="true">
            <td>&nbsp;</td>

            <?php foreach ($labelans as $ld): ?>
                <th>
                    <?php echo $ld;?>
                </th>
            <?php endforeach;?>

            <?php if ($right_exists):?>
                <th>
                    &nbsp;
                </th>
            <?php endif;?>
        </tr>
    </thead>

    <tbody>
        <?php
            // rows/answer_row.php
            echo $sAnswerRows;
        ?>
    </tbody>
</table>
<!-- end of answer -->
