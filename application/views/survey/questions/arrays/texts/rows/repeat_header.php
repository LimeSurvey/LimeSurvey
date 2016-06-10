<?php
/**
 * This view is used to repeat the headers of the table (question advanced attribute)
 *
 * @var $answerwidth
 * @var $labelans
 * @var $right_exists
 */
?>
</tbody>

<tbody>
    <tr class="dontread repeat headings hidden-xs">
        <td style='width: <?php echo $answerwidth;?>%;'>
            &nbsp;
        </td>
        <?php foreach ($labelans as $i=>$ld):?>
            <th class="answertext">
                <?php echo $ld;?>
            </th>
        <?php endforeach;?>

        <?php if ($right_exists):?>
            <td>&nbsp;</td>
        <?php endif;?>

        <?php
            echo $col_head;
        ?>
    </tr>
