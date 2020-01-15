<?php
/**
 * This view display the result of selected items. It's rendered via ajax for the confirmation modal of Massive Widget Modal
 *
 * @var $aResults   The array containing the result of each items selection
 */
if (!isset($tableLabels)) {
    $tableLabels = array('ID','Title','Status');
}

?>
<hr>
<table class="table table-striped">
    <thead>
        <?php foreach($tableLabels as $label):?>
        <th><?php echo $label?></th>
        <?php endforeach;?>
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

