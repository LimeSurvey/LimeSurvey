<?php
/**
 * Statistic output
 *
 * @var $outputs
 * @var $bSum
 * @var $bAnswer
 * @var $nbcols
 */
?>
<!-- _statisticsoutput_header -->
<div class="col-xl-<?php echo $nbcols; ?> col-md-12 ps-0 pe-3" >
<table class='table table-bordered printable' id="quid_<?php echo $outputs['parentqid'];?>">
    <thead>
        <tr class="active">
            <th colspan='4' align='center' style='text-align: center; '>
                <strong>
                    <?php echo sprintf(gT("Summary for %s"), $outputs['qtitle']); ?>
                </strong>
                <button class="float-end action_js_export_to_pdf btn btn-outline-secondary btn-sm d-print-none" data-question-id="quid_<?php echo $outputs['parentqid'];?>" data-bs-toggle="tooltip" title="<?php eT('Export this question to PDF.'); ?>" onclick="return false;">
                    <i class="ri-file-pdf-line"></i>
                </button>
            </th>
        </tr>
        <tr>
            <td colspan='4' align='center' style='text-align: center; '>
                <!-- question title -->
                <?php echo $outputs['qquestion'];?>
            </th>
        </tr>
        <!-- width depend on how much items... -->
        <tr>
            <th width='' align='center' >
                <strong>
                    <?php eT("Answer");?>
                </strong>
            </th>

            <?php if ($bShowCount  = true) : ?>
                <th width='' align='center' >
                    <strong><?php eT("Count"); ?></strong>
                </th>
            <?php endif;?>

            <?php if ($bShowPercentage  = true) : ?>
                <th width='' align='center' <?=(!$bSum ? 'colspan="2"' : '')?>>
                    <strong><?php eT("Gross percentage");?></strong>
                </th>
            <?php endif;?>

            <?php if ($bSum) : ?>
                <th width='' align='center' >
                    <strong>
                        <?php eT("Top 2, Middle, Bottom 2");?>
                    </strong>
                </th>
            <?php endif; ?>
        </tr>
    </thead>
<!-- end of _statisticsoutput_header -->
