<?php
/**
 * This view display the result of delete multiple action. It's rendered via ajax for the confirmation modal in survey list
 *
 * @var $aResults   The array containing the result of each survey deletion
 */
?>
<table class="table table-striped">
    <thead>
        <th><?php eT('Question ID');?></th>
        <th><?php eT('Question title');?></th>
        <th><?php eT('Status');?></th>
    </thead>
    <tbody>
        <?php foreach($aResults as $iQid => $result):?>
            <tr>
                <td>
                    <?php echo $iQid;?>
                </td>
                <td>
                    <?php echo $result['question'];?>
                </td>
                <?php if ($result['result']['status'] === true):?>
                    <td class="text-success">
                        <?php echo $result['result']['message']; ?>
                    </td>
                <?php else: ?>
                    <td class="text-danger">
                        <?php echo $result['result']['message']; ?>
                    </td>
                <?php endif;?>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
