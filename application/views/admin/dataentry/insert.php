<div class='header ui-widget-header'><?php eT("Data entry"); ?></div>
	<div class='messagebox ui-corner-all'>
		<br />
		<?php if(isset($save) && $errormsg): ?>
		<?php eT("Try again"); ?>:
        <?php echo CHtml::form();?>
			<table class='outlinetable'>
			  	<tr>
			   		<td align='right'><?php eT("Identifier:"); ?></td>
			   		<td>
                        <?php echo CHtml::textField('text','save_identifier',$_POST['save_identifier']);?>
			   		</td>
			   	</tr>
			  	<tr>
			  		<td align='right'><?php eT("Password:"); ?></td>
			   		<td>
                        <?php echo CHtml::passwordField('save_password',$_POST['save_password']);?>
			   		</td>
			   	</tr>
			  	<tr>
			  		<td align='right'><?php eT("Confirm Password:"); ?></td>
			   		<td>
                        <?php echo CHtml::passwordField('save_confirmpassword',$_POST['save_confirmpassword']);?>
			   		</td>
			   	</tr>
			  	<tr>
			  		<td align='right'><?php eT("Email:"); ?></td>
			   		<td>
                        <?php echo CHtml::textField('save_email',$_POST['save_email']);?>
			   		</td>
			   	</tr>
			  	<tr>
			  		<td align='right'><?php eT("Start language:"); ?></td>
			   		<td>
                        <?php echo CHtml::textField('text','save_language',$_POST['save_language']);?>
			   		</td>
			   	</tr>
			   	<tr>
			   		<td></td>
			   		<td>
			   			<input type='submit' value='<?php eT("Submit"); ?>' />
						<input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                        <?php echo CHtml::hiddenField('subaction',$_POST['subaction']);?>
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
			<div class='successheader'><?php eT("Success"); ?></div>
			<?php echo gT("The entry was assigned the following record id: ")."{$thisid}"; ?> <br /><br />
		<?php endif; ?>

		<?php echo $errormsg; ?>

		<input type='submit' value='<?php eT("Add another record"); ?>' onclick="window.open('<?php echo $this->createUrl('/admin/dataentry/sa/view/surveyid/'.$surveyid.'/lang/'.$lang); ?>', '_top')" />
		<br /><br />
        <input type='submit' value='<?php eT("Return to survey administration"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/view/surveyid/'.$surveyid); ?>', '_top')" />
        <br /><br />

        <?php if(isset($thisid) && Permission::model()->hasSurveyPermission($surveyid, 'responses','read')): ?>
			<input type='submit' value='<?php eT("View this record"); ?>' onclick="window.open('<?php echo $this->createUrl('/admin/responses/sa/view/surveyid/'.$surveyid.'/id/'.$thisid); ?>', '_top')" />
			<br /><br />
        <?php endif; ?>

        <?php if(isset($save)): ?>
        	<input type='submit' value='<?php eT("Browse saved responses"); ?>' onclick="window.open('<?php echo $this->createUrl('/admin/saved/sa/view/surveyid/'.$surveyid.'/all'); ?>', '_top')" />
        	<br /><br />
		<?php endif; ?>
	</div>
