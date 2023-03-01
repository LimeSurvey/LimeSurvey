<?php
/**
 * This view display the result of multiple update action. It's rendered via ajax for the confirmation modal
 *
 * @var $aResults   The array containing the result of each survey deletion
 */
?>

<?php if (!$aResults['global']['result']):?>
    <strong>
        <?php echo $aResults['global']['message'] ;?>
    </strong>
<?php else: ?>
    <?php unset($aResults['global']); ?>
    <table class="table table-striped">
        <thead>
            <th><?php eT('Participant ID');?></th>
            <th><?php eT('Status');?></th>
        </thead>
        <tbody>
            <?php foreach($aResults as $iTokenId => $result):?>
                <tr>
                    <td>
                        <?php echo $iTokenId;?>
                    </td>
                    <?php if ($result['status'] === true):?>
                        <td class="text-success">
                            <?php echo $result['message']; ?>
                        </td>
                    <?php else: ?>
                        <td class="text-danger">
                            <?php echo $result['message']; ?>
                        </td>
                    <?php endif;?>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
<?php endif;?>
