<?php

/**
 * @since 2017-09-20
 * @author Olle Härstedt
 */

?>

<h2><?php eT('Overview'); ?></h2>
<?php // Name/value pairs: a description list instead of a table without headers ?>
<dl class="row border rounded mx-0">
    <dt class="col-8 py-2 fw-normal border-bottom"><?php eT('Survey storage'); ?></dt>
    <dd class="col-4 py-2 mb-0 border-bottom"><?php echo $surveySize; ?></dd>
    <dt class="col-8 py-2 fw-normal border-bottom"><?php eT('Themes storage'); ?></dt>
    <dd class="col-4 py-2 mb-0 border-bottom"><?php echo $templateSize; ?></dd>
    <dt class="col-8 py-2 fw-normal border-bottom"><?php eT('Label set storage'); ?></dt>
    <dd class="col-4 py-2 mb-0 border-bottom"><?php echo $labelSize; ?></dd>
    <dt class="col-8 py-2"><?php eT('Total storage'); ?></dt>
    <dd class="col-4 py-2 mb-0 fw-bold"><?php echo $totalStorage; ?></dd>
</dl>


<?php if ($surveys) : ?>
    <h2><?php eT('Survey storage'); ?></h2>
    <table class='table table-striped table-bordered'>
            <caption class="visually-hidden">
                <?php eT("Survey storage details"); ?>
            </caption>
        <thead>
            <tr>
                <th scope="col" style='width: 70%;'><?php eT('Survey'); ?></th>
                <th scope="col"><?php eT('Size'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($surveys as $survey) : ?>
        <tr>
            <td>
                <?php echo $survey['name']; ?>
                <?php if ($survey['deleted']) : ?>
                    (<?php echo $survey['sid']; ?>)
                <?php else : ?>
                    (<a class="ls-link" href="<?php echo $this->createUrl('surveyAdministration/view', array('surveyid' => $survey['sid'])); ?>"><?php echo $survey['sid']; ?></a>)
                <?php endif; ?>
            </td>
            <td>
                <?php echo $survey['size']; ?>
                <?php if ($survey['showPurgeButton']) : ?>
                    <a
                        class='float-end btn btn-outline-secondary btn-xs'
                        data-bs-toggle='tooltip'
                        href="<?php echo $this->createUrl('surveyAdministration/purge', array('purge_sid' => $survey['sid'])); ?>"
                        title='<?php eT('Delete survey files'); ?>'
                        aria-label='<?php eT('Delete survey files'); ?>'
                    >
                        <span class='ri-delete-bin-fill text-danger' aria-hidden="true"></span>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if ($templates) : ?>
    <h2><?php eT('Themes storage'); ?></h2>
    <table class='table table-striped table-bordered'>
        <caption class="visually-hidden">
            <?php eT("Themes storage details"); ?>
        </caption>
        <thead>
            <tr>
                <th scope="col" style='width: 70%;'><?php eT('Theme'); ?></th>
                <th scope="col"><?php eT('Size'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($templates as $templates) : ?>
        <tr>
            <td><?php echo $templates['name']; ?></td>
            <td><?php echo $templates['size']; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
