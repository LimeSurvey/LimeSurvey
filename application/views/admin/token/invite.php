<?php
/**
 * Send email invitations
 */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'token'=>true, 'active'=>gT("Send email invitations"))); ?>
    <h3><?php eT("Send email invitations"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo PrepareEditorScript(true, $this); ?>
            <div>
                <?php if ($thissurvey[$baselang]['active'] != 'Y'): ?>
                    <div class="jumbotron message-box message-box-error">
                        <h2 class='text-warning'><?php eT('Warning!'); ?></h2>
                        <p class="lead text-warning">
                            <?php eT("This survey is not yet activated and so your participants won't be able to fill out the survey."); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div>
                    <?php echo CHtml::form(array("admin/tokens/sa/email/surveyid/{$surveyid}"), 'post', array('id'=>'sendinvitation', 'name'=>'sendinvitation', 'class'=>'form-horizontal')); ?>
                    <ul class="nav nav-tabs">
                        <?php
                        $c = true;
                            foreach ($surveylangs as $language)
                            {
                                echo '<li role="presentation"';

                                if ($c)
                                {
                                    $c=false;
                                    echo ' class="active"';
                                }

                                echo '><a  data-toggle="tab" href="#'.$language.'">' . getLanguageNameFromCode($language, false);
                                if ($language == $baselang)
                                {
                                    echo "(" . gT("Base language") . ")";
                                }
                                echo "</a></li>";
                            }
                        ?>
                    </ul>

                    <div class="tab-content">

                        <?php
                        $c = true;
                        foreach ($surveylangs as $language)
                        {
                                $fieldsarray["{ADMINNAME}"] = $thissurvey[$baselang]['adminname'];
                                $fieldsarray["{ADMINEMAIL}"] = $thissurvey[$baselang]['adminemail'];
                                $fieldsarray["{SURVEYNAME}"] = $thissurvey[$language]['name'];
                                $fieldsarray["{SURVEYDESCRIPTION}"] = $thissurvey[$language]['description'];
                                $fieldsarray["{EXPIRY}"] = $thissurvey[$baselang]["expiry"];

                                $subject = Replacefields($thissurvey[$language]['email_invite_subj'], $fieldsarray, false);
                                $textarea = Replacefields($thissurvey[$language]['email_invite'], $fieldsarray, false);
                                if ($ishtml !== true)
                                {
                                    $textarea = str_replace(array('<x>', '</x>'), array(''), $textarea);
                                }
                            ?>
                            <div id="<?php echo $language; ?>" class="tab-pane fade in <?php if ($c){$c=false;echo ' active';}?>">

                                <div class='form-group'>
                                    <label class='control-label col-sm-2' for='from_<?php echo $language; ?>'><?php eT("From:"); ?></label>
                                    <div class='col-sm-4'>
                                        <?php echo CHtml::textField("from_{$language}",$thissurvey[$baselang]['adminname']." <".$thissurvey[$baselang]['adminemail'].">",array('class' => 'form-control')); ?>
                                    </div>
                                </div>

                                <div class='form-group'>
                                    <label class='control-label col-sm-2' for='subject_<?php echo $language; ?>'><?php eT("Subject:"); ?></label>
                                    <div class='col-sm-4'>
                                        <?php echo CHtml::textField("subject_{$language}",$subject,array('class' => 'form-control')); ?>
                                    </div>
                                </div>

                                <div class='form-group'>

                                    <label class='control-label col-sm-2' for='message_<?php echo $language; ?>'><?php eT("Message:"); ?></label>
                                    <div class='col-sm-6'>
                                        <div class="htmleditor">
                                            <?php echo CHtml::textArea("message_{$language}",$textarea,array('cols'=>80,'rows'=>20, 'class' => 'form-control')); ?>
                                            <?php echo getEditor("email-inv", "message_$language", "[" . gT("Invitation email:", "js") . "](" . $language . ")", $surveyid, '', '', "tokens"); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if (count($tokenids)>0): ?>
                        <div class='form-group'>
                            <label class='control-label col-sm-2'><?php eT("Send invitation email to token ID(s):"); ?></label>
                            <div class='col-sm-4'>
                                <?php echo short_implode(", ", "-", (array) $tokenids); ?>
                            </div>
                        </div>

                    <?php endif; ?>

                    <div class='form-group'>

                        <label class='control-label col-sm-2' for='bypassbademails'><?php eT("Bypass token with failing email addresses:"); ?></label>
                        <div class='col-sm-1'>
                            <?php
                            $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => "bypassbademails",
                                'id'=>"bypassbademails",
                                'value' => '1',
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                        </div>
                    </div>

                    <div class='form-group'>
                        <?php echo CHtml::label(gT("Bypass date control before sending email:"),'bypassdatecontrol', array('title'=>gt("If some tokens have a 'valid from' date set which is in the future, they will not be able to access the survey before that 'valid from' date."),'unescaped', 'class' => 'control-label col-sm-2')); ?>
                        <div class='col-sm-1'>
                            <?php
                            $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => "bypassdatecontrol",
                                'id'=>"bypassdatecontrol",
                                'value' => '0',
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                        </div>
                    </div>

                    <div class='form-group'>
                        <div class='col-sm-2'></div>
                        <div class='col-sm-1'>
                            <?php echo CHtml::submitButton(gT("Send Invitations",'unescaped'), array('class'=>'btn btn-default')); ?>
                        </div>

                        <?php
                            echo CHtml::hiddenField('ok','absolutely');
                            echo CHtml::hiddenField('subaction','invite');
                            if (!empty($tokenids)) {
                                echo CHtml::hiddenField('tokenids',implode('|', (array) $tokenids));
                            }
                        ?>
                    </div>
                </form>
            </div>
        </form>
    </div>
</div>

<script>
    $( document ).ready(function(){
        $('#send-invitation-button').on('click', function(){
            $("#sendinvitation").submit();
        })
    });
</script>
