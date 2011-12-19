<br />
<div class='messagebox'>
   <div class='header'><?php echo $clang->gT("Import a VV response data file"); ?></div>
   		<div class='successtitle'><?php echo $clang->gT("Success"); ?></div>
                <?php echo $clang->gT("File upload succeeded."); ?><br /><br />
                <?php echo $clang->gT("Reading file.."); ?><br />
                <?php if($noid == 'noid' && $insertstyle == 'renumber') { ?>
                	<br />
                		<p style="color: #ff0000;">
                			<i>
                				<strong>
	                				<?php echo $clang->gT("Important Note:"); ?>
	                				<br />
	                				<?php echo $clang->gT("Do NOT refresh this page, as this will import the file again and produce duplicates"); ?>
                				</strong>
                			</i>
                		</p>
                	<br /><br />
                <?php } ?>
               <?php echo $clang->gT("Total records imported:") . ' '  . $importcount; ?>
               <br /> <br />
               [<a href='<?php echo $this->createUrl('/').'/admin/browse/surveyid/'.$surveyid; ?>'><?php echo $clang->gT("Browse responses"); ?></a>]
</div>
<br />&nbsp;
