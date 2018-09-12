<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'token'=>true, 'active'=>gT("Bounce settings"))); ?>
    <h3><?php eT("Bounce settings"); ?></h3>
    <div class="row">
        <div class="col-sm-12">
            <div id='bouncesettingsdiv'>
                <?php echo CHtml::form(array("admin/tokens/sa/bouncesettings/surveyid/$surveyid"), 'post',array('class'=>'form-core settingswidget form-horizontal','id'=>'bouncesettings','name'=>'frmeditquestion')); ?>

                        <div class="settings-list">

                            <!-- Survey bounce email -->
                            <div class="form-group setting control-group setting-email">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounce_email">
                                    <?php eT('Survey bounce email address:'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input class='form-control' size="50" type="email" value="<?php echo $settings['bounce_email'];?>" name="bounce_email" id="bounce_email" />
                                </div>
                            </div>

                            <!-- Bounce settings to be used -->
                            <div class="form-group setting control-group setting-select">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceprocessing">
                                    <?php eT('Used bounce settings:');?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                                        'name' => 'bounceprocessing',
                                        'value'=> $settings['bounceprocessing'] ,
                                        'selectOptions'=>array(
                                            "N"=>gT("None",'unescaped'),
                                            "L"=>gT("Use settings below",'unescaped'),
                                            "G"=>gT("Use global settings",'unescaped')
                                        )
                                    ));?>
                                </div>
                            </div>

                            <div id="bounceparams">

                            <!-- Server type -->
                            <div class=" form-group setting control-group setting-select">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccounttype">
                                    <?php eT("Server type:"); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                                        'name' => 'bounceaccounttype',
                                        'value'=> $settings['bounceaccounttype'] ,
                                        'selectOptions'=>array(
                                            "IMAP"=>gT("IMAP",'unescaped'),
                                            "POP"=>gT("POP",'unescaped')
                                        )
                                    ));?>
                                </div>
                            </div>

                            <!-- Server name port -->
                            <div class=" form-group setting control-group setting-string">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccounthost">
                                    <?php eT('Server name & port:'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input size="50" type="text" value="<?php echo $settings['bounceaccounthost']; ?>" name="bounceaccounthost" id="bounceaccounthost" />
                                </div>
                            </div>




                            <!-- User name -->
                            <div class=" form-group setting control-group setting-string">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccountuser">
                                    <?php eT('User name:'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input size="50" type="text" value="<?php echo $settings['bounceaccountuser'];?>" name="bounceaccountuser" id="bounceaccountuser" />
                                </div>
                            </div>

                            <!-- Password -->
                            <div class=" form-group setting control-group setting-password">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccountpass">
                                    <?php eT('Password:'); ?>
                                </label>

                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input autocomplete="off" size="50" type="password" value="<?php echo $settings['bounceaccountpass'];?>" name="bounceaccountpass" id="bounceaccountpass" />
                                </div>
                            </div>

                            <!-- Encryption type  -->
                            <div class=" form-group setting control-group setting-select">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccountencryption">
                                    <?php eT('Encryption type:'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                                        'name' => 'bounceaccountencryption',
                                        'value'=> $settings['bounceaccountencryption'] ,
                                        'selectOptions'=>array(
                                            "Off"=>gT("Off",'unescaped'),
                                            "SSL"=>gT("SSL",'unescaped'),
                                            "TLS"=>gT("TLS",'unescaped')
                                        )
                                    ));?>
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

<?php
    $oAdminTheme = AdminTheme::getInstance();
    $oAdminTheme->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'tokenbounce.js');
?>
