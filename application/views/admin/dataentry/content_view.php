<?php
    if (!empty($explanation))
    { ?>
    <tr class ='data-entry-explanation'><td class='data-entry-small-text' colspan='3' align='left'><?php echo $explanation; ?></td></tr>
    <?php } ?>

<tr class='<?php echo $bgc; ?>'>
    <td class='data-entry-small-text' valign='top' width='1%'><?php echo $deqrow['title']; ?></td>
    <td valign='top' align='right' width='30%'>
        <?php if ($deqrow['mandatory']=="Y") //question is mandatory
            // TODO - should be mandatory AND relevant
            { ?>
            <font color='red'>*</font>
            <?php } ?>
        <strong><?php
                //                    echo flattenText($deqrow['question']);
                echo $deqrow['question'];   // don't flatten if want to use EM.  However, may not be worth it as want dynamic relevance and question changes
        ?></strong></td>
    <td valign='top'  align='left' style='padding-left: 20px'>

    <?php if ($deqrow['help'])
        { ?>
        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/help.gif' alt='<?php echo $blang->gT("Help about this question"); ?>' align='right' onclick="javascript:alert('Question <?php echo $deqrow['title']; ?> Help: <?php echo $hh; ?>')" />
        <?php }
        switch($deqrow['type'])
        {
            case "5": //5 POINT CHOICE radio-buttons ?>
            <select name='<?php echo $fieldname; ?>'>
                <option value=''><?php echo $blang->gT("No answer"); ?></option>
                <?php for ($x=1; $x<=5; $x++)
                    { ?>
                    <option value='<?php echo $x; ?>'><?php echo $x; ?></option>
                    <?php } ?>
            </select>
            <?php break;
            case "D": //DATE
                //                            $qidattributes = getQuestionAttributeValues($deqrow['qid'], $deqrow['type']);
                $dateformatdetails = getDateFormatDataForQID($qidattributes, $thissurvey);
                if(canShowDatePicker($dateformatdetails))
                {
                    $goodchars = str_replace( array("m","d","y", "H", "M"), "", $dateformatdetails['dateformat']);
                    $goodchars = "0123456789".$goodchars[0]; ?>
                <input type='text' class='popupdate' size='12' name='<?php echo $fieldname; ?>' onkeypress="return goodchars(event,'<?php echo $goodchars; ?>')"/>
                <input type='hidden' name='dateformat<?php echo $fieldname; ?>' id='dateformat<?php echo $fieldname; ?>' value='<?php echo $dateformatdetails['jsdate']; ?>'  />
                <?php }
                else
                { ?>
                <input type='text' name='<?php echo $fieldname; ?>'/>
                <?php }
                break;
            case "G":  //GENDER drop-down list ?>
            <select name='<?php echo $fieldname; ?>'>
                <option selected='selected' value=''><?php echo $blang->gT("Please choose"); ?>..</option>
                <option value='F'><?php echo $blang->gT("Female"); ?></option>
                <option value='M'><?php echo $blang->gT("Male"); ?></option>
            </select>
            <?php break;
            case "Q": //MULTIPLE SHORT TEXT
            case "K": ?>

            <table>
                <?php foreach ($dearesult as $dearow)
                    { ?>
                    <tr><td align='right'>
                            <?php echo $dearow['question']; ?>
                        </td>
                        <td><input type='text' name='<?php echo $fieldname.$dearow['title']; ?>' /></td>
                    </tr>
                    <?php } ?>
            </table>
            <?php break;

            case "1": // multi scale^ ?>

            <table><tr><td></td><th><?php echo sprintf($clang->gT('Label %s'),'1').'</th><th>'.sprintf($clang->gT('Label %s'),'2'); ?></th></tr>

            <?php foreach ($dearesult as $dearow)
                {
                    // first scale
                    $delquery = "SELECT * FROM {{answers}} WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' and scale_id=0 ORDER BY sortorder, code";
                    $delresult = dbExecuteAssoc($delquery); ?>
                <tr><td><?php echo $dearow['question']; ?></td><td>
                        <select name='<?php echo $fieldname.$dearow['title']; ?>#0'>
                            <option selected='selected' value=''><?php $clang->eT("Please choose..."); ?></option>
                            <?php foreach ($delresult as $delrow)
                                { ?>
                                <option value='<?php echo $delrow['code']; ?>'><?php echo $delrow['answer']; ?></option>
                                <?php } ?>
                        </select></td>
                    <?php $delquery = "SELECT * FROM {{answers}} WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' and scale_id=1 ORDER BY sortorder, code";
                        $delresult = dbExecuteAssoc($delquery); ?>
                    <td>
                        <select name='<?php echo $fieldname.$dearow['title']; ?>#1'>
                            <option selected='selected' value=''><?php $clang->eT("Please choose..."); ?></option>
                            <?php foreach ($delresult as $delrow)
                                { ?>
                                <option value='<?php echo $delrow['code']; ?>'><?php echo $delrow['answer']; ?></option>
                                <?php } ?>
                        </select></td></tr>
                <?php }
                if ($fother == "Y")
                { ?>
                <option value='-oth-'><?php $clang->eT("Other"); ?></option>
                <?php }

                if ($fother == "Y")
                {
                    $clang->eT("Other"); ?>:
                <input type='text' name='<?php echo $fieldname; ?>other' value='' />
                <?php } ?>
        </tr></table>
        <?php break;

        case "L": //LIST drop-down/radio-button list
        case "!": ?>
        <select name='<?php echo $fieldname; ?>'>
            <?php if ($defexists=="") { ?>
                <option selected='selected' value=''><?php echo $blang->gT("Please choose"); ?>..</option><?php echo $datatemp; }
                else  { echo $datatemp;} ?>

            <?php if ($fother == "Y")
                { ?>
                <option value='-oth-'><?php echo $blang->gT("Other"); ?></option>
                <?php } ?>
        </select>
        <?php if ($fother == "Y")
            { ?>
            <?php echo $blang->gT("Other"); ?>:
            <input type='text' name='<?php echo $fieldname; ?>other' value='' />
            <?php }
            break;
        case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea ?>
        <select name='<?php echo $fieldname; ?>'>

            <?php if ($defexists=="") { ?>
                <option selected='selected' value=''><?php echo $blang->gT("Please choose"); ?>..</option><?php echo $datatemp; }
                else  { echo $datatemp;} ?>
        </select>
        <br /><?php echo $blang->gT("Comment"); ?>:<br />
        <textarea cols='40' rows='5' name='<?php echo $fieldname; ?>comment'></textarea>
        <?php break;
        case "R": //RANKING TYPE QUESTION ?>
        <div id="question<?php echo $thisqid ?>" class="ranking-answers"><ul class="answers-list">
        <?php for ($i=1; $i<=$anscount; $i++)
            {
            ?>
            <li class="select-item">
            <?php
                if($i==1){
                    echo $blang->gT('First choice');
                }else{
                    echo $blang->gT('Next choice');
                }
            ?>
            <select name="<?php echo $fieldname.$i ?>" id="answer<?php echo $fieldname.$i ?>">";
                <option value=""><?php echo $blang->gT('None') ?></option>
                <?php
                    foreach ($answers as $ansrow)
                    {
                        echo "\t<option value=\"".$ansrow['code']."\">".flattenText($ansrow['answer'])."</option>\n";
                    }
                ?>
            </select>
            </li>
        <?php 
            }
            ?>
        </ul>
        <div style="display:none" id="ranking-<?php echo $thisqid ?>-maxans"><?php echo $anscount ?></div>
        <div style="display:none" id="ranking-<?php echo $thisqid ?>-minans">0</div>
        <div style="display:none" id="ranking-<?php echo $thisqid ?>-name">javatbd<?php echo $fieldname ?></div>
        <div style="display:none">
        <?php foreach ($answers as $ansrow)
        {
            echo "<div id=\"htmlblock-{$thisqid}-{$ansrow['code']}\">{$ansrow['answer']}</div>";
        }
        ?>
        </div>
        <script type='text/javascript'>
            <!--
            var aRankingTranslations = {
                choicetitle: '<?php echo $clang->gT("Your Choices",'js') ?>',
                ranktitle: '<?php echo $clang->gT("Your Ranking",'js') ?>'
            };
            function checkconditions(){};
            $(function() {
                doDragDropRank(<?php echo $thisqid ?>,0,true,true);
            });
            -->
        </script>
        </div>
        <?php
            break;
        case "M": //Multiple choice checkbox (Quite tricky really!)

            if ($deqrow['other'] == "Y") {$meacount++;}
            if ($dcols > 0 && $meacount >= $dcols)
            {
                $width=sprintf("%0d", 100/$dcols);
                $maxrows=ceil(100*($meacount/$dcols)/100); //Always rounds up to nearest whole number
                $divider=" </td> <td valign='top' width='$width%' nowrap='nowrap'>";
                $upto=0; ?>
            <table class='question'><tr> <td valign='top' width='<?php echo $width; ?>%' nowrap='nowrap'>
                        <?php foreach ($mearesult as $mearow)
                            {
                                if ($upto == $maxrows)
                                {
                                    echo $divider;
                                    $upto=0;
                            } ?>
                            <input type='checkbox' class='checkboxbtn' name='<?php echo $fieldname.$mearow['title']; ?>' id='answer<?php echo $fieldname.$mearow['title']; ?>' value='Y' />
                            <label for='answer<?php echo $fieldname.$mearow['title']; ?>'><?php echo $mearow['question']; ?></label><br />
                            <?php $upto++; ;
                            }
                            if ($deqrow['other'] == "Y")
                            { ?>
                            <?php echo $blang->gT("Other"); ?> <input type='text' name='<?php echo $fieldname; ?>other' />
                            <?php } ?>
                    </td></tr></table>

            <?php }
            else
            {
                while ($mearow = $mearesult->FetchRow())
                { ?>
                <input type='checkbox' class='checkboxbtn' name='<?php echo $fieldname.$mearow['code']; ?>' id='answer<?php echo $fieldname.$mearow['code']; ?>' value='Y'
                    <?php if ($mearow['default_value'] == "Y") {  ?>checked<?php } ?>
                    /><label for='<?php $fieldname.$mearow['code']; ?>'><?php echo $mearow['answer']; ?></label><br />
                <?php }
                if ($deqrow['other'] == "Y")
                { ?>
                <?php echo $blang->gT("Other"); ?> <input type='text' name='<?php echo $fieldname; ?>other' />
                <?php }
            }
            break;
        case "I": //Language Switch ?>
        <select name='<?php echo $fieldname; ?>'>
            <option value='' selected='selected'><?php echo $blang->gT("Please choose"); ?>..</option>

            <?php foreach ($slangs as $lang)
                { ?>
                <option value='<?php echo $lang; ?>'><?php echo getLanguageNameFromCode($lang,false); ?></option>
                <?php } ?>
        </select>
        <?php break;
        case "P": //Multiple choice with comments checkbox + text ?>
        <table border='0'>

            <?php foreach ($mearesult as $mearow)
                { ?>
                <tr>
                    <td>
                        <input type='checkbox' class='checkboxbtn' name='<?php echo $fieldname.$mearow['title']; ?>' value='Y'
                            /><?php echo $mearow['question']; ?>
                    </td>

                    <td>
                        <input type='text' name='<?php echo $fieldname.$mearow['title']; ?>comment' size='50' />
                    </td>
                </tr>
                <?php }
                if ($deqrow['other'] == "Y")
                { ?>
                <tr>
                    <td  align='left'><label><?php echo $blang->gT("Other"); ?>:</label>
                        <input type='text' name='$fieldname"."other' size='10'/>
                    </td>
                    <td align='left'>
                        <input type='text' name='<?php echo $fieldname; ?>othercomment' size='50'/>
                    </td>
                </tr>
                <?php } ?>
        </table>
        <?php break;
        case "|": ?>

        <script type='text/javascript'>

            function updateJSON<?php echo $fieldname; ?>() {

                var jsonstr = '[';
                var i;
                var filecount = 0;

                for (i = 0; i < <?php echo $qidattributes['max_num_of_files']; ?>; i++)
                {
                    if ($('#<?php echo $fieldname; ?>_file_'+i).val() != '')
                        {

                        <?php if ($qidattributes['show_title']) { ?>
                            jsonstr += '{ "title":"'+$('#<?php echo $fieldname; ?>_title_'+i).val()+'",';
                            <?php } else { ?>
                            jsonstr += '{ "title":"",';

                            <?php } ?>
                        <?php if ($qidattributes['show_comment']) { ?>
                            jsonstr += '"comment":"'+$('#<?php echo $fieldname; ?>_comment_'+i).val()+'",';
                            <?php } else { ?>
                            jsonstr += '"comment":"",';
                            <?php } ?>
                        jsonstr += '"name":"'+$('#<?php echo$fieldname; ?>_file_'+i).val()+'"}';

                        jsonstr += ',';
                        filecount++;
                    }
                }

                if (jsonstr.charAt(jsonstr.length - 1) == ',')
                    jsonstr = jsonstr.substring(0, jsonstr.length - 1);

                jsonstr += ']';
                $('#<?php echo $fieldname ; ?>').val(jsonstr);
                $('#<?php echo $fieldname; ?>_filecount').val(filecount);
            }
        </script>

        <table border='0'>


            <?php if ($qidattributes['show_title'] && $qidattributes['show_title']) { ?>
                <tr><th>Title</th><th>Comment</th>
                <?php } else if ($qidattributes['show_title']) { ?>
                    <tr><th>Title</th>
                    <?php } else if ($qidattributes['show_comment']) { ?>
                        <tr><th>Comment</th>
                            <?php } ?>

                <th>Select file</th></tr>

            <?php for ($i = 0; $i < $maxfiles; $i++)
                { ?>
                <tr>
                    <?php if ($qidattributes['show_title'])  ?>
                    <td align='center'><input type='text' id='<?php echo $fieldname; ?>_title_<?php echo $i; ?>' maxlength='100' onChange='updateJSON<?php echo $fieldname; ?>()' /></td>

                    <?php if ($qidattributes['show_comment']) ?>
                    <td align='center'><input type='text' id='<?php echo $fieldname; ?>_comment_<?php echo $i; ?>' maxlength='100' onChange='updateJSON<?php echo $fieldname; ?>()' /></td>

                    <td align='center'><input type='file' name='<?php echo $fieldname; ?>_file_<?php echo $i; ?>' id='<?php echo $fieldname; ?>_file_<?php echo $i; ?>' onChange='updateJSON<?php echo $fieldname; ?>()' /></td></tr>
                <?php } ?>
            <tr><td align='center'><input type='hidden' name='<?php echo $fieldname; ?>' id='<?php echo $fieldname; ?>' value='' /></td></tr>
            <tr><td align='center'><input type='hidden' name='<?php echo $fieldname; ?>_filecount' id='<?php echo $fieldname; ?>_filecount' value='' /></td></tr>
        </table>
        <?php break;
        case "N": //NUMERICAL TEXT
            if (isset($qidattributes['prefix']) && trim($qidattributes['prefix'][$sDataEntryLanguage]) != '') {
                $prefix = $qidattributes['prefix'][$sDataEntryLanguage];
            } else {
                $prefix = '';
            }

            if (isset($qidattributes['suffix']) && trim($qidattributes['suffix'][$sDataEntryLanguage]) != '') {
                $suffix = $qidattributes['suffix'][$sDataEntryLanguage];
            } else {
                $suffix = '';
            }

            if (intval(trim($qidattributes['maximum_chars'])) > 0 && intval(trim($qidattributes['maximum_chars'])) < 20) { // Limt to 20 chars for numeric
                $maximum_chars = intval(trim($qidattributes['maximum_chars']));
                $maxlength = "maxlength='{$maximum_chars}' ";
            } else {
                $maxlength = "maxlength='20' ";
            }

            if (trim($qidattributes['text_input_width']) != '') {
                $tiwidth = $qidattributes['text_input_width'];
            } else {
                $tiwidth = 10;
            }

            if (trim($qidattributes['num_value_int_only']) == 1) {
                $acomma = "";
            } else {
                $acomma = getRadixPointData($thissurvey['surveyls_numberformat']);
                $acomma = $acomma['separator'];
            }
            $title = $clang->gT('Only numbers may be entered in this field.');

            echo $prefix; ?><input type='text' name='<?php echo $fieldname; ?>' size='<?php echo $tiwidth; ?>' title='<?php echo $title; ?>' <?php echo $maxlength; ?> onkeypress="return goodchars(event,'-0123456789<?php echo $acomma; ?>')" /><?php echo $suffix;
            break;

        case "S": //SHORT FREE TEXT
            if (isset($qidattributes['prefix']) && trim($qidattributes['prefix'][$sDataEntryLanguage]) != '') {
                $prefix = $qidattributes['prefix'][$sDataEntryLanguage];
            } else {
                $prefix = '';
            }

            if (isset($qidattributes['suffix']) && trim($qidattributes['suffix'][$sDataEntryLanguage]) != '') {
                $suffix = $qidattributes['suffix'][$sDataEntryLanguage];
            } else {
                $suffix = '';
            }

            if (intval(trim($qidattributes['maximum_chars'])) > 0 && intval(trim($qidattributes['maximum_chars'])) < 4000) { // Limit to 4000 to maintain compatibility
                $maximum_chars = intval(trim($qidattributes['maximum_chars']));
                $maxlength = "maxlength='{$maximum_chars}' ";
            } else {
                $maxlength = "maxlength='4000' "; // Default to 4000 chars if not set within limits
            }

            if (trim($qidattributes['text_input_width']) != '') {
                $tiwidth = $qidattributes['text_input_width'];
            } else {
                $tiwidth = 50;
            }

            if ($qidattributes['numbers_only']==1)
            {
                $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
                $sSeparator = $sSeparator['separator'];
                $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeparator.'\')"';
            }
            else
            {
                $numbersonly = '';
            }

            if (trim($qidattributes['display_rows'])!='')
            {
                //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
                $drows=$qidattributes['display_rows'];

                //if a textarea should be displayed we make it equal width to the long text question
                //this looks nicer and more continuous
                if($tiwidth == 50)
                {
                    $tiwidth=40;
                }
                echo $prefix; ?><textarea name='<?php echo $fieldname; ?>' cols='<?php echo $tiwidth; ?>' rows='<?php echo $drows; ?>' <?php echo $numbersonly; ?>></textarea><?php echo $suffix;
            } else {
                echo $prefix; ?><input type='text' name='<?php echo $fieldname; ?>' size='<?php echo $tiwidth; ?>' <?php echo $maxlength . ' ' . $numbersonly; ?> /><?php echo $suffix;
            }
        ?>
        <?php break;
        case "T": //LONG FREE TEXT
            if (trim($qidattributes['display_rows'])!='')
            {
                $drows=$qidattributes['display_rows'];
            } else {
                $drows = 5;
            }

            if (trim($qidattributes['text_input_width']) != '') {
                $tiwidth = $qidattributes['text_input_width'];
            } else {
                $tiwidth = 40;
            }

            if (isset($qidattributes['prefix']) && trim($qidattributes['prefix'][$sDataEntryLanguage]) != '') {
                $prefix = $qidattributes['prefix'][$sDataEntryLanguage];
            } else {
                $prefix = '';
            }

            if (isset($qidattributes['suffix']) && trim($qidattributes['suffix'][$sDataEntryLanguage]) != '') {
                $suffix = $qidattributes['suffix'][$sDataEntryLanguage];
            } else {
                $suffix = '';
            }
            echo $prefix; ?><textarea name='<?php echo $fieldname; ?>' cols='<?php echo $tiwidth; ?>' rows='<?php echo $drows; ?>'></textarea><?php echo $suffix;
            break;

        case "U": //HUGE FREE TEXT
            if (trim($qidattributes['display_rows'])!='')
            {
                $drows=$qidattributes['display_rows'];
            } else {
                $drows = 70;
            }

            if (trim($qidattributes['text_input_width']) != '') {
                $tiwidth = $qidattributes['text_input_width'];
            } else {
                $tiwidth = 50;
            }

            if (isset($qidattributes['prefix']) && trim($qidattributes['prefix'][$sDataEntryLanguage]) != '') {
                $prefix = $qidattributes['prefix'][$sDataEntryLanguage];
            } else {
                $prefix = '';
            }

            if (isset($qidattributes['suffix']) && trim($qidattributes['suffix'][$sDataEntryLanguage]) != '') {
                $suffix = $qidattributes['suffix'][$sDataEntryLanguage];
            } else {
                $suffix = '';
            }
            echo $prefix; ?><textarea name='<?php echo $fieldname; ?>' cols='<?php echo $tiwidth; ?>' rows='<?php echo $drows; ?>'></textarea><?php echo $suffix;
            break;

        case "Y": //YES/NO radio-buttons
        ?>
        <select name='<?php echo $fieldname; ?>'>
            <option selected='selected' value=''><?php echo $blang->gT("Please choose"); ?>..</option>
            <option value='Y'><?php echo $blang->gT("Yes"); ?></option>
            <option value='N'><?php echo $blang->gT("No"); ?></option>
        </select>
        <?php break;
        case "A": //ARRAY (5 POINT CHOICE) radio-buttons ?>

        <table>
            <?php foreach ($mearesult as $mearow)
                { ?>
                <tr>
                    <td align='right'><?php echo $mearow['question']; ?></td>
                    <td>
                        <select name='<?php echo $fieldname.$mearow['title']; ?>'>
                            <option value=''><?php echo $blang->gT("Please choose"); ?>..</option>
                            <?php for ($i=1; $i<=5; $i++)
                                { ?>
                                <option value='<?php echo $i; ?>'><?php echo $i; ?></option>
                                <?php } ?>
                        </select>
                    </td>
                </tr>
                <?php } ?>
        </table>
        <?php break;
        case "B": //ARRAY (10 POINT CHOICE) radio-buttons ?>
        <table>
            <?php foreach ($mearesult as $mearow)
                { ?>
                <tr>
                    <td align='right'><?php echo $mearow['question']; ?></td>
                    <td>
                        <select name='<?php echo $fieldname.$mearow['title']; ?>'>
                            <option value=''><?php echo $blang->gT("Please choose"); ?>..</option>
                            <?php for ($i=1; $i<=10; $i++)
                                { ?>
                                <option value='<?php echo $i; ?>'><?php echo $i; ?></option>
                                <?php } ?>
                        </select>
                    </td>
                </tr>
                <?php } ?>
        </table>
        <?php break;
        case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
        ?>
        <table>
            <?php foreach ($mearesult as $mearow)
                { ?>
                <tr>
                    <td align='right'><?php echo $mearow['question']; ?></td>
                    <td>
                        <select name='<?php echo $fieldname.$mearow['title']; ?>'>
                            <option value=''><?php echo $blang->gT("Please choose"); ?>..</option>
                            <option value='Y'><?php echo $blang->gT("Yes"); ?></option>
                            <option value='U'><?php echo $blang->gT("Uncertain"); ?></option>
                            <option value='N'><?php echo $blang->gT("No"); ?></option>
                        </select>
                    </td>
                </tr>
                <?php } ?>
        </table>
        <?php break;
        case "E": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
        ?> <table>
            <?php foreach ($mearesult as $mearow)
                { ?>
                <tr>
                    <td align='right'><?php echo $mearow['question']; ?></td>
                    <td>
                        <select name='<?php echo $fieldname.$mearow['title']; ?>'>
                            <option value=''><?php echo $blang->gT("Please choose"); ?>..</option>
                            <option value='I'><?php echo $blang->gT("Increase"); ?></option>
                            <option value='S'><?php echo $blang->gT("Same"); ?></option>
                            <option value='D'><?php echo $blang->gT("Decrease"); ?></option>
                        </select>
                    </td>
                </tr>
                <?php } ?>
        </table>
        <?php break;
        case ":": //ARRAY (Multi Flexi)
            $labelcodes=array();
        ?>
        <table>
            <tr><td></td>
                <?php foreach($lresult as $data)
                    { ?>
                    <th><?php echo $data['question']; ?></th>
                    <?php $labelcodes[]=$data['title'];
                    }
                ?>
            </tr>
            <?php $i=0;
                foreach ($mearesult as $mearow)
                {

                    if (strpos($mearow['question'],'|'))
                    {
                        $answerleft=substr($mearow['question'],0,strpos($mearow['question'],'|'));
                        $answerright=substr($mearow['question'],strpos($mearow['question'],'|')+1);
                    }
                    else
                    {
                        $answerleft=$mearow['question'];
                        $answerright='';
                } ?>

                <tr>
                    <td align='right'><?php echo $answerleft; ?></td>
                    <?php foreach($labelcodes as $ld)
                        { ?>
                        <td>
                            <?php if ($qidattributes['input_boxes']!=0) { ?>
                                <input type='text' name='<?php echo $fieldname.$mearow['title']."_".$ld;?>' size=4 />
                                <?php } else { ?>
                                <select name='<?php echo $fieldname.$mearow['title']."_$ld"; ?>'>
                                    <option value=''>...</option>
                                    <?php for($ii=$minvalue;$ii<=$maxvalue;$ii+=$stepvalue)
                                        { ?>
                                        <option value='<?php echo $ii; ?>'><?php echo $ii; ?></option>
                                        <?php } ?>
                                </select>
                                <?php } ?>
                        </td>
                        <?php } ?>
                </tr>
                <?php $i++;
                }
                $i--; ?>
        </table>
        <?php break;
        case ";": //ARRAY (Multi Flexi) ?>
        <table>
            <tr><td></td>
                <?php $labelcodes=array();
                    foreach ($lresult as $data)
                    { ?>
                    <th><?php echo $data['question']; ?></th>
                    <?php $labelcodes[]=$data['title'];
                } ?>

            </tr>

            <?php $i=0;
                foreach ($mearesult as $mearow)
                {
                    if (strpos($mearow['question'],'|'))
                    {
                        $answerleft=substr($mearow['question'],0,strpos($mearow['question'],'|'));
                        $answerright=substr($mearow['question'],strpos($mearow['question'],'|')+1);
                    }
                    else
                    {
                        $answerleft=$mearow['question'];
                        $answerright='';
                    }
                ?>
                <tr>
                    <td align='right'><?php echo $answerleft; ?></td>
                    <?php foreach($labelcodes as $ld)
                        { ?>
                        <td>
                            <input type='text' name='<?php echo $fieldname.$mearow['title']."_$ld"; ?>' />
                        </td>
                        <?php } ?>
                </tr>
                <?php $i++;
                }
                $i--; ?>
        </table>
        <?php break;
        case "F": //ARRAY (Flexible Labels)
        case "H": ?>
        <table>
            <?php  foreach ( $mearesult as $mearow)
                {

                    if (strpos($mearow['question'],'|'))
                    {
                        $answerleft=substr($mearow['question'],0,strpos($mearow['question'],'|'));
                        $answerright=substr($mearow['question'],strpos($mearow['question'],'|')+1);
                    }
                    else
                    {
                        $answerleft=$mearow['question'];
                        $answerright='';
                    }
                ?>

                <tr>
                    <td align='right'><?php echo $answerleft; ?></td>
                    <td>
                        <select name='<?php echo $fieldname.$mearow['title']; ?>'>
                            <option value=''><?php echo $blang->gT("Please choose"); ?>..</option>

                            <?php foreach ($fresult as $frow)
                                { ?>
                                <option value='<?php echo $frow['code']; ?>'><?php echo $frow['answer']; ?></option>
                                <?php } ?>
                        </select>
                    </td>
                    <td align='left'><?php echo $answerright; ?></td>
                </tr>
                <?php } ?>
        </table>
        <?php break;
} ?>
                   </td>
                   </tr>
                   <tr class='data-entry-separator'><td colspan='3'></td></tr>
