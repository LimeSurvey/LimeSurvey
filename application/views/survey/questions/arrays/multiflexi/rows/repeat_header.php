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
    <tr class="ls-heading ls-repeat-heading hidden-xs" aria-hidden="true">
        <th>&nbsp;</th>
        <?php foreach ($labelans as $ld): ?>
            <th>
                <?php echo $ld;?>
            </th>
        <?php endforeach;?>

        <?php if ($right_exists):?>
            <td>
                &nbsp;
            </td>
        <?php endif;?>
    </tr>
<!-- end of repeat header -->
