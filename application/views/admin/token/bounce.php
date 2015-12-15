<div class="side-body">
    <h3><?php eT("Bounce settings"); ?></h3>
    <div class="row">
        <div class="col-sm-12">
            <div id='bouncesettingsdiv'>
                <?php echo CHtml::form(array("admin/tokens/sa/bouncesettings/surveyid/$surveyid"), 'post',array('class'=>'form-core settingswidget form-horizontal','id'=>'bouncesettings','name'=>'frmeditquestion')); ?>

                        <div class="settings-list">

                            <!-- Survey bounce email -->
                            <div class=" form-group setting control-group setting-email" data-name="bounce_email">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounce_email">
                                    <?php eT('Survey bounce email'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input size="50" type="email" value="<?php echo $settings['bounce_email'];?>" name="bounce_email" id="bounce_email" />
                                </div>
                            </div>

                            <!-- Bounce settings to be used -->
                            <div class=" form-group setting control-group setting-select" data-name="bounceprocessing">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceprocessing">
                                    <?php eT('Bounce settings to be used');?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <select id="bounceprocessing" name="bounceprocessing" class="form-control">
                                        <option value="N" <?php if ($settings['bounceprocessing']=='N'){echo 'selected="selected"'; }?> >
                                            <?php gT("None"); ?>
                                        </option>
                                        <option value="L" <?php if ($settings['bounceprocessing']=='L'){echo 'selected="selected"'; }?> >
                                            <?php gT("Use settings below"); ?>
                                        </option>
                                        <option value="G" <?php if ($settings['bounceprocessing']=='G'){echo 'selected="selected"'; }?> >
                                            <?php eT("Use global settings"); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Server type -->
                            <div class=" form-group setting control-group setting-select" data-name="bounceaccounttype">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccounttype">
                                    <?php eT("Server type"); ?>
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
                                    <?php eT('Server name & port'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input size="50" type="text" value="<?php echo $settings['bounceaccounthost']; ?>" name="bounceaccounthost" id="bounceaccounthost" />
                                </div>
                            </div>




                            <!-- User name -->
                            <div class=" form-group setting control-group setting-string" data-name="bounceaccountuser">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccountuser">
                                    <?php eT('User name'); ?>
                                </label>
                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input size="50" type="text" value="<?php echo $settings['bounceaccountuser'];?>" name="bounceaccountuser" id="bounceaccountuser" />
                                </div>
                            </div>

                            <!-- Password -->
                            <div class=" form-group setting control-group setting-password" data-name="bounceaccountpass">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccountpass">
                                    <?php eT('Password'); ?>
                                </label>

                                <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                                    <input autocomplete="off" size="50" type="password" value="<?php echo $settings['bounceaccountpass'];?>" name="bounceaccountpass" id="bounceaccountpass" />
                                </div>
                            </div>

                            <!-- Encryption type  -->
                            <div class=" form-group setting control-group setting-select" data-name="bounceaccountencryption">
                                <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="bounceaccountencryption">
                                    <?php eT('Encryption type'); ?>
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

                        <!-- buttons -->
                        <div class="buttons control-group  hidden">
                            <button name="save" value="save" class="btn" type="submit">Save bounce settings</button>
                            <a class="btn btn-link button" href="/LimeSurveyNext/index.php/admin/tokens?sa=index&amp;surveyid=274928">
                                Cancel
                            </a>
                        </div>


                </form>






                <?php
                    /* Script for disable some setting */
                    /*
                    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "tokenbounce.js");
                    App()->getClientScript()->registerScript('bounceSettings',"hideShowParameters();",CClientScript::POS_END);

                    $this->widget('ext.SettingsWidget.SettingsWidget', array(
                        'id'=>'bouncesettings',
                        //'title'=>gT("Bounce settings"),
                        'action' => array('admin/tokens', 'sa'=>'bouncesettings','surveyid'=>$surveyid),
                        'formHtmlOptions'=>array(
                            'class'=>'form-core',
                        ),
                        'inlist'=>true,
                        'settings' => array(
                            'bounce_email'=>array(
                                'type'=>'email',
                                'label'=>gT('Survey bounce email'),
                                'htmlOptions'=>array(
                                    'size'=>50,
                                ),
                                'current'=>$settings['bounce_email'],
                            ),
                            'bounceprocessing'=>array(
                                'type'=>'select',
                                'options'=>array(
                                    'N'=>gT("None"),
                                    'L'=>gT("Use settings below"),
                                    'G'=>gT("Use global settings"),
                                ),
                                'events'=>array(
                                    'change'=>'js: function(e) { hideShowParameters(); }',
                                ),
                                'label'=>gT('Bounce settings to be used'),
                                'current'=>$settings['bounceprocessing'],
                            ),
                            'bounceaccounttype'=>array(
                                'type'=>'select',
                                'options'=>array(
                                    'IMAP'=>gT("IMAP"),
                                    'POP'=>gT("POP"),
                                ),
                                'label'=>gT('Server type'),
                                'current'=>$settings['bounceaccounttype'],
                            ),
                            'bounceaccounthost'=>array(
                                'type'=>'string',
                                'label'=>gT('Server name & port'),
                                'htmlOptions'=>array(
                                    'size'=>50,
                                ),
                                'current'=>$settings['bounceaccounthost'],
                            ),
                            'bounceaccountuser'=>array(
                                'type'=>'string',
                                'label'=>gT('User name'),
                                'htmlOptions'=>array(
                                    'size'=>50,
                                ),
                                'current'=>$settings['bounceaccountuser'],
                            ),
                            'bounceaccountpass'=>array(
                                'type'=>'password',
                                'label'=>gT('Password'),
                                'htmlOptions'=>array(
                                    'size'=>50,
                                ),
                                'current'=>$settings['bounceaccountpass'],
                            ),
                            'bounceaccountencryption'=>array(
                                'type'=>'select',
                                'options'=>array(
                                    'Off'=>gT("None"),
                                    'SSL'=>gT("SSL"),
                                    'TLS'=>gT("TLS"),
                                ),
                                'label'=>gT('Encryption type'),
                                'current'=>$settings['bounceaccountencryption'],
                                'default'=>'Off',
                            ),
                        ),
                        'buttons' => array(
                            gT('Save bounce settings')=>array(
                                'type'=>'submit',
                                'htmlOptions'=>array(
                                    'name'=>'save',
                                    'value'=>'save',
                                ),
                            ),
                            gT('Cancel') => array(
                                'type' => 'link',
                                'href' => App()->createUrl('admin/tokens',array("sa"=>"index","surveyid"=>$surveyid)),
                            )
                        )
                    ));
                    */
                ?>



            </div> <!-- bouncesettingsdiv -->
        </div> <!-- col -->
    </div> <!-- Row -->
</div> <!-- Side body -->
