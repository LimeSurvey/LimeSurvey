<table <?php echo $showstyle; ?> id='surveydetails'>
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
		<?php $tmp_url = site_url($surveyinfo['sid']);
        echo "<a href='{$tmp_url}/lang-".$surveyinfo['language']."' target='_blank'>{$tmp_url}/lang-".$surveyinfo['language']."</a>";
        foreach ($aAdditionalLanguages as $langname)
        {
            echo "&nbsp;<a href='{$tmp_url}/lang-{$langname}' target='_blank'><img title='".$clang->gT("Survey URL for language:")." ".getLanguageNameFromCode($langname,false)
            ."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='".$this->config->item("imageurl")."/flags/{$langname}.png' /></a>";
        } ?>
		</td>
	</tr>
    <?php
        LimeExpressionManager::StartProcessingGroup($gid,($surveyinfo['anonymized']!="N"),$surveyinfo['sid']);  // loads list of replacement values available for this group
    ?>
    <tr>
    	<td align='right' valign='top'>
    		<strong><?php echo $clang->gT("Description:");?></strong>
    	</td>
    	<td align='left'>
        	<?php
                if (trim($surveyinfo['surveyls_description'])!='') {
                    templatereplace($surveyinfo['surveyls_description']);
                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
                    } ;
                    ?>
        </td>
	</tr>
	<tr>
		<td align='right' valign='top'>
			<strong><?php echo $clang->gT("Welcome:");?></strong>
		</td>
        <td align='left'>
        	<?php
                templatereplace($surveyinfo['surveyls_welcometext']);
                echo LimeExpressionManager::GetLastPrettyPrintExpression();
            ?>
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
    <?php if ($tableusage != false){
            if ($tableusage['dbtype']=='mysql'){
                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
                $size_usage =  round($tableusage['size'][0]/$tableusage['size'][1] * 100,2); ?>
                <tr><td align='right' valign='top'><strong><?php echo $clang->gT("Table Column Usage");?>: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='<?php echo $column_usage;?>'></div> </td></tr>
                <tr><td align='right' valign='top'><strong><?php echo $clang->gT("Table Size Usage");?>: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='<?php echo $size_usage;?>'></div></td></tr>
            <?php }
            elseif (($arrCols['dbtype'] == 'mssql')||($arrCols['dbtype'] == 'postgre')){
                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2); ?>
                <tr><td align='right' valign='top'><strong><?php echo $clang->gT("Table Column Usage");?>: </strong></td><td><strong><?php echo $column_usage;?>%</strong><div class='progressbar' style='width:20%; height:15px;' name='<?php echo $column_usage;?>'></div> </td></tr>
            <?php }
        } ?>
</table>