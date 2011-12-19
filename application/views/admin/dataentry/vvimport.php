<?php 
if ($tableExists) {
?>
		<div class='header ui-widget-header'><?php echo $clang->gT("Import a VV survey file"); ?></div>
		<form id='vvexport' enctype='multipart/form-data' method='post' action="<?php echo $this->createURL('admin/dataentry/sa/vvimport/surveyid/'.$surveyid); ?>">
			<ul>
				<li>
					<label for='the_file'><?php echo $clang->gT("File:"); ?></label>
					<input type='file' size=50 id='the_file' name='the_file' />
				</li>
				<li>
					<label for='sid'><?php echo $clang->gT("Survey ID:"); ?></label>
					<input type='text' size=10 id='sid' name='sid' value='<?php echo $surveyid; ?>' readonly='readonly' />
				</li>
				<li>
					<label for='noid'><?php echo $clang->gT("Exclude record IDs?"); ?></label>
					<input type='checkbox' id='noid' name='noid' value='noid' checked=checked onchange='form.insertmethod.disabled=this.checked;' />
				</li>
				<li>
					<label for='insertmethod'><?php echo $clang->gT("When an imported record matches an existing record ID:"); ?></label>
					<select id='insertmethod' name='insert' disabled='disabled'>
		        		<option value='ignore' selected='selected'><?php echo $clang->gT("Report and skip the new record."); ?></option>
		        		<option value='renumber'><?php echo $clang->gT("Renumber the new record."); ?></option>
		        		<option value='replace'><?php echo $clang->gT("Replace the existing record."); ?></option>
		        	</select>
		        </li>
				<li>
					<label for='finalized'><?php echo $clang->gT("Import as not finalized answers?"); ?></label>
					<input type='checkbox' id='finalized' name='finalized' value='notfinalized' />
				</li>
				<li>
					<label for='vvcharset'><?php echo $clang->gT("Character set of the file:"); ?></label>
					<select id='vvcharset' name='vvcharset'>
						<?php echo $charsetsout; ?>
					</select>
				</li>
			</ul>
			<p>
				<input type='submit' value='<?php echo $clang->gT("Import"); ?>' />
				<input type='hidden' name='action' value='vvimport' />
				<input type='hidden' name='subaction' value='upload' />
			</p>
		</form>
		<br />

<?php } else { ?>
        <br />
        <div class='messagebox'>
	        <div class='header'><?php echo $clang->gT("Import a VV response data file"); ?></div>
	        <div class='warningheader'><?php echo $clang->gT("Cannot import the VVExport file."); ?></div>
			<?php echo $clang->gT("This survey is not active. You must activate the survey before attempting to import a VVexport file."); ?>
			<br /> <br />
	        [<a href='<?php echo $this->createUrl('/admin/survey/view/'.$surveyid); ?>'><?php echo $clang->gT("Return to survey administration"); ?></a>]
        </div>
<?php } ?>
