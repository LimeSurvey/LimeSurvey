<?php

/**
 * @since 2017-09-20
 * @author Olle Härstedt
 */

?>

<label role="heading" aria-level="2"><?php eT('Overview'); ?></label>
<table class='table table-striped table-bordered'>
    <tfoot>
        <tr>
            <td style='width: 70%;'><?php eT('Total storage'); ?></td>
            <td><?php echo $totalStorage; ?></td>
        </tr>
    </tfoot>
    <tbody>
        <tr>
            <td><?php eT('Survey storage'); ?></td>
            <td><?php echo $surveySize; ?></td>
        </tr>
        <tr>
            <td><?php eT('Themes storage'); ?></td>
            <td><?php echo $templateSize; ?></td>
        </tr>
        <tr>
            <td><?php eT('Label set storage'); ?></td>
            <td><?php echo $labelSize; ?></td>
        </tr>
    </tbody>
</table>


<?php if ($surveys) : ?>
    <label><?php eT('Survey storage'); ?></label>
    <table class='table table-striped table-bordered'>
        <?php foreach ($surveys as $survey) : ?>
        <tr>
            <td style='width: 70%;'>
                <?php echo $survey['name']; ?>
                <?php if ($survey['deleted']) : ?>
                    (<?php echo $survey['sid']; ?>)
                <?php else : ?>
                    (<a href="<?php echo $this->createUrl('surveyAdministration/view', array('surveyid' => $survey['sid'])); ?>"><?php echo $survey['sid']; ?></a>)
                <?php endif; ?>
            </td>
            <td>
                <?php echo $survey['size']; ?>
                <?php if ($survey['showPurgeButton']) : ?>
                    <span
                        class='ri-delete-bin-fill text-danger float-end btn btn-outline-secondary btn-xs'
                        data-bs-toggle='tooltip'
                        onclick='window.location = "<?php echo $this->createUrl('surveyAdministration/purge', array('purge_sid' => $survey['sid'])); ?>"'
                        title='<?php eT('Delete survey files'); ?>'
                    >
                    </span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php if ($templates) : ?>
    <label><?php eT('Themes storage'); ?></label>
    <table class='table table-striped table-bordered'>
        <?php foreach ($templates as $templates) : ?>
        <tr>
            <td style='width: 70%;'><?php echo $templates['name']; ?></td>
            <td><?php echo $templates['size']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
