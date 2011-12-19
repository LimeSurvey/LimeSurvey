<div class='header ui-widget-header'><?php echo $clang->gT("Data entry"); ?></div>
	<div class='messagebox ui-corner-all'>
		<br />
		<?php if(isset($save) && $errormsg): ?>
		<?php echo $clang->gT("Try again"); ?>:
		<form method='post'>
			<table class='outlinetable' cellspacing='0' align='center'>
			  	<tr>
			   		<td align='right'><?php echo $clang->gT("Identifier:"); ?></td>
			   		<td>
			   			<input type='text' name='save_identifier' value='<?php echo $_POST['save_identifier']; ?>' />'
			   		</td>
			   	</tr>
			  	<tr>
			  		<td align='right'><?php echo $clang->gT("Password:"); ?></td>
			   		<td>
			   			<input type='password' name='save_password' value='<?php echo $_POST['save_password']; ?>' />
			   		</td>
			   	</tr>
			  	<tr>
			  		<td align='right'><?php echo $clang->gT("Confirm Password:"); ?></td>
			   		<td>
			   			<input type='password' name='save_confirmpassword' value='<?php echo $_POST['save_confirmpassword']; ?>' />
			   		</td>
			   	</tr>
			  	<tr>
			  		<td align='right'><?php echo $clang->gT("Email:"); ?></td>
			   		<td>
			   			<input type='text' name='save_email' value='<?php echo $_POST['save_email']; ?>' />
			   		</td>
			   	</tr>
			  	<tr>
			  		<td align='right'><?php echo $clang->gT("Start Language:"); ?></td>
			   		<td>
			   			<input type='text' name='save_language' value='<?php echo $_POST['save_language']; ?>' />
			   		</td>
			   	</tr>
			   	<tr>
			   		<td></td>
			   		<td>
			   			<input type='submit' value='<?php echo $clang->gT("Submit"); ?>' />
						<input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
						<input type='hidden' name='subaction' value='<?php echo $_POST['subaction']; ?>' />
						<input type='hidden' name='language' value='<?php echo $lang; ?>' />
						<input type='hidden' name='save' value='on' />
					</td>
						<?php
							echo $hiddenfields;
							 
							if (isset($_POST['datestamp'])) { 
		                    	echo CHtml::hiddenField('datestamp', $_POST['datestamp']);
		                    }
							
		                    if (isset($_POST['ipaddr']))
		                    {
		                    	echo CHtml::hiddenField('ipaddr', $_POST['ipaddr']);
		                    }
						?>
				</tr>
			</table>
		</form>
		<?php endif; ?>
		
		<?php 
		
			foreach($dataentrymsgs as $msg) {
				echo $msg . "<br />\n";
			}
		
		?>
		
		<?php if(isset($thisid)): ?>
			<div class='successheader'><?php echo $clang->gT("Success"); ?></div>
			<?php echo $clang->gT("The entry was assigned the following record id: ")."{$thisid}"; ?> <br /><br />
		<?php endif; ?>
		
		<?php echo $errormsg; ?>
		
		<input type='submit' value='<?php echo $clang->gT("Add Another Record"); ?>' onclick="window.open('<?php echo Yii::app()->homeUrl.('/admin/dataentry/sa/view/surveyid/'.$surveyid.'/lang/'.$lang); ?>', '_top')" />
		<br /><br />
        <input type='submit' value='<?php echo $clang->gT("Return to survey administration"); ?>' onclick="window.open('<?php echo Yii::app()->homeUrl.('/admin/survey/view/'.$surveyid); ?>', '_top')" />
        <br /><br />
        
        <?php if(isset($thisid)): ?>
			<input type='submit' value='<?php echo $clang->gT("View This Record"); ?>' onclick="window.open('<?php echo Yii::app()->homeUrl.('/admin/browse/sa/action/surveyid/'.$surveyid.'/id/'.$thisid); ?>', '_top')" />
			<br /><br />
        <?php endif; ?>
        
        <?php if(isset($save)): ?>
        	<input type='submit' value='<?php echo $clang->gT("Browse Saved Responses"); ?>' onclick="window.open('<?php echo Yii::app()->homeUrl.('/admin/saved/sa/view/surveyid/'.$surveyid.'/all'); ?>', '_top')" />
        	<br /><br />
		<?php endif; ?>
	</div>
