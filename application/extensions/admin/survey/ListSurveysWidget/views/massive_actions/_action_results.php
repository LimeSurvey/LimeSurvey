<?php
/** @var string $successLabel */

/**
 * This view display the result of delete multiple action. It's rendered via ajax for the confirmation modal in survey list
 *
 * @var $aResults   The array containing the result of each survey deletion
 */
if (!isset($tableLabels)) {
    $tableLabels = array(gT('ID'), gT('Title'), gT('Status'));
}
?>
<?php if(isset($additionalMessage)):?>
    <?php echo $additionalMessage?>
<?php endif;?>
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
                    <?php echo CHtml::encode($result['title']);?>
                </td>
                <?php if ($result['result']):?>
                    <td class="text-success">
                        <?php echo $successLabel ?>
                    </td>
                <?php else: ?>
                    <td class="text-danger">
                        <?php ;
                            if(isset($result['error'])){
                                echo $result['error'] ;
                            }else{
                                eT('Error!');
                            }
                        ; ?>
                    </td>
                <?php endif;?>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
