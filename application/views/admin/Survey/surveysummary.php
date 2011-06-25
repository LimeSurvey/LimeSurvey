<table id='surveydetails'>
	<tr>
		<td align='right' valign='top' width='15%'>
			<strong><?php echo $clang->gT("Title");?>:</strong>
		</td>
		<td align='left' class='settingentryhighlight'>
			<strong><?php echo $surveyinfo['surveyls_title']." (".$clang->gT("ID")." ".$surveyinfo['sid'].")";?></strong>
		</td>
	</tr>
	<tr>
		<td align='right' valign='top'>
			<strong><?php echo $clang->gT("Survey URL") ." (".getLanguageNameFromCode($surveyinfo['language'],false)."):";?></strong>
		</td>
		<td align='left'>
			<!-- TODO Port -->
		</td>
	</tr>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Description:");?></strong>
    	</td>
    	<td align='left'>
        	<?php if (trim($surveyinfo['surveyls_description'])!='') {echo " {$surveyinfo['surveyls_description']}";} ;?>
        </td>
	</tr>
	<tr>
		<td align='right' valign='top'>
			<strong><?php echo $clang->gT("Welcome:");?></strong>
		</td>
        <td align='left'>
        	<?php echo $surveyinfo['surveyls_welcometext'];?>
        </td>
	</tr>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Administrator:");?></strong>
    	</td>
        <td align='left'>
        	<?php echo "{$surveyinfo['admin']} ({$surveyinfo['adminemail']})";?>
        </td>
	</tr>
	<?php if (trim($surveyinfo['faxto'])!='') { ?>
	    <tr>
	    	<td align='right' valign='top'>
	    		<strong><?php echo $clang->gT("Fax to:");?></strong>
	    	</td>
	    	<td align='left'>
	    		<?php echo$surveyinfo['faxto'];?>
	    	</td>
	    </tr>
    <?php } ?>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Start date/time:");?></strong>
    	</td>
        <td align='left'>
        	<?php echo $startdate;?>
        </td>
    </tr>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Expiry date/time:");?></strong>
    	</td>
    	<td align='left'>
    		<?php echo $expdate;?>
    	</td>
    </tr>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Template:");?></strong>
    	</td>
    	<td align='left'>
    		<?php echo $surveyinfo['template'];?>
    	</td>
    </tr>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Base language:");?></strong>
    	</td>
    	<td align='left'>
    		<?php echo $language;?>
    	</td>
    </tr>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Additional Languages");?></strong>
    	</td>
    		<?php echo $additionnalLanguages;?>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("End URL");?>:</strong>
    	</td>
    	<td align='left'>
    		<?php echo $endurl;?>
    	</td>
    </tr>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Number of questions/groups");?>:</strong>
    	</td>
    	<td align='left'>
    		<?php echo $sumcount3."/".$sumcount2;?>
    	</td>
    </tr>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Survey currently active");?>:</strong>
    	</td>
    	<td align='left'>
    		<?php echo $activatedlang;?>
    	</td>
    </tr>
    <?php if($activated=="Y") { ?>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Survey table name");?>:</strong>
    	</td>
    	<td align='left'>
    		<?php echo $surveydb;?>
    	</td>
    </tr>
    <?php } ?>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Hints");?>:</strong>
    	</td>
    	<td align='left'>
    		<?php echo $warnings.$hints;?>
    	</td>
    </tr>
</table>