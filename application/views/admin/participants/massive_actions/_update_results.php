<?php
/**
 * This view display the result of multiple update action. It's rendered via ajax for the confirmation modal
 *
 * @var $aResults   The array containing the result of each surveymenu entry action
 */
?>

<?php if (!$aResults['global']['result']) :?>
    <strong>
        <?php echo $aResults['global']['message'] ;?>
    </strong>
<?php else : ?>
    <?php unset($aResults['global']); ?>
    <?php
    // Inserted after "Save": the alert role makes the screen reader announce the result
    $updatedCount = count(array_filter($aResults, function ($result) {
        return $result['status'] === true;
    }));
    $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => sprintf(ngT('%s participant was updated.|%s participants were updated.', $updatedCount), $updatedCount),
        'type' => 'success',
    ]);
    ?>
    <table class="table table-striped">
        <caption class="massive-action-results-caption visually-hidden"><?php eT('Batch edit the participants'); ?></caption>
        <thead>
            <th><?php eT('Participant ID');?></th>
            <th><?php eT('Status');?></th>
        </thead>
        <tbody>
            <?php foreach ($aResults as $sParticipantToken => $result) :?>
                <tr>
                    <td>
                        <?php echo $sParticipantToken;?>
                    </td>
                    <?php if ($result['status'] === true) :?>
                        <td class="text-success">
                            <?php echo $result['message']; ?>
                        </td>
                    <?php else : ?>
                        <td class="text-danger">
                            <?php echo $result['message']; ?>
                        </td>
                    <?php endif;?>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
<?php endif;?>
