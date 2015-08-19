<table <?php echo $showstyle; ?> id='surveydetails'>
    <tr>
        <td>
            <strong><?php eT("Title");?>:</strong>
        </td>
        <td class='settingentryhighlight'>
            <?php echo flattenText($surveyinfo['surveyls_title'])." (".gT("ID")." ".$surveyinfo['sid'].")";?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php echo gT("Survey URL") ." - ".getLanguageNameFromCode($surveyinfo['language'],false).":";?></strong>
        </td>
        <td>
        <?php $tmp_url = $this->createAbsoluteUrl("survey/index",array("sid"=>$surveyinfo['sid'],"lang"=>$surveyinfo['language'])); ?>
        <a href='<?php echo $tmp_url?>' target='_blank'><?php echo $tmp_url; ?></a>
        </td>
    </tr>
        <?php
        foreach ($aAdditionalLanguages as $langname)
        {?>
        <tr>
            <td>
                <strong><?php echo getLanguageNameFromCode($langname,false).":";?></strong>
            </td>
            <td>
            <?php $tmp_url = $this->createAbsoluteUrl("/survey/index",array("sid"=>$surveyinfo['sid'],"lang"=>$langname)); ?>
            <a href='<?php echo $tmp_url?>' target='_blank'><?php echo $tmp_url; ?></a>
            </td>
        </tr>

        <?php
        } ?>
    <tr>
        <td>
            <strong><?php eT("Description:");?></strong>
        </td>
        <td>
            <?php
                if (trim($surveyinfo['surveyls_description']) != '')
                {
                    templatereplace(flattenText($surveyinfo['surveyls_description']));
                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
                }
            ?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Welcome:");?></strong>
        </td>
        <td>
            <?php
                templatereplace(flattenText($surveyinfo['surveyls_welcometext']));
                echo LimeExpressionManager::GetLastPrettyPrintExpression();
            ?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("End message:");?></strong>
        </td>
        <td>
            <?php
                templatereplace(flattenText($surveyinfo['surveyls_endtext']));
                echo LimeExpressionManager::GetLastPrettyPrintExpression();
            ?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Administrator:");?></strong>
        </td>
        <td>
            <?php echo flattenText("{$surveyinfo['admin']} ({$surveyinfo['adminemail']})");?>
        </td>
    </tr>
    <?php if (trim($surveyinfo['faxto'])!='') { ?>
        <tr>
            <td>
                <strong><?php eT("Fax to:");?></strong>
            </td>
            <td>
                <?php echo flattenText($surveyinfo['faxto']);?>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <td>
            <strong><?php eT("Start date/time:");?></strong>
        </td>
        <td>
            <?php echo $startdate;?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Expiry date/time:");?></strong>
        </td>
        <td>
            <?php echo $expdate;?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Template:");?></strong>
        </td>
        <td>
            <?php $templatename = $surveyinfo['template'];
            if (Permission::model()->hasGlobalPermission('templates','read'))
            {
            	$templateurl_url = $this->createAbsoluteUrl("admin/templates/sa/view/editfile/startpage.pstpl/screenname/welcome/templatename/$templatename"); ?>
            	<a href='<?php echo $templateurl_url?>' target='_blank'><?php echo $templatename; ?></a>
            	<?php 
            }
            else
            {
            	echo $templatename;
            }
            ?>
        
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Base language:");?></strong>
        </td>
        <td>
            <?php echo $language;?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Additional languages:");?></strong>
        </td>
            <?php echo $additionnalLanguages;?>
    <tr>
        <td>
            <strong><?php eT("End URL");?>:</strong>
        </td>
        <td>
            <?php echo $endurl;?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Number of questions/groups");?>:</strong>
        </td>
        <td>
            <?php echo $sumcount3."/".$sumcount2;?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Survey currently active");?>:</strong>
        </td>
        <td>
            <?php echo $activatedlang;?>
        </td>
    </tr>
    <?php if($activated=="Y") { ?>
    <tr>
        <td>
            <strong><?php eT("Survey table name");?>:</strong>
        </td>
        <td>
            <?php echo $surveydb;?>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td>
            <strong><?php eT("Hints");?>:</strong>
        </td>
        <td>
            <?php echo $warnings.$hints;?>
        </td>
    </tr>
    <?php if ($tableusage != false){
            if ($tableusage['dbtype']=='mysql' || $tableusage['dbtype']=='mysqli'){
                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
                $size_usage =  round($tableusage['size'][0]/$tableusage['size'][1] * 100,2); ?>
                <tr><td><strong><?php eT("Table column usage");?>: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='<?php echo $column_usage;?>'></div> </td></tr>
                <tr><td><strong><?php eT("Table size usage");?>: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='<?php echo $size_usage;?>'></div></td></tr>
            <?php }
            elseif (($arrCols['dbtype'] == 'mssql')||($arrCols['dbtype'] == 'postgre')||($arrCols['dbtype'] == 'dblib')){
                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2); ?>
                <tr><td><strong><?php eT("Table column usage");?>: </strong></td><td><strong><?php echo $column_usage;?>%</strong><div class='progressbar' style='width:20%; height:15px;' name='<?php echo $column_usage;?>'></div> </td></tr>
            <?php }
        } ?>
</table>
