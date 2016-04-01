<?php
/**
 * array 5 point choice Html : Header
 *
 * @var $sColumns   : the columns, generated with the view columns/col.php
 * @var $sHeaders   : the headers, generated with the view rows/cell/thead.php
 * @var $extraclass
 */
?>
<!-- Array 5 point choice -->

<!-- header -->
<div class="no-more-tables no-more-tables-5-point">
    <table class="table question subquestion-list questions-list <?php echo $extraclass; ?>">

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
                <!-- close_table_head -->
                    </tr>
        </thead>

        <!-- Table Body -->
        <tbody>

            <?php
                echo $sRows;
            ?>

        </tbody>
    </table>
</div>

<!-- end of header -->
