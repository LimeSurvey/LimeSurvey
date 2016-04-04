<?php
/**
 * array array, dropdown, Html
 *
 * @var $sRows      : the rows, generated with the view rows/answer_row.php
 *
 * @var $extraclass

 *
 */
?>
<!-- Array -->

<!-- answer -->
<div class="no-more-tables no-more-tables-array-dropdown">
    <table class="table table-condensed question subquestion-list questions-list  <?php echo $extraclass; ?>">
        <tbody>
            <?php
                // rows/answer_row.php
                echo $sRows;
            ?>
        </tbody>
    </table>
</div>
<!-- end of answer -->
