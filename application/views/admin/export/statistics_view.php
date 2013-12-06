<?php
    if (!$surveyid)
    {
        //need to have a survey id
        echo "<center>You have not selected a survey!</center>";
        exit;
    }
?>
<script type='text/javascript'>
    var graphUrl="<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/graph"); ?>";
    var sStatisticsLanguage="<?php echo $sStatisticsLanguage; ?>";
    var listColumnUrl="<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/listcolumn/surveyid/".$surveyid."/column/"); ?>";
    var showTextInline="<?php echo $showtextinline ?>";
</script>
<?php echo CHtml::form(array("admin/statistics/sa/index/surveyid/{$surveyid}/"), 'post', array('name'=>'formbuilder','#'=>'start'));?>
    <div class='header ui-widget-header header_statistics'>
        <div style='float:right;'><img src='<?php echo $sImageURL; ?>/maximize.png' id='showgfilter' alt='<?php $clang->eT("Maximize"); ?>'/><img src='<?php echo $sImageURL; ?>/minimize.png' id='hidegfilter' alt='<?php $clang->eT("Minimize"); ?>'/></div>
        <?php $clang->eT("General filters"); ?>
    </div>
    <!-- AUTOSCROLLING DIV CONTAINING GENERAL FILTERS -->
    <div id='statisticsgeneralfilters' class='statisticsfilters' <?php if ($filterchoice_state!='' || !empty($summary)) { echo " style='display:none' "; } ?>>

        <div id='statistics_general_filter'>
            <?php
                $error = '';
                if (!function_exists("gd_info")) {
                    $error .= '<br />'.$clang->gT('You do not have the GD Library installed. Showing charts requires the GD library to function properly.');
                    $error .= '<br />'.$clang->gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
                }
                else if (!function_exists("imageftbbox")) {
                    $error .= '<br />'.$clang->gT('You do not have the Freetype Library installed. Showing charts requires the Freetype library to function properly.');
                    $error .= '<br />'.$clang->gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
                }
            ?>
            <fieldset style='clear:both;'>
                <legend><?php $clang->eT("Data selection"); ?></legend>
                <ul>
                    <li>
                        <label for='completionstate'><?php $clang->eT("Include:"); ?> </label>
                        <select name='completionstate' id='completionstate'>
                            <option value='all' <?php echo $selectshow; ?>><?php $clang->eT("All responses"); ?></option>
                            <option value='complete' <?php echo $selecthide; ?> > <?php $clang->eT("Completed responses only"); ?></option>
                            <option value='incomplete' <?php echo $selectinc; ?> > <?php $clang->eT("Incomplete responses only"); ?></option>
                        </select>
                    </li>
                    <li>
                        <label for='viewsummaryall'><?php $clang->eT("View summary of all available fields"); ?></label>
                        <input type='checkbox' id='viewsummaryall' name='viewsummaryall' <?php if (isset($_POST['viewsummaryall'])) { echo "checked='checked'";} ?> />
                    </li>
                    <li id='vertical_slide'>
                        <label id='noncompletedlbl' for='noncompleted' title='<?php $clang->eT("Count stats for each question based only on the total number of responses for which the question was displayed"); ?>'><?php $clang->eT("Subtotals based on displayed questions"); ?></label>
                        <input type='checkbox' id='noncompleted' name='noncompleted' <?php if (isset($_POST['noncompleted'])) {echo "checked='checked'"; } ?> />
                    </li>
                    <?php

                        $language_options="";
                        foreach ($survlangs as $survlang)
                        {
                            $language_options .= "\t<option value=\"{$survlang}\"";
                            if ($sStatisticsLanguage == $survlang)
                            {
                                $language_options .= " selected=\"selected\" " ;
                            }
                            $temp = getLanguageNameFromCode($survlang,true);
                            $language_options .= ">".$temp[1]."</option>\n";

                        }

                    ?>
                    <li>
                        <label for='statlang'><?php $clang->eT("Statistics report language"); ?></label>
                        <select name="statlang" id="statlang"><?php echo $language_options; ?></select>
                    </li>
                </ul>
            </fieldset>

            <fieldset id='left'>
                <legend><?php $clang->eT("Response ID"); ?></legend>
                <ul>
                    <li>
                        <label for='idG'><?php $clang->eT("Greater than:"); ?></label>
                        <input type='text' id='idG' name='idG' size='10' value='<?php if (isset($_POST['idG'])){ echo  sanitize_int($_POST['idG']);} ?>' onkeypress="return goodchars(event,'0123456789')" />
                    </li>
                    <li>
                        <label for='idL'><?php $clang->eT("Less than:"); ?></label>
                        <input type='text' id='idL' name='idL' size='10' value='<?php if (isset($_POST['idL'])) { echo sanitize_int($_POST['idL']);} ?>' onkeypress="return goodchars(event,'0123456789')" />
                    </li>
                </ul>
            </fieldset>

            <input type='hidden' name='summary[]' value='idG' />
            <input type='hidden' name='summary[]' value='idL' />

            <?php

                if (isset($datestamp) && $datestamp == "Y") {?>
                    <fieldset id='right'><legend><?php $clang->eT("Submission date"); ?></legend><ul><li>
                    <label for='datestampE'><?php $clang->eT("Equals:"); ?></label>
                    <?php echo CHtml::textField('datestampE',isset($_POST['datestampE'])?$_POST['datestampE']:'',array('id'=>'datestampE', 'class'=>'popupdate', 'size'=>'12'));?>
                    </li><li><label for='datestampG'><?php $clang->eT("Later than:");?></label>
                    <?php echo CHtml::textField('datestampG',isset($_POST['datestampG'])?$_POST['datestampG']:'',array('id'=>'datestampG', 'class'=>'popupdate', 'size'=>'12'));?>
                    </li><li><label for='datestampL'><?php $clang->eT("Earlier than:");?></label>
                    <?php echo CHtml::textField('datestampL',isset($_POST['datestampL'])?$_POST['datestampL']:'',array('id'=>'datestampL', 'class'=>'popupdate', 'size'=>'12'));?>
                    </li></ul></fieldset>
                    <input type='hidden' name='summary[]' value='datestampE' />
                    <input type='hidden' name='summary[]' value='datestampG' />
                    <input type='hidden' name='summary[]' value='datestampL' />
                    <?php
                }

            ?>

            <fieldset>
                <legend><?php $clang->eT("Output options"); ?></legend>
                <ul>
                    <li>
                        <label for='showtextinline'><?php $clang->eT("Show text responses inline:") ?></label>
                        <input type='checkbox' id='showtextinline' name='showtextinline'<?php if(isset($showtextinline) && $showtextinline == 1) {echo "checked='checked'"; } ?> /><br />
                    </li>
                    <li>
                        <label for='usegraph'><?php $clang->eT("Show graphs"); ?></label>
                        <input type='checkbox' id='usegraph' name='usegraph' <?php if (isset($usegraph) && $usegraph == 1) { echo "checked='checked'"; } ?> /><br />
                        <?php if($error != '') { echo "<span id='grapherror' style='display:none'>$error<hr /></span>"; } ?>
                    </li>

                    <li>
                        <label><?php $clang->eT("Select output format"); ?>:</label>
                        <input type='radio' id="outputtypehtml" name='outputtype' value='html' checked='checked' />
                        <label for='outputtypehtml'>HTML</label>
                        <input type='radio' id="outputtypepdf" name='outputtype' value='pdf' />
                        <label for='outputtypepdf'>PDF</label>
                        <input type='radio' id="outputtypexls" onclick='nographs();' name='outputtype' value='xls' />
                        <label for='outputtypexls'>Excel</label>
                    </li>
                </ul>
            </fieldset>
        </div>
        <p>
            <input type='submit' value='<?php $clang->eT("View statistics"); ?>' />
            <input type='button' value='<?php $clang->eT("Clear"); ?>' onclick="window.open('<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>', '_top')" />
        </p>
    </div>
    <div style='clear: both'></div>
    <div class='header header_statistics'>
        <div style='float:right'><img src='<?php echo $sImageURL; ?>/maximize.png' id='showfilter' alt='<?php $clang->eT("Maximize"); ?>'/><img src='<?php echo $sImageURL; ?>/minimize.png' id='hidefilter' alt='<?php $clang->eT("Minimize"); ?>'/></div>
        <?php $clang->eT("Response filters"); ?>
    </div>
    <!-- AUTOSCROLLING DIV CONTAINING QUESTION FILTERS -->
    <div id='statisticsresponsefilters' class='statisticsfilters scrollheight_400' <?php if ($filterchoice_state!='' || !empty($summary)) { echo " style='display:none' "; } ?>>
    <input type='hidden' id='filterchoice_state' name='filterchoice_state' value='<?php echo $filterchoice_state; ?>' />

    <table id='filterchoices' <?php if ($filterchoice_state!='') { echo " style='display:none' "; } ?> >
        <?php
        $currentgroup='';
        foreach ($filters as $key1 => $flt) {
            if (!isset($previousquestiontype)) {$previousquestiontype="";}
            if ($flt[1] != $currentgroup) {
                if ($currentgroup!='') { ?>
                    <!-- Close filter group --></tr>
                </table></div></td></tr>
            <?php
                }
            ?>
            <!-- GROUP TITLE -->
            <tr>
                <td>
                    <div class='header ui-widget-header'>
                        <input type="checkbox"
                                 id='btn_<?php echo $flt[1]; ?>'
                            onclick="selectCheckboxes('grp_<?php echo $flt[1] ?>', 'summary[]', 'btn_<?php echo $flt[1]; ?>');"
                        />
                        <span class='smalltext'>
                            <strong>
                                <?php echo $flt[4]; ?>
                            </strong>
                            (<?php echo $clang->gT("Question group").$flt[1]; ?>)
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
            <td>
                <div id='grp_<?php echo $flt[1]; ?>'>
                    <table class='filtertable'>
                        <tr>
                <?php
                $counter=0;
            }

            if (isset($counter) && $counter == 4 ||
                ($previousquestiontype == "1" ||
                $previousquestiontype == "A" ||
                $previousquestiontype == "B" ||
                $previousquestiontype == "C" ||
                $previousquestiontype == "E" ||
                $previousquestiontype == "F" ||
                $previousquestiontype == "H" ||
                $previousquestiontype == "K" ||
                $previousquestiontype == "Q" ||
                $previousquestiontype == "R" ||
                $previousquestiontype == ":" ||
                $previousquestiontype == ";")) { ?>
            </tr>
            <tr>
            <?php
            $counter=0;
            }
        $myfield = "{$surveyid}X{$flt[1]}X{$flt[0]}"; $niceqtext=flattenText($flt[5]);

        if ($flt[2]=='M' || $flt[2]=='P' || $flt[2]=='N' || $flt[2]=='L' || $flt[2]=='5'
            || $flt[2]=='G' || $flt[2]=='I' || $flt[2]=='O' || $flt[2]=='Y' || $flt[2]=='!')
        { ?>
            <td>
        <?php
            //Multiple choice:
            if ($flt[2] == "M") {$myfield = "M$myfield";}
            if ($flt[2] == "P") {$myfield = "P$myfield";}

            // File Upload will need special filters in future, hence the special treatment
            if ($flt[2] == "|") {$myfield = "|$myfield";}

            //numerical input will get special treatment (arihtmetic mean, standard derivation, ...)
            if ($flt[2] == "N") {$myfield = "N$myfield";}
        ?>
            <input type='checkbox'
                    id='filter<?php echo $myfield; ?>'
                    name='summary[]'
                    value='<?php echo $myfield; ?>' <?php
            if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
                    || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
                    || array_search("P{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
                    || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE)) { echo " checked='checked'"; }
            ?> />
            <!-- QUESTION HEADING/TITLE -->
            <label for='filter<?php echo $myfield; ?>'><?php echo _showSpeaker(flattenText($flt[5],true)); ?></label><br />
            <?php
                if ($flt[2] != "N" && $flt[2] != "|") {?>
                <select name='<?php
                    if ($flt[2] == "M" ) { echo "M";};
                    if ($flt[2] == "P" ) { echo "P";};
                    echo "{$surveyid}X{$flt[1]}X{$flt[0]}[]'";?>' multiple='multiple'>
                <?php
                }
        }?>
        <!-- QUESTION TYPE = <?php echo $flt[2]; ?> -->
        <?php

            switch ($flt[2])
            {
                case "K": // Multiple Numerical
                    echo "\t</tr>\n\t<tr>\n";

                    $counter2=0;

                    //go through all the (multiple) answers
                    foreach($result[$key1] as $row1)
                    {
                        $row1 = array_values($row1);

                        foreach($row1 as $row)
                        {
                            $row = array_values($row);
                            /*
                            * filter form for numerical input
                            * - checkbox
                            * - greater than
                            * - less than
                            */

                            $myfield1="K".$myfield.$row[0];
                            $myfield2="K{$myfield}".$row[0]."G";
                            $myfield3="K{$myfield}".$row[0]."L";
                            if ($counter2 == 4) { echo "\t</tr>\n\t<tr>\n"; $counter2=0;}

                            //start new TD
                            echo "\t<td>";

                            //checkbox
                            echo "<input type='checkbox'  name='summary[]' value='$myfield1'";

                            //check SGQA -> do we want to pre-check the checkbox?
                            if (isset($summary) && (array_search("K{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}", $summary) !== FALSE))
                            {echo " checked='checked'";}
                            echo " />&nbsp;";

                            //show speaker
                            echo _showSpeaker($flt[3]." - ".flattenText($row[1],true))."<br>\n";?>

                            <span class='smalltext'><?php $clang->eT("Number greater than");?>:</span><br />
                            <?php echo CHtml::textField($myfield2,isset($_POST[$myfield2])?$_POST[$myfield2]:'',array('onkeypress'=>"return goodchars(event,'0123456789.,')"));?>
                            <br>
                            <span class='smalltext'><?php $clang->eT("Number less than");?>:</span><br>
                            <?php echo CHtml::textField($myfield3,isset($_POST[$myfield3])?$_POST[$myfield3]:'',array('onkeypress'=>"return goodchars(event,'0123456789.,')"));?>
                            <br>
                            <?php 
                            //we added 1 form -> increase counter
                            $counter2++;
                        }

                    }
                    break;



                case "Q": // Multiple Short Text

                    //new section
                    echo "\t</tr>\n\t<tr>\n";

                    //get subqestions
                    $result[$key1] = Question::model()->getQuestionsForStatistics('title as code, question as answer', "parent_qid='$flt[0]' AND language = '{$language}'", 'question_order');
                    $counter2=0;

                    //loop through all answers
                    foreach($result[$key1] as $row)
                    {
                        $row = array_values($row);
                        //collecting data for output, for details see above (question type "N")

                        //we have one input field for each answer
                        $myfield2 = "Q".$myfield."$row[0]";
                        if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}

                        echo "\t<td>";
                        echo "<input type='checkbox'  name='summary[]' value='$myfield2'";
                        if (isset($summary) && (array_search("Q{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}", $summary) !== FALSE))
                        {echo " checked='checked'";}

                        echo " />&nbsp;";
                        echo _showSpeaker($flt[3]." - ".flattenText($row[1],true))
                        ."<br />\n"
                        ."\t<span class='smalltext'>".$clang->gT("Responses containing").":</span><br />\n";
                        echo CHtml::textField($myfield2,isset($_POST[$myfield2])?$_POST[$myfield2]:'',array())
                        ."\t</td>\n";
                        $counter2++;
                    }
                    echo "\t</tr>\n\t<tr>\n";
                    $counter=0;
                    break;



                    /*
                    * all "free text" types (T, U, S)  get the same prefix ("T")
                    */
                case "T": // Long free text
                case "U": // Huge free text

                    $myfield2="T$myfield";
                    echo "\t<td>\n";
                    echo "\t<input type='checkbox'  name='summary[]' value='$myfield2'";
                    if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
                    {echo " checked='checked'";}

                    echo " />&nbsp;"
                    ."&nbsp;"._showSpeaker($niceqtext)
                    ."<br />\n"
                    ."\t<span class='smalltext'>".$clang->gT("Responses containing").":</span><br />\n" 
                    .CHtml::textArea($myfield2,isset($_POST[$myfield2])?$_POST[$myfield2]:'',array('rows'=>'3','cols'=>'80'))
                    ."\t</td>\n";
                    break;



                case "S": // Short free text

                    $myfield2="T$myfield";
                    echo "\t<td>";
                    echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                    if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
                    {echo " checked='checked'";}

                    echo " />&nbsp;"
                    ."&nbsp;"._showSpeaker($niceqtext)
                    ."<br />\n"
                    ."\t<span class='smalltext'>".$clang->gT("Responses containing").":</span><br />\n"
                    .CHtml::textField($myfield2,isset($_POST[$myfield2])?$_POST[$myfield2]:'',array())
                    ."\t</td>\n";
                    break;



                case "N": // Numerical

                    //textfields for greater and less than X
                    $myfield2="{$myfield}G";
                    $myfield3="{$myfield}L";
                    echo "\t<span class='smalltext'>".$clang->gT("Number greater than").":</span><br />\n"
                    .CHtml::textField($myfield2,isset($_POST[$myfield2])?$_POST[$myfield2]:'',array( 'onkeypress'=>"return goodchars(event,'0123456789.,')" ))
                    ."\t<br />\n"
                    ."\t<span class='smalltext'>".$clang->gT("Number less than").":</span><br />\n"
                    .CHtml::textField($myfield3,isset($_POST[$myfield3])?$_POST[$myfield3]:'',array( 'onkeypress'=>"return goodchars(event,'0123456789.,')" ))
                    ."\t<br />\n";

                    //put field names into array

                    break;


                case "|": // File Upload

                    // Number of files uploaded for greater and less than X
                    $myfield2 = "{$myfield}G";
                    $myfield3 = "{$myfield}L";
                    echo "\t<span class='smalltext'>".$clang->gT("Number of files greater than").":</span><br />\n"
                    .CHtml::textField($myfield2,isset($_POST[$myfield2])?$_POST[$myfield2]:'',array( 'onkeypress'=>"return goodchars(event,'0123456789.,')" ))
                    ."<br />\n"
                    ."\t<span class='smalltext'>".$clang->gT("Number of files less than").":</span><br />\n"
                    .CHtml::textField($myfield3,isset($_POST[$myfield3])?$_POST[$myfield3]:'',array( 'onkeypress'=>"return goodchars(event,'0123456789.,')" ))
                    ."<br />\n";
                    break;


                    /*
                    * DON'T show any statistics for date questions
                    * because there aren't any statistics implemented yet!
                    *
                    * Only filtering by date is possible.
                    *
                    * See bug report #2539 and
                    * feature request #2620
                    */
                case "D": // Date

                    /*
                    * - input name
                    * - date equals
                    * - date less than
                    * - date greater than
                    */
                    $myfield2="D$myfield";
                    $myfield3=$myfield2."eq";
                    $myfield4=$myfield2."less";
                    $myfield5=$myfield2."more";
                    echo "\t<td>";

                    echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                    if (isset($summary) && (array_search("D{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
                    {echo " checked='checked'";}

                    echo " /><strong>";
                    echo _showSpeaker($niceqtext)
                    ."<br />\n"

                    ."\t<span class='smalltext'>".$clang->gT("Date (YYYY-MM-DD) equals").":<br />\n"
                    .CHtml::textField($myfield3,isset($_POST[$myfield3])?$_POST[$myfield3]:'',array() )
                    ."<br />\n"
                    ."\t&nbsp;&nbsp;".$clang->gT("Date is")." >=<br />\n"
                    .CHtml::textField($myfield4,isset($_POST[$myfield4])?$_POST[$myfield4]:'',array() )
                    ."<br />"
                    .$clang->gT("AND/OR Date is")." <= <br />"
                    .CHtml::textField($myfield5,isset($_POST[$myfield5])?$_POST[$myfield5]:'',array() )
                    ."</span>\n";
                    break;



                case "5": // 5 point choice

                    //we need a list of 5 entries
                    for ($i=1; $i<=5; $i++)
                    {
                        echo "\t<option value='$i'";

                        //pre-select values which were marked before
                        if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($i, $_POST[$myfield]))
                        {echo " selected";}

                        echo ">$i</option>\n";
                    }

                    //End the select which starts before the CASE statement (around line 411)
                    echo"\t</select>\n";
                    break;



                case "G": // Gender
                    echo "\t<option value='F'";

                    //pre-select values which were marked before
                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("F", $_POST[$myfield])) {echo " selected";}

                    echo ">".$clang->gT("Female")."</option>\n";
                    echo "\t<option value='M'";

                    //pre-select values which were marked before
                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("M", $_POST[$myfield])) {echo " selected";}

                    echo ">".$clang->gT("Male")."</option>\n\t</select>\n";
                    echo "\t</td>\n";
                    break;



                case "Y": // Yes\No
                    echo "\t<option value='Y'";

                    //pre-select values which were marked before
                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("Y", $_POST[$myfield])) {echo " selected";}

                    echo ">".$clang->gT("Yes")."</option>\n"
                    ."\t<option value='N'";

                    //pre-select values which were marked before
                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("N", $_POST[$myfield])) {echo " selected";}

                    echo ">".$clang->gT("No")."</option></select>\n";
                    break;



                case "I": // Language
                    $survlangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
                    $survlangs[] = Survey::model()->findByPk($surveyid)->language;
                    foreach ($survlangs  as $availlang)
                    {
                        echo "\t<option value='".$availlang."'";

                        //pre-select values which were marked before
                        if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($availlang, $_POST[$myfield]))
                        {echo " selected";}

                        echo ">".getLanguageNameFromCode($availlang,false)."</option>\n";
                    }
                    break;



                    //----------------------- ARRAYS --------------------------

                case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS

                    echo "\t</tr>\n\t<tr>\n";

                    //get answers
                    $result[$key1] = Question::model()->getQuestionsForStatistics('title, question', "parent_qid='$flt[0]' AND language = '{$language}'", 'question_order');
                    $counter2=0;

                    //check all the results
                    foreach($result[$key1] as $row)
                    {
                        $row = array_values($row);
                        $myfield2 = $myfield.$row[0];
                        echo "<!-- $myfield2 - ";

                        if (isset($_POST[$myfield2])) {echo htmlspecialchars($_POST[$myfield2]);}

                        echo " -->\n";

                        if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}

                        echo "\t<td align='center'>"
                        ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                        //pre-check
                        if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                        echo " />&nbsp;"
                        ._showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                        ."<br />\n"
                        ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";

                        //there are always exactly 5 values which have to be listed
                        for ($i=1; $i<=5; $i++)
                        {
                            echo "\t<option value='$i'";

                            //pre-select
                            if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected";}
                            if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {echo " selected";}

                            echo ">$i</option>\n";
                        }

                        echo "\t</select>\n\t</td>\n";
                        $counter2++;

                        //add this to all the other fields
                    }

                    echo "\t</tr>\n\t<tr>\n";
                    $counter=0;
                    break;



                    //just like above only a different loop
                case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
                    echo "\t</tr>\n\t<tr>\n";

                    $counter2=0;
                    foreach($result[$key1] as $row)
                    {
                        $row=array_values($row);
                        $myfield2 = $myfield . "$row[0]";
                        echo "<!-- $myfield2 - ";

                        if (isset($_POST[$myfield2])) {echo htmlspecialchars($_POST[$myfield2]);}

                        echo " -->\n";

                        if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}

                        echo "\t<td align='center'>"; //heading
                        echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                        if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                        echo " />&nbsp;"
                        ._showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                        ."<br />\n"
                        ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";

                        //here wo loop through 10 entries to create a larger output form
                        for ($i=1; $i<=10; $i++)
                        {
                            echo "\t<option value='$i'";
                            if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected";}
                            if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {echo " selected";}
                            echo ">$i</option>\n";
                        }

                        echo "\t</select>\n\t</td>\n";
                        $counter2++;
                    }

                    echo "\t</tr>\n\t<tr>\n";
                    $counter=0;
                    break;



                case "C": // ARRAY OF YES\No\$clang->gT("Uncertain") QUESTIONS
                    echo "\t</tr>\n\t<tr>\n";

                    $counter2=0;

                    //loop answers
                    foreach($result[$key1] as $row)
                    {
                        $row=array_values($row);
                        $myfield2 = $myfield . "$row[0]";
                        echo "<!-- $myfield2 - ";

                        if (isset($_POST[$myfield2])) {echo htmlspecialchars($_POST[$myfield2]);}

                        echo " -->\n";

                        if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}

                        echo "\t<td align='center'>"
                        ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                        if (isset($summary) && array_search($myfield2, $summary)!== FALSE)
                        {echo " checked='checked'";}

                        echo " />&nbsp;<strong>"
                        ._showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                        ."</strong><br />\n"
                        ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n"
                        ."\t<option value='Y'";

                        //pre-select "yes"
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("Y", $_POST[$myfield2])) {echo " selected";}

                        echo ">".$clang->gT("Yes")."</option>\n"
                        ."\t<option value='U'";

                        //pre-select "uncertain"
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("U", $_POST[$myfield2])) {echo " selected";}

                        echo ">".$clang->gT("Uncertain")."</option>\n"
                        ."\t<option value='N'";

                        //pre-select "no"
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("N", $_POST[$myfield2])) {echo " selected";}

                        echo ">".$clang->gT("No")."</option>\n"
                        ."\t</select>\n\t</td>\n";
                        $counter2++;

                        //add to array
                    }

                    echo "\t</tr>\n\t<tr>\n";
                    $counter=0;
                    break;



                    //similiar to the above one
                case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
                    echo "\t</tr>\n\t<tr>\n";

                    $counter2=0;

                    foreach($result[$key1] as $row)
                    {
                        $row=array_values($row);
                        $myfield2 = $myfield . "$row[0]";
                        echo "<!-- $myfield2 - ";

                        if (isset($_POST[$myfield2])) {echo htmlspecialchars($_POST[$myfield2]);}

                        echo " -->\n";

                        if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}

                        echo "\t<td align='center'>"
                        ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                        if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                        echo " />&nbsp;<strong>"
                        ._showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                        ."</strong><br />\n"
                        ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n"
                        ."\t<option value='I'";

                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("I", $_POST[$myfield2])) {echo " selected";}

                        echo ">".$clang->gT("Increase")."</option>\n"
                        ."\t<option value='S'";

                        if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("S", $_POST[$myfield2])) {echo " selected";}

                        echo ">".$clang->gT("Same")."</option>\n"
                        ."\t<option value='D'";

                        if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("D", $_POST[$myfield2])) {echo " selected";}

                        echo ">".$clang->gT("Decrease")."</option>\n"
                        ."\t</select>\n\t</td>\n";
                        $counter2++;
                    }

                    echo "\t</tr>\n\t<tr>\n";
                    $counter=0;
                    break;

                case ";":  //ARRAY (Multi Flex) (Text)
                    echo "\t</tr>\n\t<tr>\n";

                    $counter2=0;
                    foreach($result[$key1] as $key => $row)
                    {
                        $row = array_values($row);
                        $fresult = $fresults[$key1][$key];
                        foreach($fresult as $frow)
                        {
                            $myfield2 = "T".$myfield . $row[0] . "_" . $frow['title'];
                            echo "<!-- $myfield2 - ";
                            if (isset($_POST[$myfield2])) {echo htmlspecialchars($_POST[$myfield2]);}
                            echo " -->\n";
                            if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}
                            echo "\t<td align='center'>"
                            ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                            if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}
                            echo " />&nbsp;<strong>"
                            ._showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]." [".$frow['question']."]")." - ".$row[0]."/".$frow['title'])
                            ."</strong><br />\n";
                            //echo $fquery;
                            echo "\t<span class='smalltext'>".$clang->gT("Responses containing").":</span><br />\n"
                            .CHtml::textField($myfield2,isset($_POST[$myfield2])?$_POST[$myfield2]:'',array() )
                            ."</td>\n";
                            $counter2++;
                        }
                    }
                    echo "\t<td>\n";
                    $counter=0;
                    break;

                case ":":  //ARRAY (Multi Flex) (Numbers)
                    echo "\t</tr>\n\t<tr>\n";

                    $counter2=0;
                    //Get qidattributes for this question
                    $qidattributes=getQuestionAttributeValues($flt[0]);
                    if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) ==''){
                        $maxvalue=$qidattributes['multiflexible_max'];
                        $minvalue=1;
                    }
                    if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) ==''){
                        $minvalue=$qidattributes['multiflexible_min'];
                        $maxvalue=$qidattributes['multiflexible_min'] + 10;
                    }
                    if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) ==''){
                        $minvalue=1;
                        $maxvalue=10;
                    }
                    if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !=''){
                        if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
                            $minvalue=$qidattributes['multiflexible_min'];
                            $maxvalue=$qidattributes['multiflexible_max'];
                        }
                    }

                    if (trim($qidattributes['multiflexible_step'])!='') {
                        $stepvalue=$qidattributes['multiflexible_step'];
                    } else {
                        $stepvalue=1;
                    }
                    if ($qidattributes['multiflexible_checkbox']!=0)
                    {
                        $minvalue=0;
                        $maxvalue=1;
                        $stepvalue=1;
                    }
                    foreach($result[$key1] as $row)
                    {
                        $row = array_values($row);
                        $fresult = Question::model()->getQuestionsForStatistics('*', "parent_qid='$flt[0]' AND language = '{$language}' AND scale_id = 1", 'question_order, title');
                        foreach($fresult as $frow)
                        {
                            $myfield2 = $myfield . $row[0] . "_" . $frow['title'];
                            echo "<!-- $myfield2 - ";
                            if (isset($_POST[$myfield2])) {echo htmlspecialchars($_POST[$myfield2]);}
                            echo " -->\n";
                            if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}
                            echo "\t<td align='center'>"
                            ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                            if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}
                            echo " />&nbsp;<strong>"
                            ._showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]." [".$frow['question']."]")." - ".$row[0]."/".$frow['title'])
                            ."</strong><br />\n";
                            //echo $fquery;
                            echo "\t<select name='{$myfield2}[]' multiple='multiple' rows='5' cols='5'>\n";
                            for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue)
                            {
                                echo "\t<option value='$ii'";
                                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected";}
                                echo ">$ii</option>\n";
                            }
                            echo "\t</select>\n\t</td>\n";
                            $counter2++;
                        }
                    }
                    echo "\t<td>\n";
                    $counter=0;
                    break;
                    /*
                    * For question type "F" and "H" you can use labels.
                    * The only difference is that the labels are applied to column heading
                    * or rows respectively
                    */
                case "F": // FlEXIBLE ARRAY
                case "H": // ARRAY (By Column)
                    //echo "\t</tr>\n\t<tr>\n";

                    //Get answers. We always use the answer code because the label might be too long elsewise

                    $counter2=0;

                    //check all the answers
                    foreach($result[$key1] as $key=>$row)
                    {
                        $row=array_values($row);
                        $myfield2 = $myfield . "$row[0]";
                        echo "<!-- $myfield2 -->\n";

                        if ($counter2 == 4)
                        {
                            echo "\t</tr>\n\t<tr>\n";
                            $counter2=0;
                        }

                        echo "\t<td align='center'>"
                        ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                        if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                        echo " />&nbsp;<strong>"
                        ._showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                        ."</strong><br />\n";

                        /*
                        * when hoovering the speaker symbol we show the whole question
                        *
                        * flt[6] is the label ID
                        *
                        * table "labels" contains
                        * - lid
                        * - code
                        * - title
                        * - sortorder
                        * - language
                        */
                        $fresult = $fresults[$key1];

                        //for debugging only:
                        //echo $fquery;

                        //creating form
                        echo "\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";

                        //loop through all possible answers
                        foreach($fresult as $frow)
                        {
                            echo "\t<option value='{$frow['code']}'";

                            //pre-select
                            if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected";}

                            echo ">({$frow['code']}) ".flattenText($frow['answer'],true)."</option>\n";
                        }

                        echo "\t</select>\n\t</td>\n";
                        $counter2++;

                        //add fields to main array
                    }

                    //echo "\t<td>\n";
                    $counter=0;
                    break;



                case "R": //RANKING

                    echo "\t</tr>\n\t<tr>\n";

                    //get some answers


                    //get number of answers
                    $count = count($result[$key1]);

                    //lets put the answer code and text into the answers array
                    foreach($result[$key1] as $row)
                    {
                        $answers[]=array($row['code'], $row['answer']);
                    }

                    $counter2=0;

                    //loop through all answers. if there are 3 items to rate there will be 3 statistics
                    for ($i=1; $i<=$count; $i++)
                    {
                        //adjust layout depending on counter
                        if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}

                        //myfield is the SGQ identifier
                        //myfield2 is just used as comment in HTML like "R40X34X1721-1"
                        $myfield2 = "R" . $myfield . $i . "-" . strlen($i);
                        $myfield3 = $myfield . $i;
                        echo "<!-- $myfield2 - ";

                        if (isset($_POST[$myfield2])) {echo htmlspecialchars($_POST[$myfield2]);}

                        echo " -->\n"
                        ."\t<td align='center'>"
                        ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                        //pre-check
                        if (isset($summary) && array_search($myfield2, $summary) !== FALSE) {echo " checked='checked'";}

                        $trow = array_values($row);

                        echo " />&nbsp;<strong>"
                        ._showSpeaker($niceqtext." ".str_replace("'", "`", $trow[1])." - # ".$flt[3])
                        ."</strong><br />\n"
                        ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$i}[]' multiple='multiple'>\n";

                        //output lists of ranking items
                        foreach ($answers as $ans)
                        {
                            echo "\t<option value='$ans[0]'";

                            //pre-select
                            if (isset($_POST[$myfield3]) && is_array($_POST[$myfield3]) && in_array("$ans[0]", $_POST[$myfield3])) {echo " selected";}

                            echo ">".flattenText($ans[1])."</option>\n";
                        }

                        echo "\t</select>\n\t</td>\n";
                        $counter2++;

                        //add averything to main array
                    }

                    echo "\t</tr>\n\t<tr>\n";

                    //Link to rankwinner script - awaiting completion - probably never gonna happen. Mystery creator.
                    //          echo "\t</tr>\n\t<tr bgcolor='#DDDDDD'>\n"
                    //              ."<td colspan=$count align=center>"
                    //              ."<input type='button' value='Show Rank Summary' onclick=\"window.open('rankwinner.php?sid=$surveyid&amp;qid=$flt[0]', '_blank', 'toolbar=no, directories=no, location=no, status=yes, menubar=no, resizable=no, scrollbars=no, width=400, height=300, left=100, top=100')\">"
                    //              ."</td></tr>\n\t<tr>\n";
                    $counter=0;
                    unset($answers);
                    break;

                    //Boilerplate questions are only used to put some text between other questions -> no analysis needed
                case "X": //This is a boilerplate question and it has no business in this script
                    echo "\t<td></td>";
                    break;

                case "1": // MULTI SCALE
                    echo "\t</tr>\n\t<tr>\n";

                    //special dual scale counter
                    $counter2=0;

                    //loop through answers
                    foreach($result[$key1] as $row)
                    {
                        $row=array_values($row);

                        //----------------- LABEL 1 ---------------------
                        //myfield2 = answer code.
                        $myfield2 = $myfield . "$row[0]#0";

                        //3 lines of debugging output
                        echo "<!-- $myfield2 - ";
                        if (isset($_POST[$myfield2]))
                        {
                            echo htmlspecialchars($_POST[$myfield2]);
                        }
                        echo " -->\n";

                        //some layout adaptions -> new line after 4 entries
                        if ($counter2 == 4)
                        {
                            echo "\t</tr>\n\t<tr>\n";
                            $counter2=0;
                        }

                        //output checkbox and question/label text
                        echo "\t<td align='center'>";
                        echo "<input type='checkbox' name='summary[]' value='$myfield2'";

                        //pre-check
                        if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                        //check if there is a dualscale_headerA/B
                        $dshresult = $dshresults[$key1][0];

                        //get header
                        foreach($dshresult as $dshrow)
                        {
                            $dshrow=array_values($dshrow);
                            $dualscaleheadera = $dshrow[0];
                        }

                        if(isset($dualscaleheadera) && $dualscaleheadera != "")
                        {
                            $labeltitle = $dualscaleheadera;
                        }
                        else
                        {
                            $labeltitle='';
                        }

                        echo " />&nbsp;<strong>"
                        ._showSpeaker($niceqtext." [".str_replace("'", "`", $row[1])."] - ".$clang->gT("Label").": ".$labeltitle)
                        ."</strong><br />\n";

                        /* get labels
                        * table "labels" contains
                        * - lid
                        * - code
                        * - title
                        * - sortorder
                        * - language
                        */
                        $fresult = Answer::model()->getQuestionsForStatistics('*', "qid='$flt[0]' AND language = '{$language}' AND scale_id = 0", 'sortorder, code');

                        //this is for debugging only
                        //echo $fquery;

                        echo "\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}#{0}[]' multiple='multiple'>\n";

                        //list answers
                        foreach($fresult as $frow)
                        {
                            echo "\t<option value='{$frow['code']}'";

                            //pre-check
                            if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected";}

                            echo ">({$frow['code']}) ".flattenText($frow['answer'],true)."</option>\n";

                        }

                        echo "\t</select>\n\t</td>\n";
                        $counter2++;




                        //----------------- LABEL 2 ---------------------

                        //myfield2 = answer code
                        $myfield2 = $myfield . "$row[0]#1";

                        //3 lines of debugging output
                        echo "<!-- $myfield2 - ";
                        if (isset($_POST[$myfield2]))
                        {
                            echo htmlspecialchars($_POST[$myfield2]);
                        }

                        echo " -->\n";

                        //some layout adaptions -> new line after 4 entries
                        if ($counter2 == 4)
                        {
                            echo "\t</tr>\n\t<tr>\n";
                            $counter2=0;
                        }

                        //output checkbox and question/label text
                        echo "\t<td align='center'>";
                        echo "<input type='checkbox' name='summary[]' value='$myfield2'";
                        //pre-check
                        if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                        //check if there is a dualsclae_headerA/B
                        $dshresult2 = $dshresults2[$key1][0];

                        //get header
                        foreach($dshresult2 as $dshrow2)
                        {
                            $dshrow2=array_values($dshrow2);
                            $dualscaleheaderb = $dshrow2[0];
                        }

                        if(isset($dualscaleheaderb) && $dualscaleheaderb != "")
                        {
                            $labeltitle2 = $dualscaleheaderb;
                        }
                        else
                        {
                            //get label text

                            $labeltitle2 = '';
                        }

                        echo " />&nbsp;<strong>"
                        ._showSpeaker($niceqtext." [".str_replace("'", "`", $row[1])."] - ".$clang->gT("Label").": ".$labeltitle2)
                        ."</strong><br />\n";
                        $fresult = Answer::model()->getQuestionsForStatistics('*', "qid='$flt[0]' AND language = '$language' AND scale_id = 1", 'sortorder, code');

                        //this is for debugging only
                        //echo $fquery;

                        echo "\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}#{1}[]' multiple='multiple'>\n";

                        //list answers
                        foreach($fresult as $frow)
                        {
                            echo "\t<option value='{$frow['code']}'";

                            //pre-check
                            if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected";}

                            echo ">({$frow['code']}) ".flattenText($frow['answer'],true)."</option>\n";

                        }

                        echo "\t</select>\n\t</td>\n";
                        $counter2++;

                    }   //end WHILE -> loop through all answers

                    echo "\t<td>\n";


                    $counter=0;
                    break;

                case "P":  //P - Multiple choice with comments
                case "M":  //M - Multiple choice

                    //loop through answers
                    foreach($result[$key1] as $row)
                    {
                        $row=array_values($row);
                        echo "\t<option value='{$row[0]}'";

                        //pre-check
                        if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {echo " selected";}

                        echo '>'.flattenText($row[1],true)."</option>\n";
                    }

                    echo "\t</select>\n\t</td>\n";
                    break;


                    /*
                    * This question types use the default settings:
                    *  L - List (Radio)
                    O - List With Comment
                    P - Multiple choice with comments
                    ! - List (Dropdown)
                    */
                default:

                    //loop through answers
                    foreach($result[$key1] as $row)
                    {
                        $row=array_values($row);
                        echo "\t<option value='{$row[0]}'";

                        //pre-check
                        if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {echo " selected";}

                        echo '>'.flattenText($row[1],true)."</option>\n";
                    }

                    echo "\t</select>\n\t</td>\n";
                    break;

            }   //end switch -> check question types and create filter forms
            $currentgroup=$flt[1];
            if (!isset($counter))
            {
                $counter=0;
            }
            $counter++;
            $previousquestiontype = $flt[2];

        ?>
        <?php
        }
        ?>
    </tr>
    </table>
    </div>
    </td></tr>
    <tr class='statistics-tbl-separator'><td></td></tr>
    </table>

    <p id='vertical_slide2'>
    <input type='submit' value='<?php $clang->eT("View statistics"); ?>' />
    <input type='button' value='<?php $clang->eT("Clear"); ?>' onclick="window.open('<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>', '_top')" />
    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
    <input type='hidden' name='display' value='stats' />
    </p>
    </div><!-- END OF AUTOSCROLLING DIV CONTAINING QUESTION FILTERS -->
