<?php
/**
 * yesnouncertain Html
 *
 * @var $sColumns   : the columns, generated with the view columns/col.php
 * @var $sHeaders   : the headers, generated with the view rows/cell/thead.php
 * @var $sRows      : the rows, generated with the view rows/answer_row.php
 * @var $anscount
 * @var $extraclass
 * @var $answerwidth
 *
 */
?>
<!-- Yes/No/Uncertain-->
<!-- answer -->
<table class="<?php echo $coreClass; ?> table table-bordered table-hover" role="group" aria-labelledby="ls-question-text-<?php echo $basename ?>">
    <colgroup class="col-responses">
        <col class="col-answers"  style='width: <?php echo $answerwidth; ?>%;' />
        <?php
            // columns/col.php
            echo $sColumns;
        ?>
    </colgroup>
    <thead aria-hidden="true">
        <tr class="ls-heading">
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
<!-- end of answer -->
