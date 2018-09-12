<?php
/**
 * Global container for the answer
 *
 * @var $q_table_id_HTML
 * @var $classes
 * @var $extraclass
 * @var $num_class
 * @var $totals_class
 * @var $answerwidth
 * @var $cellwidth
 * @var $labelans
 * @var $right_exists
 * @var $showGrandTotal
 * @var $showtotals,
 * @var $row_head,
 * @var $total,
 * @var $q_table_id
 * @var $radix
 * @var $name
 * @var $sRows
 */
?>

<!-- Multi Text -->

<!-- answer -->
<div class="no-more-tables no-more-tables-array-multi-text">
    <table <?php echo $q_table_id_HTML;?> class="table question subquestion-list questions-list <?php echo $extraclass;?> <?php echo $num_class;?> <?php echo $totals_class;?>">

        <colgroup class="col-responses">

            <!-- Column for answer label -->
            <col class="answertext" style='width: <?php echo $answerwidth;?>%;'/>

            <!-- columns for answers -->
            <?php foreach ($labelans as $i=>$ld):?>
                <col class="<?php // TODO: array2 alternation ?> <?php //echo $classes; ?>" style='width: <?php echo $cellwidth; ?>%;' />
            <?php endforeach;?>

            <!-- columns for right -->
            <?php if ($right_exists):?>
                <col class="answertextright <?php // TODO: array2 alternation ?>" style='width: <?php echo $cellwidth;?>%;' />
            <?php endif;?>

            <!-- columns for Grand Total -->
            <?php if ($showGrandTotal):?>
                <col class="<?php // TODO: array2 alternation ?>" style='width: <?php echo $cellwidth;?>%;' />
            <?php endif;?>
        </colgroup>

        <thead>
            <tr class="dontread">
                <td style='width: <?php echo $answerwidth;?>%;'>
                    &nbsp;
                </td>
                <?php foreach ($labelans as $i=>$ld):?>
                    <th class="answertext">
                        <?php echo $ld;?>
                    </th>
                <?php endforeach;?>

                <?php if ($right_exists):?>
                    <td>&nbsp;</td>
                <?php endif;?>

                <?php
                    echo $col_head;
                ?>
            </tr>
        </thead>

        <tbody>

            <?php
                // Defined in answer_row view
                echo $sRows;
            ?>

            <?php if($showtotals):?>
                <tr class="total">
                    <?php echo $row_head; ?>
                    <?php echo $total; ?>
                </tr>
            <?php endif;?>
        </tbody>
    </table>
</div>

<?php if(empty($q_table_id)): ?>
    <script type="text/javascript">
    <!--
        $('#question<?php echo $name;?> .question').delegate('input[type=text]:visible:enabled','blur keyup',function(event){
            <?php echo $checkconditionFunction;?>($(this).val(), $(this).attr('name'), 'text');
            return true;
        })
    // -->
    </script>
<?php endif;?>
<!-- end of answer -->
