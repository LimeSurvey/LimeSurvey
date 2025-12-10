<?php $myfield = "{$surveyid}X{$flt[1]}X{$flt[0]}"; $niceqtext=flattenText($flt[5]); ?>

<?php
    // If boilerplate don't render anything
    if (in_array( $flt[2],[Question::QT_X_TEXT_DISPLAY,Question::QT_ASTERISK_EQUATION])) return;

    // Get qidattributes for this question
    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($flt[0]);

    //$specialQuestionTypes = array("M","P","T","S","Q","|","","N","K","D");
    $specialQuestionTypes = array(Question::QT_M_MULTIPLE_CHOICE, Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS);
    if ( in_array( $flt[2], $specialQuestionTypes))
    {
        $myfield = $flt[2].$myfield;
    }

    // Array (Numbers) questions, have two "modes": dropdowns or text inputs
    // When using text inputs, we need to treat them as single numerical questions
    if ($flt[2] == Question::QT_COLON_ARRAY_NUMBERS && !empty($qidattributes['input_boxes'])) {
        $myfield = $flt[2] . $myfield;
    }

    $counter2 = 0;
?>




    <div class="question-filter-container grow-3 nofloat ls-space padding all-10">
    <?php echo "<!-- Question type :  $flt[2] -->"; ?>
        <?php if ($flt[2]==Question::QT_M_MULTIPLE_CHOICE || $flt[2]==Question::QT_VERTICAL_FILE_UPLOAD || $flt[2]==Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS || $flt[2]==Question::QT_L_LIST || $flt[2]==Question::QT_5_POINT_CHOICE || $flt[2]==Question::QT_G_GENDER || $flt[2]==Question::QT_I_LANGUAGE || $flt[2]==Question::QT_O_LIST_WITH_COMMENT || $flt[2]==Question::QT_Y_YES_NO_RADIO || $flt[2]==Question::QT_EXCLAMATION_LIST_DROPDOWN): ?>
            <!--  TYPE =='M' || 'P' || 'N' || 'L' || '5' || 'G' || 'I' || 'O' || 'Y' || '!' -->
            <div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">
                <input type='checkbox'
                    id='filter<?php echo $myfield; ?>'
                    name='summary[]'
                    value='<?php echo $myfield; ?>' <?php
                    if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
                    || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
                    || array_search("P{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
                    || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
                    { echo " checked='checked'"; }
                    ?>
                    />
                <label for='filter<?php echo $myfield; ?>'>&nbsp;<?php echo $flt[3].' - '.$oStatisticsHelper::_showSpeaker(flattenText($flt[5],true)); ?>
                </label>
            </div>

            <?php if ($flt[2] != Question::QT_N_NUMERICAL && $flt[2] != Question::QT_VERTICAL_FILE_UPLOAD):?>
                <select name='<?php
                    if ($flt[2] == Question::QT_M_MULTIPLE_CHOICE ) { echo Question::QT_M_MULTIPLE_CHOICE;};
                    if ($flt[2] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS ) { echo Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS;};
                    echo "{$surveyid}X{$flt[1]}X{$flt[0]}[]";?>' multiple='multiple' class='form-control'>
            <?php endif; ?>

        <?php endif; ?>
        <!-- QUESTION TYPE = <?php echo $flt[2]; ?> -->
        <?php
        switch ($flt[2])
        {
            case Question::QT_K_MULTIPLE_NUMERICAL: // Multiple Numerical
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                //go through all the (multiple) answers
                foreach($result[$key1] as $row1)
                {
                    $row1 = array_values($row1);

                    foreach($row1 as $row)
                    {
                        /*
                        * filter form for Numerical input
                        * - checkbox
                        * - greater than
                        * - less than
                        */

                        $myfield1="K".$myfield.$row['title'];
                        $myfield2="K{$myfield}".$row['title']."G";
                        $myfield3="K{$myfield}".$row['title']."L";
                        if ($counter2 == 4) { echo "\t</tr>\n\t<tr>\n"; $counter2=0;}

                        //checkbox
                        echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                        echo "<input type='checkbox'  name='summary[]' value='$myfield1'";

                        //check SGQA -> do we want to pre-check the checkbox?
                        if (isset($summary) && (array_search("K{$surveyid}X{$flt[1]}X{$flt[0]}{$row['title']}", $summary) !== FALSE))
                        {
                            echo " checked='checked'";
                        }

                        echo " />&nbsp;";

                        //show speaker
                        echo $oStatisticsHelper::_showSpeaker($flt[3]." - ".flattenText($row['question'],true))
                        ."</div>

                        <div class='mb-3 row'>
                        <label for='".$myfield2."' class='col-md-4 form-label'>".gT("Number greater than:")."</label>
                        <div class='col-md-6'>"
                        .CHtml::numberField($myfield2,$_POST[$myfield2] ?? '',array( 'class'=>'form-control', 'step'=>'any'))
                        ."</div>
                        </div>
                        <div class='mb-3 row'>
                        <label for='N".$myfield3."' class='col-md-4 form-label'>".gT("Number less than:")."</label>
                        <div class='col-md-6'>"
                        .CHtml::numberField($myfield3,$_POST[$myfield3] ?? '',array( 'class'=>'form-control', 'step'=>'any'))
                        ."</div>
                        </div>";                
                    }

                }
                break;



            case Question::QT_Q_MULTIPLE_SHORT_TEXT: // Multiple short text
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                //get subqestions
                $result[$key1] = Question::model()->getQuestionsForStatistics('title, question', "parent_qid='$flt[0]'", 'question_order');
                //$counter2=0;

                //loop through all answers
                $count = 0;
                foreach($result[$key1] as $row)
                {
                    echo '<div class="row"><div class="col-md-12">';

                    //we have one input field for each answer
                    $myfield2 = "Q".$myfield.$row['title'];
                    echo "&nbsp;&nbsp; <input type='checkbox'  name='summary[]' value='$myfield2'";

                    if (isset($summary) && (array_search("Q{$surveyid}X{$flt[1]}X{$flt[0]}{$row['title']}", $summary) !== FALSE))
                    {
                        echo " checked='checked'";
                    }

                    echo " />&nbsp;";
                    echo $oStatisticsHelper::_showSpeaker($flt[3]." - ".flattenText($row['question'],true))
                    ."<br /><p style='padding: 1em;'>\n"
                    ."\t<span class='smalltext'>".gT("Responses containing").":</span><br />\n";
                    echo CHtml::textField($myfield2,$_POST[$myfield2] ?? '',array('class'=>'form-control'));
                    echo "</p>";
                    echo '</div></div>';
                }
                break;


            /*
            * all "free text" types (T, U, S)  get the same prefix ("T")
            */
            case Question::QT_T_LONG_FREE_TEXT: // Long free text
            case Question::QT_U_HUGE_FREE_TEXT: // Huge free text
                echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                $myfield2="T$myfield";
                echo "\t<input type='checkbox'  name='summary[]' value='$myfield2'";
                if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
                {echo " checked='checked'";}

                echo " />&nbsp;"
                ."&nbsp;".$oStatisticsHelper::_showSpeaker($niceqtext)
                ."<br />\n"
                ."\t<span class='smalltext'>".gT("Responses containing").":</span>
                </div>\n"
                .CHtml::textArea($myfield2,$_POST[$myfield2] ?? '',array('rows'=>'3','cols'=>'40'));
                break;



            case Question::QT_S_SHORT_FREE_TEXT: // Short free text
                echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                $myfield2="T$myfield";
                echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
                {echo " checked='checked'";}

                echo " />&nbsp;"
                ."&nbsp;".$oStatisticsHelper::_showSpeaker($niceqtext)
                ."<br />\n"
                ."\t<span class='smalltext'>".gT("Responses containing").":</span>
                </div>\n"
                .CHtml::textField($myfield2,$_POST[$myfield2] ?? '',array('class'=>'form-control'));
                break;



            case Question::QT_N_NUMERICAL: // Numerical
                //textfields for greater and less than X
                ?>
                <div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">
                    <input type='checkbox'
                        id='filter<?php echo $myfield; ?>'
                        name='summary[]'
                        value='N<?php echo $myfield; ?>' <?php
                        if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
                        || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
                        || array_search("P{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
                        || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
                        { echo " checked='checked'"; }
                        ?>
                        />
                    <label for='filter<?php echo $myfield; ?>'>&nbsp;<?php echo $flt[3].' - '.$oStatisticsHelper::_showSpeaker(flattenText($flt[5],true)); ?>
                    </label>
                </div>
                <?php
                $myfield2="N{$myfield}G";
                $myfield3="N{$myfield}L";
                echo " 
                <div class='mb-3 row'>
                <label for='".$myfield2."' class='col-md-4 form-label'>".gT("Number greater than:")."</label>
                <div class='col-md-6'>"
                .CHtml::numberField($myfield2,$_POST[$myfield2] ?? '',array( 'class'=>'form-control', 'step'=>'any'))
                ."</div>
                </div>
                <div class='mb-3 row'>
                <label for='N".$myfield3."' class='col-md-4 form-label'>".gT("Number less than:")."</label>
                <div class='col-md-6'>"
                .CHtml::numberField($myfield3,$_POST[$myfield3] ?? '',array( 'class'=>'form-control', 'step'=>'any'))
                ."</div>
                </div>";                
                break;


            case Question::QT_VERTICAL_FILE_UPLOAD: // File Upload

                // Number of files uploaded for greater and less than X
                $myfield2 = "{$myfield}G";
                $myfield3 = "{$myfield}L";
                echo"
                <div class='mb-3 row'>
                <label for='".$myfield2."' class='col-md-4 form-label'>".gT("Number of files greater than:")."</label>
                <div class='col-md-6'>"
                .CHtml::numberField($myfield2,$_POST[$myfield2] ?? '',array( 'class'=>'form-control', 'step'=>'any'))
                ."</div>
                </div>
                <div class='mb-3 row'>
                <label for='N".$myfield3."' class='col-md-4 form-label'>".gT("Number of files less than:")."</label>
                <div class='col-md-6'>"
                .CHtml::numberField($myfield3,$_POST[$myfield3] ?? '',array( 'class'=>'form-control', 'step'=>'any'))
                ."</div>
                </div>";                
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
            case Question::QT_D_DATE: // Date

                /*
                * - input name
                * - date equals
                * - date less than
                * - date greater than
                */
                $myfield2="D$myfield";
                $myfield3=$myfield2."eq";
                $myfield4=$myfield2."more";
                $myfield5=$myfield2."less";
                echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                if (isset($summary) && (array_search("D{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
                {echo " checked='checked'";}

                echo " />";
                echo $oStatisticsHelper::_showSpeaker($niceqtext)."

                <div class='mb-3 row' style='margin-top:1em;'>
                <label for='".$myfield3."' class='col-md-4 col-form-label smalltext'>".gT("Date equals:")."</label>
                <div class='col-md-8'>";
                Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                    'name' => $myfield3,
                    'id' => $myfield3,
                    'value' => $_POST[$myfield3] ?? '',
                    'pluginOptions' => array(
                        'format' => $dateformatdetails['jsdate'],
                        'allowInputToggle' => true,
                        'showClear' => true,
                        'theme' => 'light',
                        'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                    )
                ));                
                echo "
                </div>
              </div>
              <div class='mb-3 row'>
              <label for='".$myfield4."' class='col-md-4 col-form-label smalltext'>".gT("Date is >= :")."</label>
              <div class='col-md-8'>";
              Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                  'name' => $myfield4,
                  'id' => $myfield4,
                  'value' => $_POST[$myfield4] ?? '',
                  'pluginOptions' => array(
                      'format' => $dateformatdetails['jsdate'] . " HH:mm",
                      'allowInputToggle' => true,
                      'showClear' => true,
                      'theme' => 'light',
                      'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                  )
              ));                
              echo "
              </div>
            </div>
            <div class='mb-3 row'>
            <label for='".$myfield5."' class='col-md-4 col-form-label smalltext'>".gT("And/or Date is <= :")."</label>
            <div class='col-md-8'>";
            Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                'name' => $myfield5,
                'id' => $myfield5,
                'value' => $_POST[$myfield5] ?? '',
                'pluginOptions' => array(
                    'format' => $dateformatdetails['jsdate'] . " HH:mm",
                    'allowInputToggle' => true,
                    'showClear' => true,
                    'theme' => 'light',
                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                )
            ));                
            echo "
            </div>
          </div>";            

                echo '</div>';
                break;



            case Question::QT_5_POINT_CHOICE: // 5 point choice

                //we need a list of 5 entries
                for ($i=1; $i<=5; $i++)
                {
                    echo "\t<option value='$i'";

                    //pre-select values which were marked before
                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($i, $_POST[$myfield]))
                    {echo " selected='selected' ";}

                    echo ">$i</option>\n";
                }

                //End the select which starts before the CASE statement (around line 411)
                echo"\t</select>\n";
                break;



            case Question::QT_G_GENDER: // Gender
                echo "\t<option value='F'";

                //pre-select values which were marked before
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("F", $_POST[$myfield])) {echo " selected='selected' ";}

                echo ">".gT("Female")."</option>\n";
                echo "\t<option value='M'";

                //pre-select values which were marked before
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("M", $_POST[$myfield])) {echo " selected='selected' ";}

                echo ">".gT("Male")."</option>\n\t</select>\n";
                break;



            case Question::QT_Y_YES_NO_RADIO: // Yes\No
                echo "\t<option value='Y'";

                //pre-select values which were marked before
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("Y", $_POST[$myfield])) {echo " selected='selected' ";}

                echo ">".gT("Yes")."</option>\n"
                ."\t<option value='N'";

                //pre-select values which were marked before
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("N", $_POST[$myfield])) {echo " selected='selected' ";}

                echo ">".gT("No")."</option></select>\n";
                break;



            case Question::QT_I_LANGUAGE: // Language
                $survlangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
                $survlangs[] = Survey::model()->findByPk($surveyid)->language;
                foreach ($survlangs  as $availlang)
                {
                    echo "\t<option value='".$availlang."'";

                    //pre-select values which were marked before
                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($availlang, $_POST[$myfield]))
                    {echo " selected='selected' ";}

                    echo ">".getLanguageNameFromCode($availlang,false)."</option>\n";
                }
                echo "</select>";
                break;



                //----------------------- ARRAYS --------------------------

            case Question::QT_A_ARRAY_5_POINT: // Array of 5 point choice questions
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                //get answers
                $result[$key1] = Question::model()->getQuestionsForStatistics('title, question', "parent_qid='$flt[0]' AND language = '{$language}'", 'question_order');
                //$counter2=0;

                //check all the results
                foreach($result[$key1] as $row)
                {
                    $row = array_values($row);
                    $myfield2 = $myfield.$row[4];
                    echo "<!-- $myfield2 - ";

                    if (isset($_POST[$myfield2])) {echo htmlspecialchars((string) $_POST[$myfield2]);}

                    echo " -->\n";
                    echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                    echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                    //pre-check
                    if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                    echo " />&nbsp;"
                    .$oStatisticsHelper::_showSpeaker($niceqtext." ".str_replace("'", "`", (string) $row[15])." - # ".$flt[3])
                    ."</div>\n"
                    ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple' class='form-select'>\n";

                    //there are always exactly 5 values which have to be listed
                    for ($i=1; $i<=5; $i++)
                    {
                        echo "\t<option value='$i'";

                        //pre-select
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected='selected' ";}
                        if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {echo " selected='selected' ";}

                        echo ">$i</option>\n";
                    }

                    echo "\t</select>\n\t";
                    //add this to all the other fields
                }
                break;



            //just like above only a different loop
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS: // Array of 10 point choice questions
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                foreach($result[$key1] as $row)
                {
                    $myfield2 = $myfield . $row['title'];
                    echo "<!-- $myfield2 - ";

                    if (isset($_POST[$myfield2])) {echo htmlspecialchars((string) $_POST[$myfield2]);}

                    echo " -->\n";
                    echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';

                    echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                    if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                    echo " />&nbsp;"
                    .$oStatisticsHelper::_showSpeaker($niceqtext." ".str_replace("'", "`", (string) $row['question'])." - # ".$flt[3])
                    ."</div>\n"
                    ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row['title']}[]' multiple='multiple' class='form-select'>\n";

                    //here wo loop through 10 entries to create a larger output form
                    for ($i=1; $i<=10; $i++)
                    {
                        echo "\t<option value='$i'";
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected='selected' ";}
                        if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {echo " selected='selected' ";}
                        echo ">$i</option>\n";
                    }

                    echo "\t</select>\n\t";
                }
                break;



            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO: // ARRAY OF YES\No\gT("Uncertain") QUESTIONS
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                //loop answers
                foreach($result[$key1] as $row)
                {
                    $row=array_values($row);
                    $myfield2 = $myfield . "$row[4]";
                    echo "<!-- $myfield2 - ";

                    if (isset($_POST[$myfield2])) {echo htmlspecialchars((string) $_POST[$myfield2]);}

                    echo " -->\n";
                    echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';

                    echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                    if (isset($summary) && array_search($myfield2, $summary)!== FALSE)
                    {echo " checked='checked'";}

                    echo " />&nbsp;"
                    .$oStatisticsHelper::_showSpeaker($niceqtext." ".str_replace("'", "`", (string) $row[15])." - # ".$flt[3])
                    ."\n"
                    ."</div>\n"
                    ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple' class='form-select'>\n"
                    ."\t<option value='Y'";

                    //pre-select "yes"
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("Y", $_POST[$myfield2])) {echo " selected='selected' ";}

                    echo ">".gT("Yes")."</option>\n"
                    ."\t<option value='U'";

                    //pre-select "uncertain"
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("U", $_POST[$myfield2])) {echo " selected='selected' ";}

                    echo ">".gT("Uncertain")."</option>\n"
                    ."\t<option value='N'";

                    //pre-select "no"
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("N", $_POST[$myfield2])) {echo " selected='selected' ";}

                    echo ">".gT("No")."</option>\n"
                    ."\t</select>";
                    //add to array
                }
                break;



            //similiar to the above one
            case Question::QT_E_ARRAY_INC_SAME_DEC: // Array of Increase/Same/Decrease questions
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                foreach($result[$key1] as $row)
                {
                    $row=array_values($row);
                    $myfield2 = $myfield . "$row[4]";
                    echo "<!-- $myfield2 - ";

                    if (isset($_POST[$myfield2])) {echo htmlspecialchars((string) $_POST[$myfield2]);}

                    echo " -->\n";
                    echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                    echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                    if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                    echo " />&nbsp;"
                    .$oStatisticsHelper::_showSpeaker($niceqtext." ".str_replace("'", "`", (string) $row[15])." - # ".$flt[3])
                    ."\n"
                    ."</div>\n"
                    ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'  class='form-select'>\n"
                    ."\t<option value='I'";

                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("I", $_POST[$myfield2])) {echo " selected='selected' ";}

                    echo ">".gT("Increase")."</option>\n"
                    ."\t<option value='S'";

                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("S", $_POST[$myfield2])) {echo " selected='selected' ";}

                    echo ">".gT("Same")."</option>\n"
                    ."\t<option value='D'";

                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("D", $_POST[$myfield2])) {echo " selected='selected' ";}

                    echo ">".gT("Decrease")."</option>\n"
                    ."\t</select>";
                }

                $counter=0;
                break;

            case Question::QT_SEMICOLON_ARRAY_TEXT:  // Array (Text)
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                foreach($result[$key1] as $key => $row)
                {
                    $fresult = $fresults[$key1][$key];
                    foreach($fresult as $frow)
                    {
                        $myfield2 = "T".$myfield . $row['title'] . "_" . $frow['title'];
                        echo "<!-- $myfield2 - ";
                        if (isset($_POST[$myfield2])) {echo htmlspecialchars((string) $_POST[$myfield2]);}
                        echo " -->\n";
                        echo "<input type='checkbox'  name='summary[]' value='$myfield2'";
                        if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}
                        echo " />&nbsp;"
                        .$oStatisticsHelper::_showSpeaker($niceqtext." ".str_replace("'", "`", $row['question']." [".$frow['question']."]")." - ".$row['title']."/".$frow['title'])
                        ."<br />\n";
                        echo "\t<span class='smalltext'>".gT("Responses containing").":</span><br />\n"
                        .CHtml::textField($myfield2,$_POST[$myfield2] ?? '',array('class'=>'form-control') );
                        echo "<hr/>";
                        $counter2++;
                    }
                }
                $counter=0;
                break;

            case Question::QT_COLON_ARRAY_NUMBERS:  // Array (Numbers)
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                if (empty($qidattributes['input_boxes'])) {
                    // Inputs are dropdowns, so options are limited.
                    $minvalue = 1;
                    $maxvalue = 10;
                    if (trim((string) $qidattributes['multiflexible_max']) != '' && trim((string) $qidattributes['multiflexible_min']) == '') {
                        $maxvalue = $qidattributes['multiflexible_max'];
                        $minvalue = 1;
                    }
                    if (trim((string) $qidattributes['multiflexible_min']) != '' && trim((string) $qidattributes['multiflexible_max']) == '') {
                        $minvalue = $qidattributes['multiflexible_min'];
                        $maxvalue = $qidattributes['multiflexible_min'] + 10;
                    }
                    if (trim((string) $qidattributes['multiflexible_min']) != '' && trim((string) $qidattributes['multiflexible_max']) != '') {
                        if ($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']) {
                            $minvalue = $qidattributes['multiflexible_min'];
                            $maxvalue = $qidattributes['multiflexible_max'];
                        }
                    }

                    $stepvalue = (trim((string) $qidattributes['multiflexible_step']) != '' && $qidattributes['multiflexible_step'] > 0) ? $qidattributes['multiflexible_step'] : 1;

                    if ($qidattributes['reverse'] == 1) {
                        $tmp = $minvalue;
                        $minvalue = $maxvalue;
                        $maxvalue = $tmp;
                        $reverse = true;
                        $stepvalue = -$stepvalue;
                    } else {
                        $reverse = false;
                    }

                    if ($qidattributes['multiflexible_checkbox']!=0)
                    {
                        $minvalue=0;
                        $maxvalue=1;
                        $stepvalue=1;
                    }
                    foreach($result[$key1] as $row)
                    {
                        //$fresult = Question::model()->getQuestionsForStatistics('*', "parent_qid='{$row->qid}' AND language = '{$language}' AND scale_id = 1", 'question_order, title');
                        $fresult = Question::model()->with('questionl10ns')->findAll(array('condition' =>'parent_qid = ' . $row['parent_qid'] . ' AND scale_id = 1', 'order' => 'question_order ASC'));
                        foreach ($fresult as $frow) {
                            $myfield2 = $myfield . $row['title'] . "_" . $frow['title'];
                            echo "<!-- MyField2:  $myfield2 - ";
                            if (isset($_POST[$myfield2])) {echo htmlspecialchars((string) $_POST[$myfield2]);}
                            echo " -->\n";
                            echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                            if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}
                            echo "<input type='checkbox'  name='summary[]' value='$myfield2'";
                            if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}
                            echo " />&nbsp;"
                            .$oStatisticsHelper::_showSpeaker($niceqtext." ".str_replace("'", "`", $row['question']." [".$frow->questionl10ns[$language]->question."]")." - ".$row['title']."/".$frow['title'])
                            ."\n"
                            ."</div>\n";
                            echo "\t<select name='{$myfield2}[]' multiple='multiple' rows='5' cols='5' class='form-select'>\n";
                            for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue)
                            {
                                echo "\t<option value='$ii'";
                                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected='selected' ";}
                                echo ">$ii</option>\n";
                            }
                            echo "\t</select>";
                        }
                    }
                } else {
                    // Inputs are text inputs (numerical), so we treat each single subquestion as a numerical question
                    foreach($result[$key1] as $row) {
                        $fresult = Question::model()->with('questionl10ns')->findAll(array(
                            'condition' => 'parent_qid = ' . $row['parent_qid'] . ' AND scale_id = 1',
                            'order' => 'question_order ASC'
                        ));
                        foreach ($fresult as $frow) {
                            /*
                            * filter form for numerical input
                            * - checkbox
                            * - greater than
                            * - less than
                            */
                            $myfield1 = $myfield . $row['title'] . "_" . $frow['title'];
                            $myfield2 = $myfield1 . "G";
                            $myfield3 = $myfield1 . "L";
                            if ($counter2 == 4) {
                                echo "\t</tr>\n\t<tr>\n";
                                $counter2 = 0;
                            }

                            //checkbox
                            echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                            echo "<input type='checkbox'  name='summary[]' value='$myfield1'";

                            //check SGQA -> do we want to pre-check the checkbox?
                            if (isset($summary) && array_search($myfield1, $summary)!== FALSE) {
                                echo " checked='checked'";
                            }

                            echo " />&nbsp;";

                            //show speaker
                            echo $oStatisticsHelper::_showSpeaker($niceqtext." ".str_replace("'", "`", $row['question']." [".$frow->questionl10ns[$language]->question."]")." - ".$row['title']."/".$frow['title'])
                                . "</div>
                                <div class='mb-3 row'>
                                <label for='" . $myfield2 . "' class='col-md-4 form-label'>" . gT("Number greater than:") . "</label>
                                <div class='col-md-6'>"
                                . CHtml::numberField($myfield2, $_POST[$myfield2] ?? '', array('class'=>'form-control', 'step'=>'any'))
                                . "</div>
                                </div>
                                <div class='mb-3 row'>
                                <label for='N" . $myfield3 . "' class='col-md-4 form-label'>" . gT("Number less than:") . "</label>
                                <div class='col-md-6'>"
                                . CHtml::numberField($myfield3, $_POST[$myfield3] ?? '', array('class'=>'form-control', 'step'=>'any'))
                                . "</div>
                                </div>";
                        }
                    }
                }
                break;

            /*
            * For question type "F" and "H" you can use labels.
            * The only difference is that the labels are applied to column heading
            * or rows respectively
            */
            case Question::QT_F_ARRAY: // Array
            case Question::QT_H_ARRAY_COLUMN: // Array (By Column)

                //Get answers. We always use the answer code because the label might be too long elsewise
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                //check all the answers
                foreach($result[$key1] as $key=>$row)
                {
                    $myfield2 = $myfield . $row['title'];
                    echo "<!-- $myfield2 -->\n";

                    if ($counter2 == 4)
                    {
                        echo "\t</tr>\n\t<tr>\n";
                        $counter2=0;
                    }
                    echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                    echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                    if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {echo " checked='checked'";}

                    echo " />&nbsp;"
                    .$oStatisticsHelper::_showSpeaker($niceqtext." ".str_replace("'", "`", (string) $row['question'])." - # ".$flt[3])
                    ."</div>\n";

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

                    //creating form
                    echo "\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row['title']}[]' multiple='multiple' class='form-select'>\n";

                    //loop through all possible answers
                    foreach($fresult as $frow)
                    {
                        echo "\t<option value='{$frow['code']}'";

                        //pre-select
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected='selected' ";}

                        echo ">({$frow['code']}) ".flattenText($frow->answerl10ns[$language]->answer,true)."</option>\n";
                    }

                    echo "\t</select>";
                    //add fields to main array
                }

                break;



                case Question::QT_R_RANKING: // Ranking

                //get some answers
                //get number of columns
                $answersCount = count($result[$key1]);
                $maxDbAnswer=QuestionAttribute::model()->find("qid = :qid AND attribute = 'max_subquestions'",array(':qid' => $flt[0]));
                $columnsCount=(!$maxDbAnswer || intval($maxDbAnswer->value)<1) ? $answersCount : intval($maxDbAnswer->value); // If max_subquestions is not set or is invalid : get the answer count
                $columnsCount = min($columnsCount,$answersCount); // Can not be upper than current answers #14899
                //lets put the answer code and text into the answers array
                foreach($result[$key1] as $row)
                {
                    $answers[]=array($row->code, $row->answerl10ns[$language]->answer);
                }

                //loop through all answers. if there are 3 items to rate there will be 3 statistics
                for ($i=1; $i<=$columnsCount; $i++)
                {
                    //adjust layout depending on counter
                    //if ($counter2 == 4) {echo "\t</tr>\n\t<tr>\n"; $counter2=0;}

                    //myfield is the SGQ identifier
                    //myfield2 is just used as comment in HTML like "R40X34X1721-1"
                    $myfield2 = "R" . $myfield . $i . "-" . strlen($i);
                    $myfield3 = $myfield . $i;
                    echo "<!-- $myfield2 --> ";
                    echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                    if (isset($_POST[$myfield2])) {echo htmlspecialchars((string) $_POST[$myfield2]);}

                    echo "<input type='checkbox'  name='summary[]' value='$myfield2'";

                    //pre-check
                    if (isset($summary) && array_search($myfield2, $summary) !== FALSE) {echo " checked='checked'";}

                    echo " />&nbsp;"
                    .$oStatisticsHelper::_showSpeaker($niceqtext." ".str_replace("'", "`", (string) $row->answerl10ns[$language]->answer)." - # ".$flt[3])
                    ."</div>\n"
                    ."\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$i}[]' multiple='multiple' class='form-select'>\n";

                    //output lists of ranking items
                    foreach ($answers as $ans)
                    {
                        echo "\t<option value='$ans[0]'";

                        //pre-select
                        if (isset($_POST[$myfield3]) && is_array($_POST[$myfield3]) && in_array("$ans[0]", $_POST[$myfield3])) {echo " selected='selected' ";}

                        echo ">".flattenText($ans[1])."</option>\n";
                    }

                    echo "\t</select>";
                    //add averything to main array
                }

                //Link to rankwinner script - awaiting completion - probably never gonna happen. Mystery creator.
                //          echo "\t</tr>\n\t<tr bgcolor='#DDDDDD'>\n"
                //              ."<td colspan=$count align=center>"
                //              ."<input type='button' value='Show Rank Summary' onclick=\"window.open('rankwinner.php?sid=$surveyid&amp;qid=$flt[0]', '_blank', 'toolbar=no, directories=no, location=no, status=yes, menubar=no, resizable=no, scrollbars=no, width=400, height=300, left=100, top=100')\">"
                //              ."</td></tr>\n\t<tr>\n";
                $counter=0;
                unset($answers);
                break;


            case Question::QT_1_ARRAY_DUAL: // Dual scale

                //special dual scale counter
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                //loop through answers
                foreach($result[$key1] as $row)
                {
                    $row=array_values($row);

                    //----------------- LABEL 1 ---------------------
                    //myfield2 = answer code.
                    $myfield2 = $myfield . "$row[4]#0";

                    //3 lines of debugging output
                    echo "<!-- $myfield2 - ";
                    if (isset($_POST[$myfield2]))
                    {
                        echo htmlspecialchars(implode(',',$_POST[$myfield2]));
                    }
                    echo " -->\n";

                    echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
                    //output checkbox and question/label text
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

                    echo " />&nbsp;"
                    .$oStatisticsHelper::_showSpeaker($niceqtext." [".str_replace("'", "`", (string) $row[15])."] - ".gT("Label").": ".$labeltitle)
                    ."</div>\n";

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
                    echo "\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[4]}#0[]' multiple='multiple' class='form-select'>\n";

                    //list answers
                    foreach($fresult as $frow)
                    {
                        echo "\t<option value='{$frow['code']}'";

                        //pre-check
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected='selected' ";}

                        echo ">({$frow['code']}) ".flattenText($frow['answer'],true)."</option>\n";

                    }

                    echo "\t</select>";

                    //----------------- LABEL 2 ---------------------

                    //myfield2 = answer code
                    $myfield2 = $myfield . "$row[4]#1";

                    //3 lines of debugging output
                    echo "<!-- $myfield2 - ";
                    if (isset($_POST[$myfield2]))
                    {
                        echo htmlspecialchars(implode(',',$_POST[$myfield2]));
                    }

                    echo " -->\n";
                    echo '<div class="statistics-responses-label-group ls-space padding bottom-5 top-15 ls-flex-item">';
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

                    echo " />&nbsp;"
                    .$oStatisticsHelper::_showSpeaker($niceqtext." [".str_replace("'", "`", (string) $row[15])."] - ".gT("Label").": ".$labeltitle2)
                    ."</div>\n";
                    $fresult = Answer::model()->getQuestionsForStatistics('*', "qid='$flt[0]' AND language = '$language' AND scale_id = 1", 'sortorder, code');

                    //this is for debugging only
                    echo "\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[4]}#1[]' multiple='multiple' class='form-select'>\n";

                    //list answers
                    foreach($fresult as $frow)
                    {
                        echo "\t<option value='{$frow['code']}'";

                        //pre-check
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected='selected' ";}

                        echo ">({$frow['code']}) ".flattenText($frow['answer'],true)."</option>\n";

                    }

                    echo "\t</select>";

                }   //end WHILE -> loop through all answers

                break;

            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:  //P - Multiple choice with comments
            case Question::QT_M_MULTIPLE_CHOICE:  //M - Multiple choice
                echo '<h4 class="question-selector-title">'.$oStatisticsHelper::_showSpeaker($niceqtext).'</h4><br/>';
                //loop through answers
                foreach($result[$key1] as $row)
                {
                    echo "\t<option value='{$row['title']}'";

                    //pre-check
                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row['title'], $_POST[$myfield])) {echo " selected='selected' ";}

                    echo '>'.flattenText($row['question'],true)."</option>\n";
                }

                echo "\t</select>";
                break;

                /*
                * This question types use the default settings:
                *  L - List (Radio)
                O - List With Comment
                P - Multiple choice with comments
                ! - List (Dropdown)
                */
            default:
                echo "<!-- Default rendering in _question view -->";
                //loop through answers
                foreach($result[$key1] as $row)
                {
                    echo "\t<option value='{$row->code}'";

                    //pre-check
                    if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row->code, $_POST[$myfield])) {echo " selected='selected' ";}

                    echo '>'.flattenText($row->answerl10ns[$language]->answer,true)."</option>\n";
                }

                echo "\t</select>\n\t";
                //</td><div class='inerTableBox'>\n";
                break;

        }   //end switch -> check question types and create filter forms
    ?>
</div>
