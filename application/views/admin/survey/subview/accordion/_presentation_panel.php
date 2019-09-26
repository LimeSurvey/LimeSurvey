<?php
/**
 * Presentation panel
 * @var AdminController $this
 * @var Survey $oSurvey
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyPresentationOptions');


 App()->getClientScript()->registerScript("presentation-panel-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
", LSYii_ClientScript::POS_BEGIN); 
?>

<?php 

$optionsQuestionIndex = array(
    0 => gT('Disabled','unescaped'),
    1 => gT('Incremental','unescaped'),
    2 => gT('Full','unescaped')
);
if ($bShowInherited){
    $optionsQuestionIndex['-1'] = gT('Inherit','unescaped').' ['. $oSurveyOptions->questionindex . ']';
}
?>

<!-- Presentation panel -->
<div id='presentation-panel' class="container-fluid">
    <div class="col-sm-12 col-md-6">
        <!-- Navigation delay -->
        <div class="form-group">
            <?php $navigationdelay = $oSurvey->navigationdelay; ?>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 content-right">
                    <label class=" control-label"  for='navigationdelay'><?php  eT("Navigation delay (seconds):"); ?></label>
                        <input class="form-control inherit-edit <?php echo ($bShowInherited && $navigationdelay === '-1' ? 'hide' : 'show'); ?>" type='text' size='50' id='navigationdelay' name='navigationdelay' value="<?php echo htmlspecialchars($navigationdelay); ?>" data-inherit-value="-1" data-saved-value="<?php echo $navigationdelay; ?>"/>
                        <input class="form-control inherit-readonly <?php echo ($bShowInherited && $navigationdelay === '-1' ? 'show' : 'hide'); ?>" type='text' size='50' value="<?php echo htmlspecialchars($oSurveyOptions->navigationdelay); ?>" readonly />
                </div>
                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 content-right <?php echo ($bShowInherited ? 'show' : 'hide'); ?>">
                    <label class=" control-label content-center col-sm-12"  for='navigationdelay'><?php  eT("Inherit:"); ?></label>
                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'navigationdelaybutton',
                        'value'=> ($bShowInherited && $navigationdelay === '-1' ? 'Y' : 'N'),
                        'selectOptions'=>$optionsOnOff,
                        'htmlOptions' => array(
                            'class' => 'text-option-inherit'
                            )
                        ));
                        ?>
                </div>
            </div>
        </div>

        <!-- Show question index -->
        <div class="form-group">
            <label class=" control-label" for='questionindex'><?php  eT("Show question index / allow jumping:"); ?></label>
            <div class="">

            <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                'name' => 'questionindex',
                'value'=> $oSurvey->questionindex ,
                'selectOptions'=>$optionsQuestionIndex
                ));
            ?>
            </div>
        </div>

        <?php
            $sel_showgri = array( 'B' => '' , 'D' => '' , 'N' => '' , 'X' => '' , 'I' => '' );
            if (isset($oSurvey->showgroupinfo))
            {
                $set_showgri = $oSurvey->showgroupinfo;
                $sel_showgri[$set_showgri] = ' selected="selected"';
            }
            if (empty($sel_showgri['B']) && empty($sel_showgri['D']) && empty($sel_showgri['N']) && empty($sel_showgri['X']) && empty($sel_showgri['I']))
                $sel_showgri['B'] = ' selected="selected"';
        ?>

        <!-- Show group name and/or group description -->
        <div class="form-group">
            <label class=" control-label" for="showgroupinfo"><?php  eT('Show group name and/or group description:'); ?></label>
            <div class="">
                <select id="showgroupinfo" name="showgroupinfo"  class="form-control" >
                    <?php if ($bShowInherited){ ?>
                        <option value="I"<?php echo $sel_showgri['I']; ?>><?php echo eT('Inherit').' ['. $oSurveyOptions->showgroupinfo . ']'; ?></option>
                    <?php } ?>
                    <option value="B"<?php echo $sel_showgri['B']; ?>><?php  eT('Show both'); ?></option>
                    <option value="N"<?php echo $sel_showgri['N']; ?>><?php  eT('Show group name only'); ?></option>
                    <option value="D"<?php echo $sel_showgri['D']; ?>><?php  eT('Show group description only'); ?></option>
                    <option value="X"<?php echo $sel_showgri['X']; ?>><?php  eT('Hide both'); ?></option>
                </select>
                <?php unset($sel_showgri,$set_showgri); ?>
            </div>
        </div>

        <?php
            $sel_showqnc = array( 'B' => '' , 'C' => '' , 'N' => '' , 'X' => '' , 'I' => '' );
            if (isset($oSurvey->showqnumcode)) {
                $set_showqnc = $oSurvey->showqnumcode;
                $sel_showqnc[$set_showqnc] = ' selected="selected"';
            }
            if (empty($sel_showqnc['B']) && empty($sel_showqnc['C']) && empty($sel_showqnc['N']) && empty($sel_showqnc['X']) && empty($sel_showqnc['I'])) {
                $sel_showqnc['X'] = ' selected="selected"';
            };
        ?>

        <!-- Show question number and/or code -->
        <div class="form-group">
            <label class=" control-label" for="showqnumcode"><?php  eT('Show question number and/or code:'); ?></label>
            <div class="">
                <select class="form-control" id="showqnumcode" name="showqnumcode">
                    <?php if ($bShowInherited){ ?>
                        <option value="I"<?php echo $sel_showqnc['I']; ?>><?php echo eT('Inherit').' ['. $oSurveyOptions->showqnumcode . ']'; ?></option>
                    <?php } ?>
                    <option value="B"<?php echo $sel_showqnc['B']; ?>><?php  eT('Show both'); ?></option>
                    <option value="N"<?php echo $sel_showqnc['N']; ?>><?php  eT('Show question number only'); ?></option>
                    <option value="C"<?php echo $sel_showqnc['C']; ?>><?php  eT('Show question code only'); ?></option>
                    <option value="X"<?php echo $sel_showqnc['X']; ?>><?php  eT('Hide both'); ?></option>
                </select>
                <?php unset($sel_showqnc,$set_showqnc);?>
            </div>
        </div>

        <!-- Show "No answer" -->
        <div class="form-group">
            <label class=" control-label" for="shownoanswer"><?php  eT('Show "No answer":'); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'shownoanswer',
                    'value'=> $oSurvey->shownoanswer,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->shownoanswer . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>
            
    </div>
    <div class="col-sm-12 col-md-6">
        
        <!-- Show "There are X questions in this survey" -->
        <div class="form-group">
            <label class=" control-label" for="showxquestions"><?php  eT('Show "There are X questions in this survey":'); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'showxquestions',
                    'value'=> $oSurvey->showxquestions,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->showxquestions . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>
 
        <!-- Show welcome screen -->
        <div class="form-group">
            <label class=" control-label" for='showwelcome'><?php  eT("Show welcome screen:") ; ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'showwelcome',
                    'value'=> $oSurvey->showwelcome,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->showwelcome . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>

        <!-- Allow backward navigation: -->
        <div class="form-group">
            <label class=" control-label" for='allowprev'><?php  eT("Allow backward navigation:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'allowprev',
                    'value'=> $oSurvey->allowprev,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->allowprev . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>


        <!-- Show on-screen keyboard -->
        <div class="form-group">
            <label class=" control-label" for='nokeyboard'><?php  eT("Show on-screen keyboard:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'nokeyboard',
                    'value'=> $oSurvey->nokeyboard,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->nokeyboard . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>

        <!-- Show progress bar -->
        <div class="form-group">
            <label class=" control-label" for='showprogress'><?php  eT("Show progress bar:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'showprogress',
                    'value'=> $oSurvey->showprogress,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->showprogress . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>
        <!-- Participants may print answers -->
        <div class="form-group">
            <label class=" control-label" for='printanswers'><?php  eT("Participants may print answers:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'printanswers',
                    'value'=> $oSurvey->printanswers,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->printanswers . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>

        <!-- Public statistics -->
        <div class="form-group">
            <label class=" control-label" for='publicstatistics'><?php  eT("Public statistics:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'publicstatistics',
                    'value'=> $oSurvey->publicstatistics,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->publicstatistics . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>

        <!-- Show graphs in public statistics -->
        <div class="form-group">
            <label class=" control-label" for='publicgraphs'><?php  eT("Show graphs in public statistics:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'publicgraphs',
                    'value'=> $oSurvey->publicgraphs,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->publicgraphs . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>
    
        <!-- Automatically load URL -->
        <div class="form-group">
            <label class=" control-label" for='autoredirect'><?php  eT("Automatically load end URL when survey complete:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhButtonGroup', array(
                    'name' => 'autoredirect',
                    'value'=> $oSurvey->autoredirect,
                    'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->autoredirect . ']')): $optionsOnOff
                    ));
                ?>
            </div>
        </div>
    </div>
</div>
