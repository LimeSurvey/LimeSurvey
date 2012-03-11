<div class='menubar'>
    <div class='menubar-title ui-widget-header'><?php echo sprintf($clang->gT("View response ID %d"), $id); ?></div>
    <div class='menubar-main'>
        <img src='<?php echo $imageurl; ?>/blank.gif' width='31' height='20' border='0' hspace='0' align='left' alt='' />
        <img src='<?php echo $imageurl; ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
        <?php if (isset($rlanguage))
        { ?>
            <a href='<?php echo $this->createUrl("admin/dataentry/editdata/subaction/edit/surveyid/{$surveyid}/id/{$id}/lang/$rlanguage"); ?>' title='<?php $clang->eT("Edit this entry"); ?>'>
                <img align='left' src='<?php echo $imageurl; ?>/edit.png' alt='<?php $clang->gT("Edit this entry"); ?>' /></a>
        <?php }
        if (hasSurveyPermission($surveyid, 'responses', 'delete') && isset($rlanguage))
        { ?>
            <a href='#' title='<?php $clang->eT("Delete this entry"); ?>' onclick="if (confirm('<?php $clang->eT("Are you sure you want to delete this entry?", "js"); ?>')) { <?php echo convertGETtoPOST($this->createUrl("admin/dataentry/delete/id/$id/sid/$surveyid")); ?>}">
                <img align='left' hspace='0' border='0' src='<?php echo $imageurl; ?>/delete.png' alt='<?php $clang->eT("Delete this entry"); ?>' /></a>
        <?php }
        else
        { ?>
            <img align='left' hspace='0' border='0' src='<?php echo $imageurl; ?>/delete_disabled.png' alt='<?php $clang->eT("You don't have permission to delete this entry."); ?>'/>
        <?php }
        if (hasFileUploadQuestion($surveyid))
        { ?>
            <a href='#' title='<?php $clang->eT("Download files for this entry"); ?>' onclick="<?php echo convertGETtoPOST('?action=browse&amp;subaction=all&amp;downloadfile=' . $id . '&amp;sid=' . $surveyid); ?>" >
                <img align='left' hspace='0' border='0' src='<?php echo $imageurl; ?>/download.png' alt='<?php $clang->eT("Download files for this entry"); ?>' /></a>
        <?php } ?>

        <a href='<?php echo $this->createUrl("admin/export/exportresults/surveyid/$surveyid/id/$id"); ?>' title='<?php $clang->eT("Export this Response"); ?>' >
            <img name='ExportAnswer' src='<?php echo $imageurl; ?>/export.png' alt='<?php $clang->eT("Export this Response"); ?>' align='left' /></a>
        <img src='<?php echo $imageurl; ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
        <img src='<?php echo $imageurl; ?>/blank.gif' width='20' height='20' border='0' hspace='0' align='left' alt='' />
        <a href='<?php echo $this->createUrl("admin/browse/view/surveyid/$surveyid/id/$last"); ?>' title='<?php $clang->eT("Show previous..."); ?>' >
            <img name='DataBack' align='left' src='<?php echo $imageurl; ?>/databack.png' alt='<?php $clang->eT("Show previous..."); ?>' /></a>
        <img src='<?php echo $imageurl; ?>/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />
        <a href='<?php echo $this->createUrl("admin/browse/view/surveyid/$surveyid/id/$next"); ?>' title='<?php $clang->eT("Show next..."); ?>'>
            <img name='DataForward' align='left' src='<?php echo $imageurl; ?>/dataforward.png' alt='<?php $clang->eT("Show next..."); ?>' /></a>
    </div>
</div>

<table class='detailbrowsetable' width='99%'>