</form>
<div style='clear: both'></div>
<?php
flush(); //Let's give the user something to look at while they wait for the pretty pictures
?>
<div class='header ui-widget-header header_statistics'>
    <div style='float:right'><img src='<?php echo $sImageURL; ?>/maximize.png' id='showsfilter' alt='<?php $clang->eT("Maximize"); ?>'/><img src='<?php echo $sImageURL; ?>/minimize.png' id='hidesfilter' alt='<?php $clang->eT("Minimize"); ?>'/></div>
    <?php $clang->eT("Statistics"); ?>
</div>

<div id='statisticsoutput' class='statisticsfilters'>
<?php echo $output; ?>
</div>

<?php

    /* This function builds the text description of eqch question in the filter section
     *
     * @param string $hinttext The question text
     *
     * */
    function _showSpeaker($hinttext)
    {
        global $maxchars; //Where does this come from? can it be replaced? passed with function call?
        $clang = Yii::app()->lang;
        $sImageURL = Yii::app()->getConfig('adminimageurl');
        if(!isset($maxchars))
        {
            $maxchars = 100;
        }
        $htmlhinttext=str_replace("'",'&#039;',$hinttext);  //the string is already HTML except for single quotes so we just replace these only
        $jshinttext=javascriptEscape($hinttext,true,true);  //Build a javascript safe version of the string

        if(strlen($hinttext) > ($maxchars))
        {
            $shortstring = flattenText($hinttext);

            $shortstring = htmlspecialchars(mb_strcut(html_entity_decode($shortstring,ENT_QUOTES,'UTF-8'), 0, $maxchars, 'UTF-8'));

            //output with hoover effect
            $reshtml= "<span style='cursor: pointer' title='".$htmlhinttext."' "
            ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\">"
            ." \"$shortstring...\" </span>"
            ."<img style='cursor: pointer' src='$sImageURL/speaker.png' align='bottom' alt='$htmlhinttext' title='$htmlhinttext' "
            ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\" />";
        }
        else
        {
            $reshtml= "<span style='cursor: pointer' title='".$htmlhinttext."'> \"$htmlhinttext\"</span>";
        }
        return $reshtml;
    }

?>
