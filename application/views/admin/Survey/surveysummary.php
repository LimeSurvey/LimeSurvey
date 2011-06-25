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
<?php echo $details; ?>