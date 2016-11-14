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
    <tr class="ls-heading ls-repeat-heading hidden-xs" aria-hidden="true">
        <th>
            &nbsp;
        </th>
        <?php foreach ($labelans as $i=>$ld):?>
            <td>
                <?php echo $ld;?>
            </td>
        <?php endforeach;?>

        <?php if ($right_exists):?>
            <td>&nbsp;</td>
        <?php endif;?>

        <?php
            echo $col_head;
        ?>
    </tr>
