<?php
/** @var string $successLabel */

/**
 * This view display the result of delete multiple action. It's rendered via ajax for the confirmation modal in survey list
 *
 * it's also used for saving "change survey group" result (not only delete multiple action) ...
 *
 * @var $aResults   The array containing the result of each survey deletion
 */
?>
<table class="table table-striped">
    <thead>
        <th><?php eT('Survey ID');?></th>
        <th><?php eT('Survey title');?></th>
        <th><?php eT('Status');?></th>
    </thead>
    <tbody>
        <?php foreach($aResults as $iSid => $result):?>
            <tr>
                <td>
                    <?php echo $iSid;?>
                </td>
                <td>
                    <?php echo CHtml::encode($result['title']);?>
                </td>
                <?php if ($result['result']):?>
                    <td class="text-success">
                        <?php echo $successLabel ?>
                    </td>
                <?php else: ?>
                    <td class="text-warning">
                        <?php eT('Error'); ?>
                    </td>
                <?php endif;?>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
