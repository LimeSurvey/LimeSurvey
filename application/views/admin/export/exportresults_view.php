<div class="side-body">
    <h3>
        <?php eT("Export results");?>
        <?php     
            if (isset($_POST['sql'])) {echo" - ".gT("Filtered from statistics script");}
            if ($SingleResponse) 
            {
                echo " - ".sprintf(gT("Single response: ID %s"),$SingleResponse);
            } 
        ?>        
    </h3>
        <div class="row">
            <div class="col-lg-12 content-right">
                
<script type="text/javascript">
    var sMsgMaximumExcelColumns = '<?php eT("You can only choose 255 colums at a maximum for Excel export.",'js'); ?>';
    var sMsgExcelColumnsReduced = '<?php eT("The number of selected columns was reduced automatically.",'js'); ?>';
    var sMsgColumnCount = '<?php eT("%s of %s columns selected",'js'); ?>';
</script>

<div class='wrap2columns'>
    <?php echo CHtml::form(array('admin/export/sa/exportresults/surveyid/'.$surveyid), 'post', array('id'=>'resultexport'));?>
        <div class='left'>
<fieldset><legend><?php eT("Format");?></legend>
                <ul class="list-unstyled">  
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
            echo CHtml::openTag('div', array('class'=>'radio'));
            echo CHtml::radioButton('type', $info['checked'], $htmlOptions);
            echo " "; // Needed to get space between radio element and label
            echo CHtml::label($info['label'], $key);
            echo $tooltip;
            $tooltip .= CHtml::closeTag('div');
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
            ><legend><?php eT("General");?></legend>

                <ul class="list-unstyled"><li><?php eT("Range:");?><br>
                        <?php echo CHTML::label(gT("From"),"export_from") . CHTML::numberField('export_from','1',array('min'=>1,'max'=>$max_datasets,'step'=>1,'style'=>'max-width:7em')) ?>
                        <?php echo CHTML::label(gT("to"),"export_to") . CHTML::numberField('export_to',$max_datasets,array('min'=>1,'max'=>$max_datasets,'step'=>1,'style'=>'max-width:7em')) ?>
                    </li>

                    <li><label for='completionstate'><?php eT("Completion state");?></label> <select id='completionstate' name='completionstate' class="form-control">
                            <option value='complete' <?php echo $selecthide;?>><?php eT("Completed responses only");?></option>
                            <option value='all' <?php echo $selectshow;?>><?php eT("All responses");?></option>
                            <option value='incomplete' <?php echo $selectinc;?>><?php eT("Incomplete responses only");?></option>
                        </select>
                    </li>
                        <?php echo CHTML::label(gT("Export language"),"exportlang");
                            echo CHtml::dropDownList('exportlang', null, $aLanguages, array('class'=>'form-control'));
                        ?>
                    </ul></fieldset>

            <fieldset><legend>
                <?php eT("Headings");?></legend>
                <ul class="list-unstyled">
                    <li>
                        
                            <?php foreach($headexports as $type=>$headexport):?>
                                <li>
                                    <div class="radio">
                                    <?php 
                                    
                                    echo CHTML::radioButton('headstyle',$headexport['checked'],array('value'=>$type,'id'=>"headstyle-{$type}"))
                                             . CHTML::label($headexport['label'],"headstyle-{$type}",array('title'=>$headexport['help']));
                                    ?>
                                    </div>
                                </li>
                            <?php endforeach;?>
                        
                    </li>
                    </div>
                </ul>
            </fieldset>
           <fieldset>
                <legend><?php eT("Heading option");?></legend>
                <ul class="list-unstyled">
                    <li>
                        <div class="checkbox">
                            <?php echo CHTML::checkBox('headspacetounderscores',false,array('value'=>'1','id'=>'headspacetounderscores'));
                            echo CHTML::label(gT("Convert spaces in question text to underscores"),'headspacetounderscores'); ?>
                        </div>
                     </li>
                    <li>
                        <div class="checkbox">
                        <?php echo CHTML::checkBox('abbreviatedtext',false,array('value'=>'1','id'=>'abbreviatedtext'));
                        echo CHTML::label(gT("Text abbreviated"),'abbreviatedtext');?>
                        </div>
                        <ul class="list-unstyled">
                        <li>
                            <?php echo CHTML::label(gT("Number of characters"),'abbreviatedtextto');
                        echo CHTML::numberField('abbreviatedtextto','15',array('id'=>'abbreviatedtextto','size'=>'4','min'=>'1','step'=>'1')); ?>
                       </li>
                        </ul>
                    </li>
                    <li>
                        <div class="checkbox">
                        <?php echo CHTML::checkBox('emcode',false,array('value'=>'emcode','id'=>'emcode'));
                        echo CHTML::label(gT("Use Expression Manager code"),'emcode'); ?>
                        </div>
                    </li>
                    <li>
                        <?php echo CHTML::label(gT("Code/text separator"),'codetextseparator');
                    echo CHTML::textField('codetextseparator','. ',array('id'=>'codetextseparator','size'=>'4')); ?>
                    </li>
                </ul>
            </fieldset>


            <fieldset>
                <legend><?php eT("Responses");?></legend>
                <ul class="list-unstyled">
                    <li>
                        <div class="radio">
                            <?php echo CHTML::radioButton('answers',false,array('value'=>'short','id'=>'answers-short'));
                            echo CHTML::label(gT("Answer codes"),'answers-short');?>
                        </div>
                        
                        <ul class="list-unstyled">
                            <div class="checkbox">
                            <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo CHTML::checkBox('converty',false,array('value'=>'Y','id'=>'converty'));
                                echo CHTML::label(gT("Convert Y to"),'converty');?>
                                <?php echo CHTML::textField('convertyto','1',array('id'=>'convertyto','size'=>'3','maxlength'=>'1')); ?>
                            </li>
                            <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo CHTML::checkBox('convertn',false,array('value'=>'Y','id'=>'convertn'));
                                echo CHTML::label(gT("Convert N to"),'convertn');?>
                                <?php echo CHTML::textField('convertnto','2',array('id'=>'convertnto','size'=>'3','maxlength'=>'1')); ?>
                            </li>
                            </div>
                        </ul>
                    </li>
                    <li>
                        <div class="radio">
                        <?php echo CHTML::radioButton('answers',true,array('value'=>'long','id'=>'answers-long'));
                        echo CHTML::label(gT("Full answers"),'answers-long');?>
                        </div>
                </ul></fieldset>
        </div>
        <div class='right'>
            <fieldset>
                <legend><?php eT("Column control");?></legend>

                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                <?php 
                    if ($SingleResponse) { ?>
                    <input type='hidden' name='response_id' value="<?php echo $SingleResponse;?>" />
                    <?php }
                    eT("Choose columns");?>:
                <br />
                <?php 
                echo CHtml::listBox('colselect[]',array_keys($aFields),$aFields,array('multiple'=>'multiple','size'=>'20','style'=>'width:370px;','options'=>$aFieldsOptions));
                echo "\t<img src='$imageurl/help.gif' alt='".gT("Help")."' onclick='javascript:alert(\"".gT("Please note: The export to Excel is currently limited to loading no more than 255 columns.","js")."\")'>";?>
                <span id='columncount'>&nbsp;</span>
                </fieldset>
            <?php if ($thissurvey['anonymized'] == "N" && tableExists("{{tokens_$surveyid}}") && Permission::model()->hasSurveyPermission($surveyid,'tokens','read')) { ?>
                <fieldset><legend><?php eT("Token control");?></legend>
                    <?php eT("Choose token fields");?>:
                    <img src='<?php echo $imageurl;?>/help.gif' alt='<?php eT("Help");?>' onclick='javascript:alert("<?php gT("Your survey can export associated token data with each response. Select any additional fields you would like to export.","js");?>")' /><br />
                    <select name='attribute_select[]' multiple size='20' class="form-control">
                        <option value='first_name' id='first_name'><?php eT("First name");?></option>
                        <option value='last_name' id='last_name'><?php eT("Last name");?></option>
                        <option value='email_address' id='email_address'><?php eT("Email address");?></option>

                        <?php $attrfieldnames=getTokenFieldsAndNames($surveyid,true);
                            foreach ($attrfieldnames as $attr_name=>$attr_desc)
                            {
                                echo "<option value='$attr_name' id='$attr_name' />".$attr_desc['description']."</option>\n";
                        } ?>
                    </select></fieldset>
                <?php } ?>
        </div>
        <div style='clear:both;'><p><input type='submit' class="btn btn-default hidden" value='<?php eT("Export data");?>' /></div></form></div>
</div></div></div>