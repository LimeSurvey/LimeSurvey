<!-- Same HTML as login.php, but adapted for Ajax modal login -->
<div class="row text-center">
    <div id="panel-1">

        <!-- Header -->
        <div class="d-flex justify-content-center">
          <img alt="logo" id="profile-img" class="profile-img-card img-fluid" src="<?php echo LOGO_URL;?>" />
        </div>

        <!-- Action Name -->
        <div class="row login-title login-content">
            <div class="col-12">
                <h3><?php eT("Log in");?></h3>
                <p class='text-muted'><?php eT('You\'ve been logged out due to inactivity. Please log in again.'); ?></p>
            </div>
        </div>

        <!-- Form -->
        <?php echo CHtml::form(array('admin/authentication/sa/login'), 'post', array('id'=>'loginform', 'name'=>'loginform'));?>
            <div class="row login-content login-content-form">
                <div class="col-md-6 offset-md-3">
                    <?php
                        $pluginNames = array_keys($pluginContent);
                        if (!isset($defaultAuth))
                        {
                            // Make sure we have a default auth, if not set, use the first one we find
                            $defaultAuth = reset($pluginNames);
                        }

                        if (count($pluginContent)>1)
                        {
                            $selectedAuth = App()->getRequest()->getParam('authMethod', $defaultAuth);
                            if (!in_array($selectedAuth, $pluginNames))
                            {
                                $selectedAuth = $defaultAuth;
                            }
                    ?>

                   <label for='authMethod'><?php eT("Authentication method"); ?></label>
                        <?php
                            $possibleAuthMethods = array();
                            foreach($pluginNames as $plugin)
                            {
                                $info = App()->getPluginManager()->getPluginInfo($plugin);
                                $possibleAuthMethods[$plugin] = $info['pluginName'];
                            }
                            //print_r($possibleAuthMethods); die();

                            $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                                'name' => 'authMethod',
                                'data' => $possibleAuthMethods,
                                'value' => $selectedAuth,
                                'pluginOptions' => array(
                                    'options' => array(
                                            'onChange'=>'this.form.submit();'
                                            )
                            )));


                        }
                        else
                        {
                            echo CHtml::hiddenField('authMethod', $defaultAuth);
                            $selectedAuth = $defaultAuth;
                        }
                        if (isset($pluginContent[$selectedAuth]))
                        {
                            $blockData = $pluginContent[$selectedAuth];
                            /* @var $blockData PluginEventContent */
                            echo $blockData->getContent();
                        }

                        $languageData = array(
                            'default' => gT('Default')
                        );
                        foreach (getLanguageDataRestricted(true) as $sLangKey => $aLanguage)
                        {
                            $languageData[$sLangKey] =  html_entity_decode((string) $aLanguage['nativedescription'], ENT_NOQUOTES, 'UTF-8') . " - " . $aLanguage['description'];
                        }
                        echo CHtml::label(gT('Language'), 'loginlang');

                        $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                            'name' => 'loginlang',
                            'data' => $languageData,
                            'pluginOptions' => array(
                            'options' => array(
                            ),
                            'htmlOptions' => array(
                                'id' => 'loginlang'
                            ),
                            'value' => 'default'
                        )));
                        ?>

                        <?php   if (Yii::app()->getConfig("demoMode") === true && Yii::app()->getConfig("demoModePrefill") === true)
                        { ?>
                            <p><?php eT("Demo mode: Login credentials are prefilled - just click the Login button."); ?></p>
                            <?php
                        } ?>
                </div>
            </div>

            <!-- Buttons -->
            <div class="row login-submit login-content">
                <div class="col-12">
                        <p><input type='hidden' name='action' value='login' />
                           <input type='hidden' id='width' name='width' value='' />
                            <button type="submit" class="btn btn-outline-secondary" name='login_submit' value='login'><?php eT('Log in');?></button><br />
                            <br/>
                            <?php
                            if (Yii::app()->getConfig("display_user_password_in_email") === true)
                            {
                                ?>
                                <a href='<?php echo $this->createUrl("admin/authentication/sa/forgotpassword"); ?>'><?php eT("Forgot your password?"); ?></a><br />
                                <?php
                            }
                            ?>
                        </p>
                </div>

            </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<!-- Set focus on user input -->
<script type='text/javascript'>
$( document ).ready(function() {
    $('#user').focus();

    $('button[name="login_submit"]').unbind();
    $('button[name="login_submit"]').on('click', function(ev) {
        ev.preventDefault();
        var data = $('#loginform').serializeArray();;
        var url = $('#loginform').attr('action');
        console.log(data);
        console.log(url);

		var o = {};
		var a = data;
		$.each(a, function() {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});

        o.login_submit = 'login';

        LS.AjaxHelper.ajax({
            url: url + '&ajax=1',
            data: o,
            method: 'post',
            success: function(response, status) {
                console.log('ajaxLogin');
                console.log(response);
                console.log(response.loggedIn);

                if (!response.loggedIn) {
                    // TODO: Re-open login modal?
                }
            }
        });
        return false;
    });
});
</script>
