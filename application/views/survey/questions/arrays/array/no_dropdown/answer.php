<?php
/**
 * array array, no dropdown, Html
 *
 * @var $sColumns   : the columns, generated with the view columns/col.php
 * @var $sHeaders   : the headers, generated with the view rows/cell/thead.php
 * @var $sRows      : the rows, generated with the view rows/answer_row.php
 *
 * @var $extraclass

 *
 */
?>
<!-- Array -->

<!-- answer -->
<div class="no-more-tables no-more-tables-array-no-dropdown">
    <table class="array-no-dropdown table table-condensed question subquestion-list questions-list <?php echo $extraclass; ?>">
        <colgroup class="col-responses">
            <col class="col-answers"  style='width: <?php echo $answerwidth; ?>%;' />
            <?php
                // columns/col.php
                echo $sColumns;
            ?>
        </colgroup>
        <thead>
            <tr class="array1 dontread">
                <?php
                    // rows/cell/thead.php
                    echo $sHeaders;
                ?>
            </tr>
        </thead>
        <tbody>

            <?php if($anscount==0):?>
                <tr>
                    <th class="answertext">
                        <?php eT('Error: This question has no answers.');?>
                    </th>
                </tr>
            <?php endif; ?>

            <?php
                // rows/answer_row.php
                echo $sRows;
            ?>
        </tbody>
    </table>
</div>
<!-- end of answer -->
