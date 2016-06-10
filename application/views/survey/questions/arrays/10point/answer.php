<?php
/**
 * array 10 point choice Html
 *
 * @var $sColumns   : the columns, generated with the view columns/col.php
 * @var $sHeaders   : the headers, generated with the view rows/cell/thead.php
 * @var $sRows      : the rows, generated with the view rows/answer_row.php
 * @var $extraclass
 * @var $answerwidth
 */
?>
<!-- Array 10 point choice -->

<!-- answer -->
<div class="no-more-tables no-more-tables-10-point">
    <table class="table question table-10-point-array subquestion-list questions-list <?php echo $extraclass; ?>">
        <colgroup class="col-responses">
            <col class="col-answers" style='width: <?php echo $answerwidth; ?>%;'/>
            <?php
                // columns/col.php
                echo $sColumns;
            ?>
        </colgroup>
        <thead>
            <tr class="array1 dontread">
                <th>&nbsp;</th>

                <?php
                    // rows/cell/thead.php
                    echo $sHeaders;
                ?>

            </tr>
        </thead>
        <tbody>
            <?php
                // rows/answer_row.php
                echo $sRows;
            ?>
        </tbody>
    </table>
</div>
<!-- end of answer -->
