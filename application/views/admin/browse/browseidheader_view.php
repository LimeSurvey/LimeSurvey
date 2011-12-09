<div class='menubar'>
<div class='menubar-title ui-widget-header'><?php echo sprintf($clang->gT("View response ID %d"),$id);?></div>
<div class='menubar-main'>
<img src='<?php echo $imageurl;?>/blank.gif' width='31' height='20' border='0' hspace='0' align='left' alt='' />
<img src='<?php echo $imageurl;?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
<?php if (isset($rlanguage)) { ?>
	<a href='<?php echo $this->createUrl("admin/dataentry/editdata/edit/$id/$surveyid/$rlanguage");?>' title='<?php echo $clang->gTview("Edit this entry");?>'>
	<img align='left' src='<?php echo $imageurl;?>/edit.png' alt='<?php $clang->gT("Edit this entry");?>' /></a>
<?php }
if (bHasSurveyPermission($surveyid,'responses','delete') && isset($rlanguage)) { ?>
    <a href='#' title='<?php echo $clang->gTview("Delete this entry");?>' onclick="if (confirm('<?php echo $clang->gT("Are you sure you want to delete this entry?","js");?>')) { <?php echo get2post($this->createUrl("admin/dataentry/delete").'?action=dataentry&amp;subaction=delete&amp;id='.$id.'&amp;sid='.$surveyid);?>}">
    <img align='left' hspace='0' border='0' src='<?php echo $imageurl;?>/delete.png' alt='<?php echo $clang->gT("Delete this entry");?>' /></a>
<?php } else { ?>
    <img align='left' hspace='0' border='0' src='<?php echo $imageurl;?>/delete_disabled.png' alt='<?php echo $clang->gT("You don't have permission to delete this entry.");?>'/>
<?php }
if (bHasFileUploadQuestion($surveyid)) { ?>
    <a href='#' title='<?php echo $clang->gTview("Download files for this entry");?>' onclick="<?php echo get2post('?action=browse&amp;subaction=all&amp;downloadfile='.$id.'&amp;sid='.$surveyid);?>" >
    <img align='left' hspace='0' border='0' src='<?php echo $imageurl;?>/download.png' alt='<?php echo $clang->gT("Download files for this entry");?>' /></a>
<?php } ?>

<a href='$scriptname?action=exportresults&amp;sid=$surveyid&amp;id=$id' title='<?php echo $clang->gTview("Export this Response");?>' >
<img name='ExportAnswer' src='<?php echo $imageurl;?>/export.png' alt='<?php echo $clang->gT("Export this Response");?>' align='left' /></a>
<img src='<?php echo $imageurl;?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
<img src='<?php echo $imageurl;?>/blank.gif' width='20' height='20' border='0' hspace='0' align='left' alt='' />
<a href='<?php echo $this->createUrl("admin/browse/$surveyid/id/$last/");?>' title='<?php echo $clang->gTview("Show previous...");?>' >
<img name='DataBack' align='left' src='<?php echo $imageurl;?>/databack.png' alt='<?php echo $clang->gT("Show previous...");?>' /></a>
<img src='<?php echo $imageurl;?>/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />
<a href='<?php echo $this->createUrl("admin/browse/$surveyid/id/$next/");?>' title='<?php echo $clang->gTview("Show next...");?>'>
<img name='DataForward' align='left' src='<?php echo $imageurl;?>/dataforward.png' alt='<?php echo $clang->gT("Show next...");?>' /></a>
</div>
</div>

<table class='detailbrowsetable' width='99%'>