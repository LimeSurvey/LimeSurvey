<div class='side-body'>
    <h2 class="h3"><?php eT("Bounce settings"); ?></h2>
    <div class="row">
        <div class="col-12">
            <div id='bouncesettingsdiv'>
                <?php echo CHtml::form(array("admin/tokens/sa/bouncesettings/surveyid/$surveyid"), 'post', array('class' => 'form-core settingswidget ','id' => 'bouncesettings','name' => 'frmeditquestion')); ?>

                        <div class="settings-list">

                            <!-- Survey bounce email -->
                            <div class="mb-3 setting control-group setting-email col-3">
                                <label class="default form-label" for="bounce_email">
                                    <?php eT('Survey bounce email address'); ?>
                                </label>
                                <div class="default controls">
                                    <input class='form-control' size="50" type="email" value="<?php echo $settings['bounce_email'];?>" name="bounce_email" id="bounce_email" />
                                </div>
                            </div>

                            <!-- Bounce settings to be used -->
                            <div class="mb-3 setting control-group setting-select">
                                <label class="default form-label" for="bounceprocessing">
                                    <?php eT('Used bounce settings');?>
                                </label>
                                <div class="default controls">
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', array(
                                        'name' => 'bounceprocessing',
                                        'value'=> $settings['bounceprocessing'] ,
                                        'ariaLabel'=> gT('Used bounce settings'),
                                        'checkedOption'=> $settings['bounceprocessing'] ,
                                        'selectOptions' => array(
                                            "N" => gT("None", 'unescaped'),
                                            "L" => gT("Use settings below", 'unescaped'),
                                            "G" => gT("Use global settings", 'unescaped')
                                        )
                                    ));?>
                                </div>
                            </div>

                            <div id="bounceparams">

                            <!-- Server type -->
                            <div class=" mb-3 setting control-group setting-select">
                                <label class="default form-label" for="bounceaccounttype">
                                    <?php eT("Server type"); ?>
                                </label>
                                <div class="default controls">
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', array(
                                        'name' => 'bounceaccounttype',
                                        'checkedOption'=> $settings['bounceaccounttype'] ,
                                        'ariaLabel'=> gT('Server type'),
                                        'selectOptions' => array(
                                            "IMAP" => gT("IMAP", 'unescaped'),
                                            "POP" => gT("POP", 'unescaped')
                                        )
                                    ));?>
                                </div>
                            </div>

                            <!-- Server name port -->
                            <div class=" mb-3 setting control-group setting-string col-3">
                                <label class="default form-label" for="bounceaccounthost">
                                    <?php eT('Server name & port'); ?>
                                </label>
                                <div class="default controls">
                                    <input class="form-control" size="50" type="text" value="<?php echo $settings['bounceaccounthost']; ?>" name="bounceaccounthost" id="bounceaccounthost" />
                                </div>
                            </div>




                            <!-- User name -->
                            <div class=" mb-3 setting control-group setting-string col-3">
                                <label class="default form-label" for="bounceaccountuser">
                                    <?php eT('User name'); ?>
                                </label>
                                <div class="default controls">
                                    <input class="form-control" size="50" type="text" value="<?php echo $settings['bounceaccountuser'];?>" name="bounceaccountuser" id="bounceaccountuser" />
                                </div>
                            </div>

                            <!-- Password -->
                            <div class=" mb-3 setting control-group setting-password col-3">
                                <label class="default form-label" for="bounceaccountpass">
                                    <?php eT('Password'); ?>
                                </label>

                                <div class="default controls">
                                    <input class="form-control" autocomplete="off" size="50" type="password" value="somepassword" name="bounceaccountpass" id="bounceaccountpass" />
                                </div>
                            </div>

                            <!-- Encryption type  -->
                            <div class=" mb-3 setting control-group setting-select">
                                <label class="default form-label" for="bounceaccountencryption">
                                    <?php eT('Encryption type'); ?>
                                </label>
                                <div class="default controls">
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', array(
                                        'name' => 'bounceaccountencryption',
                                        'ariaLabel'=> gT('Encryption type'),
                                        'checkedOption'=> strtolower((string) $settings['bounceaccountencryption']),
                                        'selectOptions' => array(
                                            "off" => gT("Off (unsafe)", 'unescaped'),
                                            "ssl" => gT("SSL/TLS", 'unescaped'),
                                            "tls" => gT("StartTLS", 'unescaped')
                                        )
                                    ));?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- buttons -->
                    <div class="buttons control-group d-none">
                        <button name="save" value="save" class="btn" type="submit">Save bounce settings</button>
                    </div>
                </form>
            </div> <!-- bouncesettingsdiv -->
        </div> <!-- col -->
    </div> <!-- Row -->
</div> <!-- Side body -->

<?php App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'tokenbounce.js'); ?>
