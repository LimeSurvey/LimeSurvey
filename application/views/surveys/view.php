<?php
/* @var Survey $survey */
?>
<table id='surveydetails'>
    <tr>
        <td>
            <strong><?php eT("Title");?>:</strong>
        </td>
        <td class='settingentryhighlight'>
            <?php echo flattenText($survey->localizedTitle)." (".gT("ID")." " .  $survey->sid .")";?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php echo gT("Survey URL") ." - ".getLanguageNameFromCode($survey->language,false).":";?></strong>
        </td>
        <td>
        <?php $tmp_url = $this->createAbsoluteUrl("survey/index",array("sid"=> $survey->sid,"lang"=> $survey->language)); ?>
        <a href='<?php echo $tmp_url?>' target='_blank'><?php echo $tmp_url; ?></a>
        </td>
    </tr>
        <?php
        foreach ($survey->additionalLanguages as $langname)
        {?>
        <tr>
            <td>
                <strong><?php echo getLanguageNameFromCode($langname,false).":";?></strong>
            </td>
            <td>
            <?php $tmp_url = $this->createAbsoluteUrl("/survey/index",array("sid"=>$survey->sid,"lang"=>$langname)); ?>
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
                if (trim($survey->localizedDescription) != '')
                {
                    templatereplace(flattenText($survey->localizedDescription));
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
                templatereplace(flattenText($survey->localizedWelcomeText));
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
                templatereplace(flattenText($survey->localizedEndText));
                echo LimeExpressionManager::GetLastPrettyPrintExpression();
            ?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Administrator:");?></strong>
        </td>
        <td>
            <?php echo flattenText("{$survey->admin} ({$survey->adminEmail})");?>
        </td>
    </tr>
    <?php if (trim($survey->faxto)!='') { ?>
        <tr>
            <td>
                <strong><?php eT("Fax to:");?></strong>
            </td>
            <td>
                <?php echo flattenText($survey->faxto);?>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <td>
            <strong><?php eT("Start date/time:");?></strong>
        </td>
        <td>
            <?php echo $survey->startdate; ?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Expiry date/time:");?></strong>
        </td>
        <td>
            <?php echo $survey->expires; ?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Template:");?></strong>
        </td>
        <td>
            <?php echo $survey->template;?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Base language:");?></strong>
        </td>
        <td>
            <?php echo $survey->language;?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Additional languages:");?></strong>
        </td>
            <?php echo implode(', ', $survey->additionalLanguages); ?>
    <tr>
        <td>
            <strong><?php eT("End URL");?>:</strong>
        </td>
        <td>
            <?php echo $survey->localizedEndUrl;?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Number of questions/groups");?>:</strong>
        </td>
        <td>
            <?php echo "{$survey->questionCount} / {$survey->groupCount}"; ?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php eT("Survey currently active");?>:</strong>
        </td>
        <td>
            <?php echo gT($survey->active);?>
        </td>
    </tr>
    <?php if($survey->active == "Y") { ?>
    <tr>
        <td>
            <strong><?php eT("Survey table name");?>:</strong>
        </td>
        <td>
            <?php echo Response::model($survey->sid)->tableName(); ?>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td>
            <strong><?php eT("Hints");?>:</strong>
        </td>
        <td>
            <?php 
                echo implode('<br>', $survey->hints);
            ?>
        </td>
    </tr> 
    <?php /*
    if ($tableusage != false){
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
    } */
    ?>
</table>
