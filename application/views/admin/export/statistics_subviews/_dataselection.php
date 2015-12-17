<fieldset style='clear:both;'>
    <legend><?php eT("Data selection"); ?></legend>

    <div class="form-group">
        <label for='completionstate' class="col-sm-5 control-label"><?php eT("Include:"); ?> </label>

        <div class="col-sm-5">
            <select name='completionstate' id='completionstate' class='form-control'>
                <option value='all' <?php echo $selectshow; ?>><?php eT("All responses"); ?></option>
                <option value='complete' <?php echo $selecthide; ?> > <?php eT("Completed responses only"); ?></option>
                <option value='incomplete' <?php echo $selectinc; ?> > <?php eT("Incomplete responses only"); ?></option>
            </select>
        </div>
    </div>

    <div class='form-group'>
        <label class="col-sm-5 control-label" for='viewsummaryall'><?php eT("View summary of all available fields"); ?></label>
        <div class='col-sm-7'>
            <input type='checkbox' id='viewsummaryall' name='viewsummaryall' <?php if (isset($_POST['viewsummaryall'])) { echo "checked='checked'";} ?> />
        </div>
    </div>

    <div class='form-group'>
        <label class="col-sm-5 control-label" id='noncompletedlbl' for='noncompleted' title='<?php eT("Count stats for each question based only on the total number of responses for which the question was displayed"); ?>'><?php eT("Subtotals based on displayed questions"); ?></label>
        <div class='col-sm-7'>
            <input type='checkbox' id='noncompleted' name='noncompleted' <?php if (isset($_POST['noncompleted'])) {echo "checked='checked'"; } ?> />
        </div>
    </div>

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

    <div class='form-group'>
        <label for='statlang' class="col-sm-5 control-label" ><?php eT("Statistics report language"); ?></label>
        <div class='col-sm-5'>
            <select name="statlang" id="statlang" class="form-control"><?php echo $language_options; ?></select>
        </div>
    </div>
</fieldset>
