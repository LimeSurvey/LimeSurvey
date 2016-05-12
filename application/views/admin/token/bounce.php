<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'token'=>true, 'active'=>gT("Bounce settings"))); ?>
    <h3><?php eT("Bounce settings"); ?></h3>
    <div class="row">
        <div class="col-sm-12">
            <div id='bouncesettingsdiv'>
                <?php echo CHtml::form(array("admin/tokens/sa/bouncesettings/surveyid/$surveyid"), 'post',array('class'=>'form-core settingswidget form-horizontal','id'=>'bouncesettings','name'=>'frmeditquestion')); ?>

                        <div class="settings-list">

                            <!-- Survey bounce email -->
                            <div class=" form-group setting control-group setting-email" data-name="bounce_email">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounce_email">
                                    <?php eT('Survey bounce email address:'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input class='form-control' size="50" type="email" value="<?php echo $settings['bounce_email'];?>" name="bounce_email" id="bounce_email" />
                                </div>
                            </div>

                            <!-- Bounce settings to be used -->
                            <div class=" form-group setting control-group setting-select" data-name="bounceprocessing">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceprocessing">
                                    <?php eT('Used bounce settings:');?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <select id="bounceprocessing" name="bounceprocessing" class="form-control">
                                        <option value="N" <?php if ($settings['bounceprocessing']=='N'){echo 'selected="selected"'; }?> >
                                            <?php eT("None"); ?>
                                        </option>
                                        <option value="L" <?php if ($settings['bounceprocessing']=='L'){echo 'selected="selected"'; }?> >
                                            <?php eT("Use settings below"); ?>
                                        </option>
                                        <option value="G" <?php if ($settings['bounceprocessing']=='G'){echo 'selected="selected"'; }?> >
                                            <?php eT("Use global settings"); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div id="bounceparams">

                            <!-- Server type -->
                            <div class=" form-group setting control-group setting-select" data-name="bounceaccounttype">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccounttype">
                                    <?php eT("Server type:"); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <select id="bounceaccounttype" name="bounceaccounttype" class="form-control">
                                        <option value="IMAP" <?php if($settings['bounceaccounttype']=="IMAP"){echo "selected";}?> >
                                            <?php eT("IMAP"); ?>
                                        </option>

                                        <option value="POP" <?php if($settings['bounceaccounttype']=="POP"){echo "selected";}?>>
                                            <?php eT("POP"); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Server name port -->
                            <div class=" form-group setting control-group setting-string" data-name="bounceaccounthost">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccounthost">
                                    <?php eT('Server name & port:'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input size="50" type="text" value="<?php echo $settings['bounceaccounthost']; ?>" name="bounceaccounthost" id="bounceaccounthost" />
                                </div>
                            </div>




                            <!-- User name -->
                            <div class=" form-group setting control-group setting-string" data-name="bounceaccountuser">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccountuser">
                                    <?php eT('User name:'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input size="50" type="text" value="<?php echo $settings['bounceaccountuser'];?>" name="bounceaccountuser" id="bounceaccountuser" />
                                </div>
                            </div>

                            <!-- Password -->
                            <div class=" form-group setting control-group setting-password" data-name="bounceaccountpass">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccountpass">
                                    <?php eT('Password:'); ?>
                                </label>

                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input autocomplete="off" size="50" type="password" value="<?php echo $settings['bounceaccountpass'];?>" name="bounceaccountpass" id="bounceaccountpass" />
                                </div>
                            </div>

                            <!-- Encryption type  -->
                            <div class=" form-group setting control-group setting-select" data-name="bounceaccountencryption">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccountencryption">
                                    <?php eT('Encryption type:'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <select id="bounceaccountencryption" name="bounceaccountencryption" class="form-control">

                                        <option value="Off" <?php if($settings['bounceaccountencryption']=='Off' || $settings['bounceaccountencryption']==''){echo 'selected="selected"';}?>>
                                            <?php eT('None'); ?>
                                        </option>
                                        <option value="SSL"  <?php if($settings['bounceaccountencryption']=='SSL'){echo 'selected="selected"';}?> >
                                            <?php eT('SSL'); ?>
                                        </option>
                                        <option value="TLS" <?php if($settings['bounceaccountencryption']=='TLS'){echo 'selected="selected"';}?> >
                                            <?php eT('TLS'); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        </div>
                        <!-- buttons -->
                        <div class="buttons control-group  hidden">
                            <button name="save" value="save" class="btn" type="submit">Save bounce settings</button>
                            <a class="btn btn-link button" href="/LimeSurveyNext/index.php/admin/tokens?sa=index&amp;surveyid=274928">
                                Cancel
                            </a>
                        </div>


                </form>
            </div> <!-- bouncesettingsdiv -->
        </div> <!-- col -->
    </div> <!-- Row -->
</div> <!-- Side body -->
<?php App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "tokenbounce.js"); ?>
