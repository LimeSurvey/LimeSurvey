<div class='menubar'>
    <div class='menubar-title ui-widget-header'><strong><?php echo sprintf($clang->gT("View response ID %d"), $id); ?></strong></div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <img src='<?php echo $sImageURL; ?>blank.gif' width='31' height='16' alt='' />
            <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
            <?php if($exist) { ?>
                <?php if (isset($rlanguage))
                    { ?>
                    <a href='<?php echo $this->createUrl("admin/dataentry/editdata/subaction/edit/surveyid/{$surveyid}/id/{$id}/lang/$rlanguage"); ?>' title='<?php $clang->eT("Edit this entry"); ?>'>
                        <img src='<?php echo $sImageURL; ?>edit.png' alt='<?php $clang->gT("Edit this entry"); ?>' /></a>
                    <?php }
                    if (hasSurveyPermission($surveyid, 'responses', 'delete') && isset($rlanguage))
                    { ?>
                    <a href='#' title='<?php $clang->eT("Delete this entry"); ?>' onclick="if (confirm('<?php $clang->eT("Are you sure you want to delete this entry?", "js"); ?>')) { <?php echo convertGETtoPOST($this->createUrl("admin/dataentry/sa/delete/id/$id/sid/$surveyid")); ?>}">
                        <img src='<?php echo $sImageURL; ?>delete.png' alt='<?php $clang->eT("Delete this entry"); ?>' /></a>
                    <?php }
                    else
                    { ?>
                    <img src='<?php echo $sImageURL; ?>delete_disabled.png' alt='<?php $clang->eT("You don't have permission to delete this entry."); ?>'/>
                    <?php }
                    if (hasFileUploadQuestion($surveyid))
                    { ?>
                    <a href='#' title='<?php $clang->eT("Download files for this entry"); ?>' onclick="<?php echo convertGETtoPOST('?action=browse&amp;subaction=all&amp;downloadfile=' . $id . '&amp;sid=' . $surveyid); ?>" >
                        <img src='<?php echo $sImageURL; ?>download.png' alt='<?php $clang->eT("Download files for this entry"); ?>' /></a>
                    <?php } ?>

                <a href='<?php echo $this->createUrl("admin/export/sa/exportresults/surveyid/$surveyid/id/$id"); ?>' title='<?php $clang->eT("Export this response"); ?>' >
                    <img src='<?php echo $sImageURL; ?>export.png' alt='<?php $clang->eT("Export this response"); ?>'/></a>
                <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
            <?php } ?>
            <img src='<?php echo $sImageURL; ?>blank.gif' width='20' height='20' alt='' />
            <?php if($previous) { ?>
            <a href='<?php echo $this->createUrl("admin/responses/sa/view/surveyid/$surveyid/id/$previous"); ?>' title='<?php $clang->eT("Show previous..."); ?>' >
                <img src='<?php echo $sImageURL; ?>databack.png' alt='<?php $clang->eT("Show previous..."); ?>' /></a>
            <?php } ?>
            <?php if($next) { ?>
                <a href='<?php echo $this->createUrl("admin/responses/sa/view/surveyid/$surveyid/id/$next"); ?>' title='<?php $clang->eT("Show next..."); ?>'>
                    <img src='<?php echo $sImageURL; ?>dataforward.png' alt='<?php $clang->eT("Show next..."); ?>' /></a>
            <?php } ?>
        </div>
    </div>
</div>

<table class='detailbrowsetable'>
