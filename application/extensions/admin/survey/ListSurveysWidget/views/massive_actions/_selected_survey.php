<?php
/**
 * This view display the result of selected surveys. It's rendered via ajax for the confirmation modal in survey list
 *
 * @var $aResults   The array containing the result of each survey selection
 */
?>
<hr>
<table class="table table-striped">
    <thead>
        <th><?php eT('Survey ID');?></th>
        <th><?php eT('Title');?></th>
        <th><?php eT('Status');?></th>
    </thead>
    
    <tbody>
        <?php foreach($aResults as $iSid => $result):?>
            <tr>
                <td>
                    <?php echo $iSid;?>
                </td>
                <td>
                    <?php echo $result['title'];?>
                </td>
                <?php if ($result['result']):?>
                    <td class="text-success">
                        <?php eT('Selected'); ?>
                    </td>
                <?php else: ?>
                    <td class="text-warning">
                        <?php echo $result['error'] ; ?>
                    </td>
                <?php endif;?>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>