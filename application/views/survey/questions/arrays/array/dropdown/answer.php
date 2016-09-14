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
    <colgroup class="col-responses">
        <col class="col-answers" style='width: <?php echo $answerwidth; ?>%;' />
        <col class="odd" style='width: <?php echo $columnswidth; ?>%;' />
        <?php if($right_exists): ?>
            <col class="col-answersright" style='width: <?php echo $answerwidth; ?>%;' />
        <?php endif; ?>
    </colgroup>
        <tbody>
            <?php
                // rows/answer_row.php
                echo $sRows;
            ?>
        </tbody>
    </table>
</div>
<!-- end of answer -->
