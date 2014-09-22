<script type="text/javascript">
    var sMsgMaximumExcelColumns = '<?php $clang->eT("You can only choose 255 colums at a maximum for Excel export.",'js'); ?>';
    var sMsgExcelColumnsReduced = '<?php $clang->eT("The number of selected columns was reduced automatically.",'js'); ?>';
    var sMsgColumnCount = '<?php $clang->eT("%s of %s columns selected",'js'); ?>';
</script>
<div class='header ui-widget-header'><?php $clang->eT("Export results");?>
    <?php     if (isset($_POST['sql'])) {echo" - ".$clang->gT("Filtered from statistics script");}
        if ($SingleResponse) {
            echo " - ".sprintf($clang->gT("Single response: ID %s"),$SingleResponse);} 
    ?>
</div>
<div class='wrap2columns'>
    <?php echo CHtml::form(array('admin/export/sa/exportresults/surveyid/'.$surveyid), 'post', array('id'=>'resultexport'));?>
        <div class='left'>
<fieldset><legend><?php $clang->eT("Format");?></legend>
                <ul>  
<?php
    $hasTips = false;
    foreach ($exports as $key => $info)
    {
        // Only output when a label was set
        if (!empty($info['label'])) {
            $htmlOptions = array(
                'id'=>$key,
                'value'=>$key,
                'class'=>'radiobtn'
                );
            // For onclick, always start to re-enable all disabled elements
            $htmlOptions['onclick'] = "$('form#resultexport input:disabled').attr('disabled', false);" . $info['onclick'];
            if (!empty($info['tooltip'])) {
                $hasTips = true;
                $tooltip = CHtml::openTag('div', array('class'=>'tooltip-export'));
                $tooltip .= CHtml::image($imageurl. '/help.gif');
                $tooltip .= ChTml::tag('div', array('class'=>'exporttip'), $info['tooltip']);
                $tooltip .= CHtml::closeTag('div');
            } else {
                $tooltip = '';
            }
            echo CHtml::openTag('li');
            echo CHtml::radioButton('type', $info['checked'], $htmlOptions);
            echo " "; // Needed to get space between radio element and label
            echo CHtml::label($info['label'], $key);
            echo $tooltip;
            echo CHtml::closeTag('li');
        }
    }
    if ($hasTips) {
        // We have tooltips, now register javascript
        App()->clientScript->registerScript('tooltip-export', 
                "jQuery('div.tooltip-export').popover({
                    html: true,
                    content: function() {
                        return $(this).find('div.exporttip').clone();
                    },
                    title: function() { 
                        return $(this).parent().find('label').text();
                    },
                    trigger: 'hover'
                });
                ");
    }
?>
            </ul></fieldset>            
            <fieldset <?php  if ($SingleResponse) {?>
                style='display:none';
            <?php } ?>
            ><legend><?php $clang->eT("General");?></legend>

                <ul><li><label><?php $clang->eT("Range:");?></label><br> <?php $clang->eT("From");?> <input type='text' name='export_from' size='7' value='1' />
                        <?php $clang->eT("to");?> <input type='text' name='export_to' size='7' value='<?php echo $max_datasets;?>' /></li>

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
                        <label for='convertyto1'><?php $clang->eT("Convert Y to");?></label> <input type='text' name='convertyto' id='convertyto' size='3' value='1' maxlength='1' style='width:10px'  />
                    </li>
                    <li><input type='checkbox' value='Y' name='convertnto2' id='convertnto2' style='margin-left: 25px' />
                        <label for='convertnto2'><?php $clang->eT("Convert N to");?></label> <input type='text' name='convertnto' id='convertnto' size='3' value='2' maxlength='1' style='width:10px' />
                    </li><li>
                        <input type='radio' class='radiobtn' checked name='answers' value='long' id='ansfull' />
                        <label for='ansfull'>
                        <?php $clang->eT("Full answers");?></label></li>
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
                <br />
                <?php 
                echo CHtml::listBox('colselect[]',array_keys($aFields),$aFields,array('multiple'=>'multiple','size'=>'20','style'=>'width:370px;','options'=>$aFieldsOptions));
                echo "\t<img src='$imageurl/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\"".$clang->gT("Please note: The export to Excel is currently limited to loading no more than 255 columns.","js")."\")'>";?>
                <span id='columncount'>&nbsp;</span>
                </fieldset>
            <?php if ($thissurvey['anonymized'] == "N" && tableExists("{{tokens_$surveyid}}") && Permission::model()->hasSurveyPermission($surveyid,'tokens','read')) { ?>
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
