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
<table class="table question subquestion-list questions-list  <?php echo $extraclass; ?>">
    <tbody>
        <?php
            // rows/answer_row.php
            echo $sRows;
        ?>
    </tbody>
</table>

<!-- end of answer -->
