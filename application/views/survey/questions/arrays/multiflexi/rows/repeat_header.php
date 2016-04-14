<?php
/**
 * This view is used to repeat the headers of the table (question advanced attribute)
 *
 * @var $labelans
 * @var $right_exists
 * @var $cellwidth
 * @var $answerwidth
 * @var $textAlignment
 */
?>

<!-- repeat_header -->
</tbody>

<tbody>
    <tr class="dontread repeat headings hidden-xs">
        <th>&nbsp;</th>
        <?php foreach ($labelans as $ld): ?>
            <th  class='th-11  text-<?php echo $textAlignment;?>'>
                <?php echo $ld;?>
            </th>
        <?php endforeach;?>

        <?php if ($right_exists):?>
            <th>
                &nbsp;
            </th>
        <?php endif;?>
    </tr>
<!-- end of repeat header -->
