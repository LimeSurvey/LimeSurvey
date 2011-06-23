<div id='copy'>
    <form class='form30' action='<?php echo site_url('admin/survey/copy'); ?>' id='copysurveyform' method='post'>
                    <ul>
                    <li><label for='copysurveylist'><span class='annotationasterisk'>*</span><?php echo $clang->gT("Select survey to copy:"); ?> </label>
                    <select id='copysurveylist' name='copysurveylist'>
                    <?php  echo getsurveylist(false, true); ?> </select> <span class='annotation'><?php echo  $clang->gT("*Required"); ?> </span></li>
                    <li><label for='copysurveyname'><span class='annotationasterisk'>*</span><?php echo  $clang->gT("New survey title:"); ?> </label>
                    <input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' />
                    <span class='annotation'><?php echo  $clang->gT("*Required"); ?> </span></li>
                    <li><label for='copysurveytranslinksfields'><?php echo  $clang->gT("Convert resource links and INSERTANS fields?"); ?> </label>
                    <input id='copysurveytranslinksfields' name="copysurveytranslinksfields" type="checkbox" checked='checked'/></li>
                    <li><label for='copysurveyexcludequotas'><?php echo $clang->gT("Exclude quotas?") ; ?></label>
                    <input id='copysurveyexcludequotas' name="copysurveyexcludequotas" type="checkbox" /></li>
                    <li><label for='copysurveyexcludeanswers'><?php echo  $clang->gT("Exclude answers?"); ?> </label>
                    <input id='copysurveyexcludeanswers' name="copysurveyexcludeanswers" type="checkbox" /></li>
                    <li><label for='copysurveyresetconditions'><?php echo  $clang->gT("Reset conditions?") ; ?></label>
                    <input id='copysurveyresetconditions' name="copysurveyresetconditions" type="checkbox" /></li></ul>
                    <p><input type='submit' value='<?php echo $clang->gT("Copy survey"); ?>' />
                    <input type='hidden' name='action' value='copysurvey' /></p></form>

        
</div>