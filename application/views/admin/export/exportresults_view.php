<div class='header ui-widget-header'><?php $clang->eT("Export results");?>
    <?php     if (isset($_POST['sql'])) {echo" - ".$clang->gT("Filtered from statistics script");}
        if ($SingleResponse) {
            echo " - ".sprintf($clang->gT("Single response: ID %s"),$SingleResponse);} 
    ?>
</div>
<div class='wrap2columns'>
    <?php echo CHtml::form(array('admin/export/sa/exportresults/surveyid/'.$surveyid), 'post', array('id'=>'resultexport'));?>
        <div class='left'>

            
            <fieldset <?php  if ($SingleResponse) {?>
                style='display:none';
            <?php } ?>
            ><legend><?php $clang->eT("General");?></legend>

                <ul><li><label><?php $clang->eT("Range:");?></label> <?php $clang->eT("From");?> <input type='text' name='export_from' size='8' value='1' />
                        <?php $clang->eT("to");?> <input type='text' name='export_to' size='8' value='<?php echo $max_datasets;?>' /></li>

                    <li><br /><label for='completionstate'><?php $clang->eT("Completion state");?></label> <select id='completionstate' name='completionstate'>
                            <option value='complete' <?php echo $selecthide;?>><?php $clang->eT("Completed responses only");?></option>
                            <option value='all' <?php echo $selectshow;?>><?php $clang->eT("All responses");?></option>
                            <option value='incomplete' <?php echo $selectinc;?>><?php $clang->eT("Incomplete responses only");?></option>
                        </select>
                    </li></ul></fieldset>

            <fieldset><legend>
                <?php $clang->eT("Headings");?></legend>
                <ul>
                    <li><input type='radio' class='radiobtn' name='exportstyle' value='code' id='headcodes' />
                        <label for='headcodes'><?php $clang->eT("Question code");?></label></li>
                    <li><input type='radio' class='radiobtn' name='exportstyle' value='abbreviated' id='headabbreviated' />
                        <label for='headabbreviated'><?php $clang->eT("Abbreviated question text");?></label></li>
                    <li><input type='radio' class='radiobtn' checked='checked' name='exportstyle' value='full' id='headfull'  />
                        <label for='headfull'><?php $clang->eT("Full question text");?></label></li>
                    <li><br /><input type='checkbox' value='Y' name='convertspacetous' id='convertspacetous' />
                        <label for='convertspacetous'>
                        <?php $clang->eT("Convert spaces in question text to underscores");?></label></li>
                </ul>
            </fieldset>

            <fieldset>
                <legend><?php $clang->eT("Responses");?></legend>
                <ul>
                    <li><input type='radio' class='radiobtn' name='answers' value='short' id='ansabbrev' />
                        <label for='ansabbrev'><?php $clang->eT("Answer codes");?></label></li>

                    <li><input type='checkbox' value='Y' name='convertyto1' id='convertyto1' style='margin-left: 25px' />
                        <label for='convertyto1'><?php $clang->eT("Convert Y to");?></label> <input type='text' name='convertyto' size='3' value='1' maxlength='1' style='width:10px'  />
                    </li>
                    <li><input type='checkbox' value='Y' name='convertnto2' id='convertnto2' style='margin-left: 25px' />
                        <label for='convertnto2'><?php $clang->eT("Convert N to");?></label> <input type='text' name='convertnto' size='3' value='2' maxlength='1' style='width:10px' />
                    </li><li>
                        <input type='radio' class='radiobtn' checked name='answers' value='long' id='ansfull' />
                        <label for='ansfull'>
                        <?php $clang->eT("Full answers");?></label></li>
                </ul></fieldset>
            <fieldset><legend><?php $clang->eT("Format");?></legend>
                <ul>
                    <li><input type='radio' class='radiobtn' name='type' value='csv' id='csvdoc' <?php if (!function_exists('iconv'))
                            { echo 'checked="checked" ';} ?> onclick='document.getElementById("ansabbrev").disabled=false;' />
                        <label for='csvdoc'><?php $clang->eT("CSV File (All charsets)");?></label></li>
                    <li><input type='radio' class='radiobtn' name='type' value='xls' checked id='exceldoc' <?php if (!function_exists('iconv')) echo ' disabled="disabled" ';?> onclick='document.getElementById("ansabbrev").disabled=false;' />
                        <label for='exceldoc'><?php $clang->eT("Microsoft Excel (All charsets)");?><?php if (!function_exists('iconv'))
                            { echo '<font class="warningtitle">'.$clang->gT("(Iconv Library not installed)").'</font>'; } ?>
                        </label></li>
                    <li>
                        <input type='radio' class='radiobtn' name='type' value='doc' id='worddoc' onclick='document.getElementById("ansfull").checked=true;document.getElementById("ansabbrev").disabled=true;' />
                        <label for='worddoc'>
                        <?php $clang->eT("Microsoft Word (Latin charset)");?></label></li>
                    <li><input type='radio' class='radiobtn' name='type' value='pdf' id='pdfdoc' onclick='document.getElementById("ansabbrev").disabled=false;' />
                        <label for='pdfdoc'><?php $clang->eT("PDF");?><br />
                        </label></li>
                </ul></fieldset>
        </div>
        <div class='right'>
            <fieldset>
                <legend><?php $clang->eT("Column control");?></legend>

                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                <?php 
                    if ($SingleResponse) { ?>
                    <input type='hidden' name='response_id' value="<?php echo $SingleResponse;?>" />
                    <?php }
                    $clang->eT("Choose columns");?>:

                <?php if ($afieldcount > 255) {
                        echo "\t<img src='$imageurl/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\""
                        .$clang->gT("Your survey contains more than 255 columns of responses. Spreadsheet applications such as Excel are limited to loading no more than 255. Select the columns you wish to export in the list below.","js")
                        ."\")' />";
                    }
                    else
                    {
                        echo "\t<img src='$imageurl/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\""
                        .$clang->gT("Choose the columns you wish to export.","js")
                        ."\")' />";
                } ?>
                <br /><select name='colselect[]' multiple size='20'>
                    <?php $i=1;
                        foreach($excesscols as $sFieldName=>$fieldinfo)
                        {
                            $questiontext=viewHelper::getFieldText($fieldinfo);
                            $questioncode=viewHelper::getFieldCode($fieldinfo);
                            echo "<option value='{$sFieldName}'";
                            if (isset($_POST['summary']))
                            {
                                if (in_array($ec, $_POST['summary']))
                                {
                                    echo "selected";
                                }
                            }
                            elseif ($i<256)
                            {
                                echo " selected";
                            }
                            echo " title='{$sFieldName} : ".str_replace("'", "&#39;",$questiontext)."'>".ellipsize("{$i} : {$questioncode} - ".str_replace(array("\r\n", "\n", "\r"), " ", $questiontext),45)."</option>\n";
                            $i++;
                    } ?>
                </select>
                <br />&nbsp;</fieldset>
            <?php if ($thissurvey['anonymized'] == "N" && tableExists("{{tokens_$surveyid}}") && hasSurveyPermission($surveyid,'tokens','read')) { ?>
                <fieldset><legend><?php $clang->eT("Token control");?></legend>
                    <?php $clang->eT("Choose token fields");?>:
                    <img src='<?php echo $imageurl;?>/help.gif' alt='<?php $clang->eT("Help");?>' onclick='javascript:alert("<?php $clang->gT("Your survey can export associated token data with each response. Select any additional fields you would like to export.","js");?>")' /><br />
                    <select name='attribute_select[]' multiple size='20'>
                        <option value='first_name' id='first_name'><?php $clang->eT("First name");?></option>
                        <option value='last_name' id='last_name'><?php $clang->eT("Last name");?></option>
                        <option value='email_address' id='email_address'><?php $clang->eT("Email address");?></option>

                        <?php $attrfieldnames=getTokenFieldsAndNames($surveyid,true);
                            foreach ($attrfieldnames as $attr_name=>$attr_desc)
                            {
                                echo "<option value='$attr_name' id='$attr_name' />".$attr_desc['description']."</option>\n";
                        } ?>
                    </select></fieldset>
                <?php } ?>
        </div>
        <div style='clear:both;'><p><input type='submit' value='<?php $clang->eT("Export data");?>' /></div></form></div>
