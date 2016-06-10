<?php
/**
 * array 5 point choice Html : Header
 *
 * @var $sColumns   : the columns, generated with the view columns/col.php
 * @var $sHeaders   : the headers, generated with the view rows/cell/thead.php
 * @var $sRows      : the rows, generated with the view rows/answer_row.php
 * @var $extraclass
 */
?>
<!-- Array 5 point choice -->

<!-- answer -->
<div class="no-more-tables no-more-tables-5-point">
    <table class="table question table-5-point-array subquestion-list questions-list <?php echo $extraclass; ?>">

        <!-- Columns -->
        <colgroup class="col-responses">
            <col class="col-answers" />

            <?php
                // columns/col.php
                echo $sColumns;
            ?>
        </colgroup>

        <!-- Table headers -->
        <thead>
            <tr class="array1 dontread">
                <th>&nbsp;</th>

                <?php
                    // rows/cell/thead.php
                    echo $sHeaders;
                ?>
            </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
            <?php
                // rows/answer_row.php
                echo $sRows;
            ?>
        </tbody>
    </table>
</div>
<!-- end of answer -->
