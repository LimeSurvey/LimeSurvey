<?php
/**
 * @var $answer_head_line
 * @var $q_table_id_HTML
 * @var $extraclass
 * @var $num_class
 * @var $totals_class
 * @var $answerwidth
 * @var $cellwidth
 * @var $labelans
 * @var $right_exists
 * @var $showGrandTotal
 *
 */
?>
<!-- answer_head -->
<div class="no-more-tables no-more-tables-array-multi-text">
    <table <?php echo $q_table_id_HTML;?> class="table question subquestion-list questions-list <?php echo $extraclass;?> <?php echo $num_class;?> <?php echo $totals_class;?>">

        <colgroup class="col-responses">
            <col class="answertext" style='width: <?php echo $answerwidth;?>%;'/>

            <?php foreach ($labelans as $i=>$ld):?>
                <col class="<?php // TODO: array2 alternation ?>" style='width: <?php echo $cellwidth;?>%;'/>
            <?php endforeach;?>

            <?php if ($right_exists):?>
                <col class="answertextright <?php // TODO: array2 alternation ?>" style='width: <?php echo $cellwidth;?>%;' />
            <?php endif;?>

            <?php if ($showGrandTotal):?>
                <col class="<?php // TODO: array2 alternation ?>" style='width: <?php echo $cellwidth;?>%;' />
            <?php endif;?>
        </colgroup>

        <thead>
            <tr class="dontread">
                <?php echo $answer_head_line; ?>
            </tr>
        </thead>

        <tbody>
