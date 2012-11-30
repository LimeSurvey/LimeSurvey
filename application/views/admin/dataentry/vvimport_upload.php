<br />
<div class='messagebox'>
   <div class='header'><?php $clang->eT("Import a VV response data file"); ?></div>
   		<div class='successtitle'><?php $clang->eT("Success"); ?></div>
                <?php $clang->eT("File upload succeeded."); ?><br /><br />
                <?php $clang->eT("Reading file.."); ?><br />
                <?php if($noid == 'noid' && $insertstyle == 'renumber') { ?>
                	<br />
                		<p style="color: #ff0000;">
                			<i>
                				<strong>
	                				<?php $clang->eT("Important Note:"); ?>
	                				<br />
	                				<?php $clang->eT("Do NOT refresh this page, as this will import the file again and produce duplicates"); ?>
                				</strong>
                			</i>
                		</p>
                	<br /><br />
                <?php } ?>
               <?php echo $clang->gT("Total records imported:") . ' '  . $importcount; ?>
               <br /> <br />
               [<a href='<?php echo $this->createUrl("/admin/responses/sa/index/surveyid/{$surveyid}"); ?>'><?php $clang->eT("Browse responses"); ?></a>]
</div>
<br />&nbsp;
