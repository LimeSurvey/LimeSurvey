<tr>
	<td>
		<table width='100%' cellspacing='0'>
			<tr>
				$initialCheckbox
				<td width='90%'><?php echo $scenariotext; ?>&nbsp;
					<form action='<?php echo $this->createUrl("/admin/conditions/updatescenario/$surveyid/$gid/$qid/"); ?>' method='post' id='editscenario
						<?php echo $scenarionr['scenario']; ?>' style='display: none'>
					    <label><?php echo $clang->gT("New scenario number"); ?>":&nbsp;
					    <input type='text' name='newscenarionum' size='3'/></label>
					    <input type='hidden' name='scenario' value='<?php echo $scenarionr['scenario']; ?>'/>
					    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
					    <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
					    <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
					    <input type='hidden' name='subaction' value='updatescenario' />&nbsp;&nbsp;
					    <input type='submit' name='scenarioupdated' value='<?php echo $clang->gT("Update scenario"); ?>' />
					    <input type='button' name='cancel' value='<?php echo $clang->gT("Cancel"); ?>' onclick="$('#editscenario<?php echo $scenarionr['scenario']; ?>').hide('slow');" />
					</form>
				</td>
				<td width='10%' valign='middle' align='right'>
					<form id='deletescenario<?php echo $scenarionr['scenario']; ?>' action='<?php echo $this->createUrl("/admin/conditions/sa/action/subaction/deletescenario/surveyid/$surveyid/gid/$gid/qid/$qid/"); ?>' method='post' name='deletescenario<?php echo $scenarionr['scenario']; ?>' style='margin-bottom:0;'>
						<?php if(isset($additional_conetent)) echo $additional_content; ?>
						<input type='hidden' name='scenario' value='{$scenarionr['scenario']}' />
					    <input type='hidden' name='qid' value='$qid' />
					    <input type='hidden' name='sid' value='$surveyid' />
					    <input type='hidden' name='subaction' value='deletescenario' />
			    	</form>
			    </td>
			</tr>
		</table>
	</td>
</tr>
