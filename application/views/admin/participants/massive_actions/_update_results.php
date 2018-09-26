<?php
/**
 * This view display the result of multiple update action. It's rendered via ajax for the confirmation modal
 *
 * @var $aResults   The array containing the result of each surveymenu entry action
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
            <th><?php eT('Survey menu entry ID');?></th>
            <th><?php eT('Status');?></th>
        </thead>
        <tbody>
            <?php foreach($aResults as $sParticipantToken => $result):?>
                <tr>
                    <td>
                        <?php echo $sParticipantToken;?>
                    </td>
                    <?php if ($result['status'] === true):?>
                        <td class="text-success">
                            <?php echo $result['message']; ?>
                        </td>
                    <?php else: ?>
                        <td class="text-warning">
                            <?php echo $result['message']; ?>
                        </td>
                    <?php endif;?>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
<?php endif;?>
