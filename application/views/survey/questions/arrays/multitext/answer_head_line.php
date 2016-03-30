<?php
/**
 * @var $answerwidth
 * @var $labelans
 * @var $right_exists
 */
?>
<!-- answer_head_line -->
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
