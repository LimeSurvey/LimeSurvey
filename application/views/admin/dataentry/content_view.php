<?php
/**
 * @var $this AdminController
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('dataEntryView');

?>

<!-- content_view.php -->

<!-- explanation -->
<?php if (!empty($explanation)): ?>
    <tr class ='data-entry-explanation text-info'><td class='data-entry-small-text' colspan='3' align='left'><?php echo $explanation; ?></td></tr>
<?php endif; ?>

<!-- questions -->
<tr class='<?php echo $bgc; ?>'>


    <!-- question title -->
    <td class='data-entry-small-text' valign='top' width='1%'>
        <?php echo $deqrow['title']; ?>
    </td>


    <!-- question text -->
    <td valign='top' align='right' width='30%'>
        <!-- mandatory -->
        <?php if ($deqrow['mandatory']=="Y"):?>
            <span class="text-danger">*</span>
        <?php endif; ?>

        <!-- question text -->
        <strong>
            <?php echo $deqrow->questionl10ns[$sDataEntryLanguage]->question;   // don't flatten if want to use EM.  However, may not be worth it as want dynamic relevance and question changes?>
        </strong>
    </td>

    <!-- Answers -->
    <td valign='top'  align='left' style='padding-left: 20px'>
        <?php switch($deqrow['type'])
        {


            //5 POINT CHOICE radio-buttons
            case Question::QT_5_POINT_CHOICE: ?>
            <div class="col-md-10">
                <select name='<?php echo $fieldname; ?>' class='form-select'>
                    <option value=''><?php eT("No answer",'html',$sDataEntryLanguage); ?></option>
                    <?php for ($x=1; $x<=5; $x++)
                    { ?>
                        <option value='<?php echo $x; ?>'><?php echo $x; ?></option>
                    <?php } ?>
                </select>
            </div>
            <?php break;


            //DATE
            case Question::QT_D_DATE:
                $dateformatdetails = getDateFormatDataForQID($qidattributes, $thissurvey);
                ?>
            <div class="col-md-10 has-feedback">
                <?php if(canShowDatePicker($dateformatdetails)): ?>
                    <?php Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                        'name' => $fieldname,
                        'id' => 'q_date_' . $fieldname,
                        'pluginOptions' => array(
                            'format' => $dateformatdetails['jsdate'] . " HH:mm",
                            'allowInputToggle' =>true,
                            'showClear' => true,
                            'theme' => 'light',
                            'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                        )
                    )); ?>
                    <input type='hidden' name='dateformat<?php echo $fieldname; ?>' id='dateformat<?php echo $fieldname; ?>' value='<?php echo $dateformatdetails['jsdate']; ?>'  />
                <?php else:?>
                    <input type='text' name='<?php echo $fieldname; ?>'/>
                <?php endif; ?>
            </div>
            <?php break;


            //GENDER drop-down list
            case Question::QT_G_GENDER: ?>
            <div class="col-md-10">
                <select name='<?php echo $fieldname; ?>'  class='form-select'>
                    <option selected='selected' value=''><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option>
                    <option value='F'><?php eT("Female",'html',$sDataEntryLanguage); ?></option>
                    <option value='M'><?php eT("Male",'html',$sDataEntryLanguage); ?></option>
                </select>
            </div>
            <?php break;


            //Multiple short text
            case Question::QT_Q_MULTIPLE_SHORT_TEXT:
            case Question::QT_K_MULTIPLE_NUMERICAL: ?>
            <div class="col-md-10">
                <table>
                    <?php foreach ($dearesult as $dearow):?>
                        <tr>
                            <td align='right'>
                                <?php echo $dearow->questionl10ns[$sDataEntryLanguage]->question; ?>
                            </td>
                            <td>
                                <input type='text' name='<?php echo $fieldname.$dearow['title']; ?>' />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php break;


            // Dual scale
            case Question::QT_1_ARRAY_DUAL: ?>
            <div class="col-md-10">
                <table>
                    <tr>
                        <th></th>
                        <th>
                            <?php echo sprintf(gT('Label %s'),'1').'</th><th>'.sprintf(gT('Label %s'),'2'); ?>
                        </th>
                    </tr>

                    <?php foreach ($dearesult as $dearow):?>
                        <?php
                            // first scale
                            $delresult = Answer::model()->findAll("qid={$deqrow['qid']} and scale_id=0");
                        ?>
                        <tr>
                            <td><?php echo $dearow->questionl10ns[$sDataEntryLanguage]->question; ?></td>
                            <td>
                                <div class="col-md-10">
                                    <select name='<?php echo $fieldname.$dearow['title']; ?>#0'  class='form-select'>
                                        <option selected='selected' value=''><?php eT("Please choose..."); ?></option>
                                        <?php foreach ($delresult as $delrow): ?>
                                            <option value='<?php echo $delrow['code']; ?>'><?php echo $delrow->answerl10ns[$sDataEntryLanguage]->answer; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                            <?php $delresult = Answer::model()->findAll("qid={$deqrow['qid']} and scale_id=1"); ?>
                            <td>
                                <div class="col-md-10">
                                    <select name='<?php echo $fieldname.$dearow['title']; ?>#1'  class='form-select'>
                                        <option selected='selected' value=''><?php eT("Please choose..."); ?></option>
                                        <?php foreach ($delresult as $delrow)
                                        { ?>
                                            <option value='<?php echo $delrow['code']; ?>'><?php echo $delrow->answerl10ns[$sDataEntryLanguage]->answer; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                    <?php endforeach;?>

                    <!-- other -->
                    <?php if ($fother == "Y"): ?>
                        <option value='-oth-'><?php eT("Other"); ?></option>
                    <?php endif; ?>

                    <?php if ($fother == "Y"): ?>
                        <?php eT("Other"); ?>:
                        <input type='text' name='<?php echo $fieldname; ?>other' value='' />
                    <?php endif; ?>
                    </tr>
                </table>
            </div>
        <?php break;


        //LIST drop-down/radio-button list
        case Question::QT_L_LIST:
        case Question::QT_EXCLAMATION_LIST_DROPDOWN: ?>
        <div class="col-md-10">
            <select name='<?php echo $fieldname; ?>'  class='form-select'>
                <?php if ($defexists=="") { ?>
                    <option selected='selected' value=''><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option><?php echo $datatemp; }
                    else  { echo $datatemp;} ?>

                    <?php if ($fother == "Y")
                    { ?>
                        <option value='-oth-'><?php eT("Other",'html',$sDataEntryLanguage); ?></option>
                        <?php } ?>
                    </select>
        </div>
        <?php if ($fother == "Y")
            { ?>
            <div class="col-md-10">
            <?php eT("Other",'html',$sDataEntryLanguage); ?>:
            <input type='text' name='<?php echo $fieldname; ?>other' value='' />
            </div>
            <?php }
            break;


        //LIST WITH COMMENT drop-down/radio-button list + textarea
        case Question::QT_O_LIST_WITH_COMMENT:  ?>
        <div class="col-md-10">
            <select name='<?php echo $fieldname; ?>'  class='form-select'>
                <?php if ($defexists=="") { ?>
                    <option selected='selected' value=''><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option><?php echo $datatemp; }
                    else  { echo $datatemp;} ?>
            </select>
        </div>
        <div class="col-md-10">
            <?php eT("Comment"); ?>:<br />
            <textarea cols='40' rows='5' name='<?php echo $fieldname; ?>comment'></textarea>
        </div>
        <?php break;?>

        <?php case "*":?>
            <input type="text" name="<?php echo $fieldname; ?>" value="">
        <?php break;


        // Ranking TYPE QUESTION
        case Question::QT_R_RANKING: ?>
        <div class="col-md-10">
        <div id="question<?php echo $thisqid ?>" class="ranking-answers">
            <ul class="answers-list list-unstyled">
                <?php for ($i=1; $i<=$anscount; $i++)
            {
            ?>
            <li class="select-item">
            <?php
                if($i==1){
                    eT('First choice','html',$sDataEntryLanguage);
                }else{
                    eT('Next choice','html',$sDataEntryLanguage);
                }
            ?>
            <select name="<?php echo $fieldname.$i ?>"  class='form-select' id="answer<?php echo $fieldname.$i ?>">";
                <option value=""><?php eT('None','html',$sDataEntryLanguage) ?></option>
                <?php
                    foreach ($answers as $ansrow)
                    {
                        echo "\t<option value=\"".$ansrow['code']."\">".flattenText($ansrow->answerl10ns[$sDataEntryLanguage]->answer)."</option>\n";
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
            echo "<div id=\"htmlblock-{$thisqid}-{$ansrow['code']}\">{$ansrow->answerl10ns[$sDataEntryLanguage]->answer}</div>";
        }
        ?>
        </div>
        <script type='text/javascript'>
            <!--
            var aRankingTranslations = {
                choicetitle: '<?php echo gT("Available items",'js') ?>',
                ranktitle: '<?php echo gT("Your ranking",'js') ?>'
            };
            function checkconditions(){
                // Some space so the EM won't kick in
            };
            $(function() {
                doDragDropRank(<?php echo $thisqid ?>,0,true,true);
            });
            -->
        </script>
        </div></div>
        <?php
            break;


        //Multiple choice checkbox (Quite tricky really!)
        case Question::QT_M_MULTIPLE_CHOICE: ?>
        <div class="col-md-10">
            <?php
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
                            <label for='answer<?php echo $fieldname.$mearow['title']; ?>'><?php echo $mearow->questionl10ns[$sDataEntryLanguage]->question; ?></label><br />
                            <?php $upto++; ;
                            }
                            if ($deqrow['other'] == "Y")
                            { ?>
                            <?php eT("Other",'html',$sDataEntryLanguage); ?> <input type='text' name='<?php echo $fieldname; ?>other' />
                            <?php } ?>
                    </td></tr></table>

            <?php } else {
                foreach ($mearesult as $mearow) { ?>
                <input type='checkbox' class='checkboxbtn' name='<?php echo $fieldname.$mearow['title']; ?>' id='answer<?php echo $fieldname.$mearow['title']; ?>' value='Y'
                    /><label for='<?php $fieldname.$mearow['title']; ?>'><?php echo $mearow->questionl10ns[$sDataEntryLanguage]->question; ?></label><br />
                <?php }
                if ($deqrow['other'] == "Y")
                { ?>
                <?php eT("Other",'html',$sDataEntryLanguage); ?> <input type='text' name='<?php echo $fieldname; ?>other' />
                <?php }
            }?>
        </div><?php
            break;


        //Language Switch
        case Question::QT_I_LANGUAGE:  ?>
        <div class="col-md-10">
            <select name='<?php echo $fieldname; ?>'  class='form-select'>
                <option value='' selected='selected'><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option>

                <?php foreach ($slangs as $lang)
                    { ?>
                        <option value='<?php echo $lang; ?>'><?php echo getLanguageNameFromCode($lang,false); ?></option>
                <?php } ?>
            </select>
        </div>
        <?php break;


        //Multiple choice with comments checkbox + text
        case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:  ?>
        <div class="col-xl-10">
            <table border='0'>
                <?php foreach ($mearesult as $mearow)
                    { ?>
                        <tr>
                            <td>
                                <input type='checkbox' class='checkboxbtn' name='<?php echo $fieldname.$mearow['title']; ?>' value='Y'
                                /><?php echo $mearow->questionl10ns[$sDataEntryLanguage]->question; ?>
                            </td>

                            <td>
                                <input type='text' name='<?php echo $fieldname.$mearow['title']; ?>comment' size='50' />
                            </td>
                        </tr>
                        <?php }
                        if ($deqrow['other'] == "Y")
                        { ?>
                            <tr>
                                <td  align='left'><label><?php eT("Other",'html',$sDataEntryLanguage); ?>:</label>
                                    <input type='text' name='$fieldname"."other' size='10'/>
                                </td>
                                <td align='left'>
                                    <input type='text' name='<?php echo $fieldname; ?>othercomment' size='50'/>
                                </td>
                            </tr>
                            <?php } ?>
            </table>
        </div>
        <?php break;
        case Question::QT_VERTICAL_FILE_UPLOAD: ?>

        <div class="col-md-10">
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
        </div>
        <?php break;


        //NUMERICAL TEXT
        case Question::QT_N_NUMERICAL: ?>
            <div class="col-md-10">
            <?php
            if (isset($qidattributes['prefix']) && trim((string) $qidattributes['prefix'][$sDataEntryLanguage]) != '') {
                $prefix = $qidattributes['prefix'][$sDataEntryLanguage];
            } else {
                $prefix = '';
            }

            if (isset($qidattributes['suffix']) && trim((string) $qidattributes['suffix'][$sDataEntryLanguage]) != '') {
                $suffix = $qidattributes['suffix'][$sDataEntryLanguage];
            } else {
                $suffix = '';
            }

            if (intval(trim((string) $qidattributes['maximum_chars'])) > 0 && intval(trim((string) $qidattributes['maximum_chars'])) < 20) { // Limt to 20 chars for numeric
                $maximum_chars = intval(trim((string) $qidattributes['maximum_chars']));
                $maxlength = "maxlength='{$maximum_chars}' ";
            } else {
                $maxlength = "maxlength='20' ";
            }

            if (trim((string) $qidattributes['text_input_width']) != '') {
                $tiwidth = $qidattributes['text_input_width'];
            } else {
                $tiwidth = 10;
            }

            if (trim((string) $qidattributes['num_value_int_only']) == 1) {
                $acomma = "";
            } else {
                $acomma = getRadixPointData($thissurvey['surveyls_numberformat']);
                $acomma = $acomma['separator'];
            }
            $title = gT('Only numbers may be entered in this field.');

            echo $prefix; ?><input type='text' name='<?php echo $fieldname; ?>' size='<?php echo $tiwidth; ?>' title='<?php echo $title; ?>' <?php echo $maxlength; ?> onkeypress="return window.LS.goodchars(event,'-0123456789<?php echo $acomma; ?>')" /><?php echo $suffix;
            echo '</div>';
            break;

        case Question::QT_S_SHORT_FREE_TEXT: //Short free text
            ?>
            <div class="col-md-10">
            <?php
            if (isset($qidattributes['prefix']) && trim((string) $qidattributes['prefix'][$sDataEntryLanguage]) != '') {
                $prefix = $qidattributes['prefix'][$sDataEntryLanguage];
            } else {
                $prefix = '';
            }

            if (isset($qidattributes['suffix']) && trim((string) $qidattributes['suffix'][$sDataEntryLanguage]) != '') {
                $suffix = $qidattributes['suffix'][$sDataEntryLanguage];
            } else {
                $suffix = '';
            }

            if (intval(trim((string) $qidattributes['maximum_chars'])) > 0 && intval(trim((string) $qidattributes['maximum_chars'])) < 4000) { // Limit to 4000 to maintain compatibility
                $maximum_chars = intval(trim((string) $qidattributes['maximum_chars']));
                $maxlength = "maxlength='{$maximum_chars}' ";
            } else {
                $maxlength = "maxlength='4000' "; // Default to 4000 chars if not set within limits
            }

            if (trim((string) $qidattributes['text_input_width']) != '') {
                $tiwidth = $qidattributes['text_input_width'];
            } else {
                $tiwidth = 50;
            }

            if ($qidattributes['numbers_only']==1)
            {
                $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
                $sSeparator = $sSeparator['separator'];
                $numbersonly = 'onkeypress="return window.LS.goodchars(event,\'-0123456789'.$sSeparator.'\')"';
            }
            else
            {
                $numbersonly = '';
            }

            if (trim((string) $qidattributes['display_rows'])!='')
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
        </div>
        <?php break;


        //LONG FREE TEXT
        case Question::QT_T_LONG_FREE_TEXT:
        ?>
        <div class="col-md-10">
        <?php
            if (trim((string) $qidattributes['display_rows'])!='')
            {
                $drows=$qidattributes['display_rows'];
            } else {
                $drows = 5;
            }

            if (trim((string) $qidattributes['text_input_width']) != '') {
                $tiwidth = $qidattributes['text_input_width'];
            } else {
                $tiwidth = 40;
            }

            if (isset($qidattributes['prefix']) && trim((string) $qidattributes['prefix'][$sDataEntryLanguage]) != '') {
                $prefix = $qidattributes['prefix'][$sDataEntryLanguage];
            } else {
                $prefix = '';
            }

            if (isset($qidattributes['suffix']) && trim((string) $qidattributes['suffix'][$sDataEntryLanguage]) != '') {
                $suffix = $qidattributes['suffix'][$sDataEntryLanguage];
            } else {
                $suffix = '';
            }
            echo $prefix; ?><textarea name='<?php echo $fieldname; ?>' cols='<?php echo $tiwidth; ?>' rows='<?php echo $drows; ?>'></textarea>
            <?php echo $suffix;?>
            </div>
            <?php
            break;

        case Question::QT_U_HUGE_FREE_TEXT: //Huge free text
            if (trim((string) $qidattributes['display_rows'])!='')
            {
                $drows=$qidattributes['display_rows'];
            } else {
                $drows = 70;
            }

            if (trim((string) $qidattributes['text_input_width']) != '') {
                $tiwidth = $qidattributes['text_input_width'];
            } else {
                $tiwidth = 50;
            }

            if (isset($qidattributes['prefix']) && trim((string) $qidattributes['prefix'][$sDataEntryLanguage]) != '') {
                $prefix = $qidattributes['prefix'][$sDataEntryLanguage];
            } else {
                $prefix = '';
            }

            if (isset($qidattributes['suffix']) && trim((string) $qidattributes['suffix'][$sDataEntryLanguage]) != '') {
                $suffix = $qidattributes['suffix'][$sDataEntryLanguage];
            } else {
                $suffix = '';
            }
            echo $prefix; ?><textarea name='<?php echo $fieldname; ?>' cols='<?php echo $tiwidth; ?>' rows='<?php echo $drows; ?>'></textarea><?php echo $suffix;
            break;

        case Question::QT_Y_YES_NO_RADIO: //YES/NO radio-buttons
        ?>
        <div class="col-md-10">
            <select name='<?php echo $fieldname; ?>'  class='form-select'>
                <option selected='selected' value=''><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option>
                <option value='Y'><?php eT("Yes",'html',$sDataEntryLanguage); ?></option>
                <option value='N'><?php eT("No",'html',$sDataEntryLanguage); ?></option>
            </select>
        </div>
        <?php break;


        // Array (5 point choice) radio-buttons
        case Question::QT_A_ARRAY_5_POINT: ?>

        <div class="col-md-10">
            <table>
                <?php foreach ($mearesult as $mearow)
                    { ?>
                        <tr>
                            <td align='right'><?php echo $mearow->questionl10ns[$sDataEntryLanguage]->question; ?></td>
                            <td>
                                <select name='<?php echo $fieldname.$mearow['title']; ?>' class='form-select'>
                                    <option value=''><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option>
                                    <?php for ($i=1; $i<=5; $i++)
                                    { ?>
                                        <option value='<?php echo $i; ?>'><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                <?php } ?>
            </table>
        </div>
        <?php break;


        // Array (10 point choice) radio-buttons
        case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:  ?>
        <div class="col-md-10">
        <table>
            <?php foreach ($mearesult as $mearow)
                { ?>
                <tr>
                    <td align='right'><?php echo $mearow->questionl10ns[$sDataEntryLanguage]->question; ?></td>
                    <td>
                        <select name='<?php echo $fieldname.$mearow['title']; ?>'  class='form-select'>
                            <option value=''><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option>
                            <?php for ($i=1; $i<=10; $i++)
                                { ?>
                                <option value='<?php echo $i; ?>'><?php echo $i; ?></option>
                                <?php } ?>
                        </select>
                    </td>
                </tr>
                <?php } ?>
        </table>
        </div>
        <?php break;


        // Array (Yes/Uncertain/No)
        case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
        ?>
        <div class="col-md-10">
        <table>
            <?php foreach ($mearesult as $mearow)
                { ?>
                <tr>
                    <td align='right'><?php echo $mearow->questionl10ns[$sDataEntryLanguage]->question; ?></td>
                    <td>
                        <select name='<?php echo $fieldname.$mearow['title']; ?>'  class='form-select'>
                            <option value=''><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option>
                            <option value='Y'><?php eT("Yes",'html',$sDataEntryLanguage); ?></option>
                            <option value='U'><?php eT("Uncertain",'html',$sDataEntryLanguage); ?></option>
                            <option value='N'><?php eT("No",'html',$sDataEntryLanguage); ?></option>
                        </select>
                    </td>
                </tr>
                <?php } ?>
        </table>
        </div>


        <?php
        // Array (Yes/Uncertain/No)
        break;
        case Question::QT_E_ARRAY_INC_SAME_DEC:
        ?>
        <div class="col-md-10">
            <table>
            <?php foreach ($mearesult as $mearow)
                { ?>
                <tr>
                    <td align='right'><?php echo $mearow->questionl10ns[$sDataEntryLanguage]->question; ?></td>
                    <td>
                        <select name='<?php echo $fieldname.$mearow['title']; ?>'  class='form-select'>
                            <option value=''><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option>
                            <option value='I'><?php eT("Increase",'html',$sDataEntryLanguage); ?></option>
                            <option value='S'><?php eT("Same",'html',$sDataEntryLanguage); ?></option>
                            <option value='D'><?php eT("Decrease",'html',$sDataEntryLanguage); ?></option>
                        </select>
                    </td>
                </tr>
                <?php } ?>
        </table>
        </div>
        <?php break;


        // Array
        case Question::QT_COLON_ARRAY_NUMBERS:
            $labelcodes=array();
        ?>
        <div class="col-md-10">
        <table>
            <tr><td></td>
                <?php foreach($lresult as $data)
                    { ?>
                    <th><?php echo $data->questionl10ns[$sDataEntryLanguage]->question; ?></th>
                    <?php $labelcodes[]=$data['title'];
                    }
                ?>
            </tr>
            <?php $i=0;
                foreach ($mearesult as $mearow)
                {

                    if (strpos((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,'|'))
                    {
                        $answerleft=substr((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,0,strpos((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,'|'));
                        $answerright=substr((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,strpos((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,'|')+1);
                    }
                    else
                    {
                        $answerleft=$mearow->questionl10ns[$sDataEntryLanguage]->question;
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
                                <select name='<?php echo $fieldname.$mearow['title']."_$ld"; ?>'  class='form-select'>
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
        </div>
        <?php break;


        // Array
        case Question::QT_SEMICOLON_ARRAY_TEXT: ?>
        <div class="col-md-10">
        <table>
            <tr><td></td>
                <?php $labelcodes=array();
                    foreach ($lresult as $data)
                    { ?>
                    <th><?php echo $data->questionl10ns[$sDataEntryLanguage]->question; ?></th>
                    <?php $labelcodes[]=$data['title'];
                } ?>

            </tr>

            <?php $i=0;
                foreach ($mearesult as $mearow)
                {
                    if (strpos((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,'|'))
                    {
                        $answerleft=substr((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,0,strpos((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,'|'));
                        $answerright=substr((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,strpos((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,'|')+1);
                    }
                    else
                    {
                        $answerleft=$mearow->questionl10ns[$sDataEntryLanguage]->question;
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
        </div>
        <?php break;


        // Array (Flexible Labels)
        case Question::QT_F_ARRAY:
        case Question::QT_H_ARRAY_COLUMN: ?>
        <div class="col-md-10">
        <table>
            <?php  foreach ( $mearesult as $mearow)
                {

                    if (strpos((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,'|'))
                    {
                        $answerleft=substr((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,0,strpos((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,'|'));
                        $answerright=substr((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,strpos((string) $mearow->questionl10ns[$sDataEntryLanguage]->question,'|')+1);
                    }
                    else
                    {
                        $answerleft=$mearow->questionl10ns[$sDataEntryLanguage]->question;
                        $answerright='';
                    }
                ?>

                <tr>
                    <td align='right'><?php echo $answerleft; ?></td>
                    <td>
                        <select name='<?php echo $fieldname.$mearow['title']; ?>'  class='form-select'>
                            <option value=''><?php eT("Please choose",'html',$sDataEntryLanguage); ?>..</option>

                            <?php foreach ($fresult as $frow)
                                { ?>
                                <option value='<?php echo $frow['code']; ?>'><?php echo $frow->answerl10ns[$sDataEntryLanguage]->answer; ?></option>
                                <?php } ?>
                        </select>
                    </td>
                    <td align='left'><?php echo $answerright; ?></td>
                </tr>
                <?php } ?>
        </table>
        </div>
        <?php break;
} ?>

<?php if (!empty($deqrow->questionl10ns[$sDataEntryLanguage]->help)): ?>
    <div class="col-md-1">
        <a href="#" onclick="javascript:alert('Question <?php echo $deqrow['title']; ?> Help: <?php echo $hh; ?>')" title="<?php eT('Help about this question','html',$sDataEntryLanguage); ?>" data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="ri-question-fill"></i>
        </a>
    </div>
<?php endif; ?>

               </td>
           </tr>

            <tr class='data-entry-separator'>
                <td colspan='3'>
                </td>
            </tr>
