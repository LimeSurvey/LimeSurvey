<?php
/**
 * This view is used to repeat the headers of the table (question advanced attribute)
 *
 * @var $labelans
 * @var $right_exists
 * @var $cellwidth
 * @var $answerwidth
 */
?>

<!-- repeat_header -->
</tbody>

<tbody>
    <tr class="dontread repeat headings hidden-xs">
        <?php foreach ($labelans as $i=>$ld):?>
            <col class="<?php // TODO: array2 alternation ?>" style='width: <?php echo $cellwidth;?>%;'/>
        <?php endforeach;?>

        <?php if ($right_exists):?>
            <col class="answertextright <?php // TODO: array2 alternation ?>" style='width: <?php echo $answerwidth;?>%;' />
        <?php endif;?>
    </tr>
<!-- end of repeat header -->
