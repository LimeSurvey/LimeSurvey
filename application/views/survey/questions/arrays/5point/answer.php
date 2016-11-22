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
<table class="<?php echo $coreClass; ?> table table-bordered table-hover table-5-point-array" role="group" aria-labelledby="ls-question-text-<?php echo $basename ?>">
    <!-- Columns -->
    <colgroup class="col-responses">
        <col class="col-answers" style='width: <?php echo $answerwidth; ?>%;' />
        <?php
            // columns/col.php
            echo $sColumns;
        ?>
    </colgroup>
    <!-- Table headers -->
    <thead aria-hidden="true">
        <tr class="ls-heading"><!-- same class for repeat heading too -->
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

<!-- end of answer -->
