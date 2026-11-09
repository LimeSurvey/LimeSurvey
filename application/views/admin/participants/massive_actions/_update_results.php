<?php
/**
 * This view display the result of multiple update action. It's rendered via ajax for the confirmation modal
 *
 * @var array $aResults     The array containing the result of each participant update
 * @var int   $updatedCount Number of participants that were updated
 * @var int   $failedCount  Number of participants that could not be updated
 */
?>

<?php if (!$aResults['global']['result']) :?>
    <?php
    $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => CHtml::encode($aResults['global']['message']),
        'type' => 'warning',
    ]);
    ?>
<?php else : ?>
    <?php unset($aResults['global']); ?>
    <?php
    // Inserted after "Save": The alert role makes the screen reader announce the result
    if ($updatedCount > 0 || $failedCount === 0) {
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => sprintf(gT('%s participants have been updated'), $updatedCount),
            'type' => 'success',
        ]);
    }
    if ($failedCount > 0) {
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => sprintf(gT('%s participants could not be updated'), $failedCount),
            'type' => 'danger',
        ]);
    }
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
                        <?php echo CHtml::encode($sParticipantToken);?>
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
