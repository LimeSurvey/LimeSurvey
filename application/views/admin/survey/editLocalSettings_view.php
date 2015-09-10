<div id="edittxtele-<?php echo $i;?>" class="tab-pane fade in <?php if($i==0){echo "active";}?> col-lg-6 center-box">
	<div class="input-group">
	  <span class="input-group-addon" id="question-group-title"><?php eT("Survey title"); ?></span>
	  <input class="form-control" type='text' size='80' id='short_title_<?php echo $esrow['surveyls_language']; ?>' name='short_title_<?php echo $esrow['surveyls_language']; ?>' value="<?php echo $esrow['surveyls_title']; ?>" />
	</div>					
	
	<div class="form-group form-group-lg">
		<label class="col-sm-2 control-label" for="description_<?php echo $esrow['surveyls_language']; ?>"><?php eT("Description:"); ?></label>
		<div class="htmleditorboot">
	        <textarea cols='80' rows='15' id='description_<?php echo $esrow['surveyls_language']; ?>' name='description_<?php echo $esrow['surveyls_language']; ?>'>
	        	<?php echo $esrow['surveyls_description']; ?>
	        </textarea>
	        <?php echo getEditor("survey-desc","description_".$esrow['surveyls_language'], "[".gT("Description:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action); ?>
		</div>
		
	</div>

	<div class="form-group form-group-lg">
		<label class="col-sm-2 control-label" for='welcome_<?php echo $esrow['surveyls_language']; ?>'><?php eT("Welcome message:"); ?></label>
		
		<div class="htmleditorboot">
        	<textarea cols='80' rows='15' id='welcome_<?php echo $esrow['surveyls_language']; ?>' name='welcome_<?php echo $esrow['surveyls_language']; ?>'>
        		<?php echo $esrow['surveyls_welcometext']; ?>
        	</textarea>
        	<?php echo getEditor("survey-welc","welcome_".$esrow['surveyls_language'], "[".gT("Welcome:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action); ?>
		</div>
	</div>

	<div class="form-group form-group-lg">
		<label class="col-sm-2 control-label" for='endtext_<?php echo $esrow['surveyls_language']; ?>'><?php eT("End message:"); ?></label>
		
		<div class="htmleditorboot">
        	<textarea cols='80' rows='15' id='endtext_<?php echo $esrow['surveyls_language']; ?>' name='endtext_<?php echo $esrow['surveyls_language']; ?>'>
        		<?php echo $esrow['surveyls_endtext']; ?>
        	</textarea>
        	<?php echo getEditor("survey-endtext","endtext_".$esrow['surveyls_language'], "[".gT("End message:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action); ?>
		</div>
	</div>

	<div class="col-lg-6">
			<div class="input-group">
			  <span class="input-group-addon"><?php eT("End URL:"); ?></span>
        	  <input class="form-control" type='text' size='80' maxlength='2000' id='url_<?php echo $esrow['surveyls_language']; ?>' name='url_<?php echo $esrow['surveyls_language']; ?>' value="<?php echo ($esrow['surveyls_url']!="")?$esrow['surveyls_url']:"http://"; ?>" />			  
			</div>									
	</div>

	<div class="col-lg-6">
			<div class="input-group">
			  <span class="input-group-addon"><?php eT("URL description:"); ?></span>
        	  <input class="form-control" type='text' id='urldescrip_<?php echo $esrow['surveyls_language']; ?>' size='80' name='urldescrip_<?php echo $esrow['surveyls_language']; ?>' value="<?php echo $esrow['surveyls_urldescription']; ?>" />			  
			</div>									
	</div>
	
	<div class="col-lg-6">
			<div class="input-group">
			  <span class="input-group-addon"><?php eT("Date format:"); ?></span>

				<select size='1' id='dateformat_<?php echo $esrow['surveyls_language']; ?>' name='dateformat_<?php echo $esrow['surveyls_language']; ?>' class="form-control">
				    <?php foreach (getDateFormatData(0,Yii::app()->session['adminlang']) as $index=>$dateformatdata)
				        { ?>
				        <option value='<?php echo $index; ?>'
				            <?php if ($esrow['surveyls_dateformat']==$index) { ?>
				                selected='selected'
				                <?php } ?>
				            ><?php echo $dateformatdata['dateformat']; ?></option>
				        <?php } ?>
				</select>			  
			  
			</div>									
	</div>

	<div class="col-lg-6">
			<div class="input-group">
			  <span class="input-group-addon"><?php eT("Decimal mark:"); ?></span>

				<select size='1' id='numberformat_<?php echo $esrow['surveyls_language']; ?>' name='numberformat_<?php echo $esrow['surveyls_language']; ?>' class="form-control">
				    <?php foreach (getRadixPointData() as $index=>$radixptdata)
				        { ?>
				        <option value='<?php echo $index; ?>'
				            <?php if ($esrow['surveyls_numberformat']==$index) { ?>
				                selected='selected'
				                <?php } ?>
				            ><?php echo $radixptdata['desc']; ?></option>
				        <?php } ?>
				</select>			  
			</div>									
	</div>	
</div>
