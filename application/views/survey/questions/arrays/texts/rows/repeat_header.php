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
    <tr class="ls-heading ls-heading-repeat hidden-xs" aria-hidden="true">
        <td></td>
        <?php foreach ($labelans as $i=>$ld):?>
            <th>
                <?php echo $ld;?>
            </th>
        <?php endforeach;?>

        <?php if ($right_exists):?>
            <td></td>
        <?php endif;?>

        <?php
            echo $col_head; /* What is it ? */
        ?>
    </tr>
