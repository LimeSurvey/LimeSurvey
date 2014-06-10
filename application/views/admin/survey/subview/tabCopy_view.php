<div id='copy'>
    <?php echo CHtml::form(array('admin/survey/sa/copy'), 'post', array('id'=>'copysurveyform', 'name'=>'copysurveyform', 'class'=>'form30')); ?>
        <ul>
            <li><label for='copysurveylist'><?php $clang->eT("Select survey to copy:"); ?> </label>
                <select id='copysurveylist' name='copysurveylist' required="required">
                    <?php echo getSurveyList(false); ?> </select> <span class='annotation'><?php echo $clang->gT("Required"); ?> </span></li>
            <li><label for='copysurveyname'><?php echo $clang->gT("New survey title:"); ?> </label>
                <input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' required="required" />
                <span class='annotation'><?php echo $clang->gT("Required"); ?> </span></li>
            <li><label for='copysurveyid'><?php echo $clang->gT("New survey id:"); ?> </label>
                <input type='text' id='copysurveyid' size='82' maxlength='6' name='copysurveyid' value=''/>
                <span class='annotation'><?php echo $clang->gT("Optional"); ?> </span></li>

            <li><label for='copysurveytranslinksfields'><?php echo $clang->gT("Convert resource links and INSERTANS fields?"); ?> </label>
                <input id='copysurveytranslinksfields' name="copysurveytranslinksfields" type="checkbox" checked='checked'/></li>
            <li><label for='copysurveyexcludequotas'><?php $clang->eT("Exclude quotas?"); ?></label>
                <input id='copysurveyexcludequotas' name="copysurveyexcludequotas" type="checkbox" /></li>
            <li><label for='copysurveyexcludepermissions'><?php echo $clang->gT("Exclude survey permissions?"); ?> </label>
                <input id='copysurveyexcludepermissions' name="copysurveyexcludepermissions" type="checkbox"/></li>
            <li><label for='copysurveyexcludeanswers'><?php echo $clang->gT("Exclude answers?"); ?> </label>
                <input id='copysurveyexcludeanswers' name="copysurveyexcludeanswers" type="checkbox" /></li>
            <li><label for='copysurveyresetconditions'><?php echo $clang->gT("Reset conditions/relevance?"); ?></label>
                <input id='copysurveyresetconditions' name="copysurveyresetconditions" type="checkbox" /></li>
            <li><label for='copysurveyresetstartenddate'><?php echo $clang->gT("Reset start/end date/time?"); ?></label>
                <input id='copysurveyresetstartenddate' name="copysurveyresetstartenddate" type="checkbox" /></li>
        </ul>
        <p><input type='submit' value='<?php $clang->eT("Copy survey"); ?>' />
            <?php if (isset($surveyid)) echo '<input type="hidden" name="sid" value="' . $surveyid . '" />'; ?>
            <input type='hidden' name='action' value='copysurvey' /></p>
    </form>
</div>
