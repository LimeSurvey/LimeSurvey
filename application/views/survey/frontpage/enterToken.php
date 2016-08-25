<div class="tokenmessage-wrapper">
    <?php if (isset($error)): ?>
        <span class='error'>$error</span><br/>
    <?php endif; ?>
    <script type='text/javascript'>var focus_element = '#token';</script>
    <div class='jumbotron'>
        <div id="tokenmessage" class="container clearfix">
            <h3><?php eT("To participate in this restricted survey, you need a valid token."); ?></h3>
            <?php if(!isset($token)): ?>
            <h3><small class='text-info'><?php eT("If you have been issued a token, please enter it in the box below and click continue."); ?></small></h3>
            <?php else: ?>
            <h3><small class='text-info'><?php eT("Please confirm the token by answering the security question below and click continue."); ?></small></h3>
            <?php endif; ?>
            </p>
        </div>
    </div>

    <?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $errorMessage; ?>
    </div>
    <?php endif; ?>

    <div class="container">
        <?php echo CHtml::beginForm(array("/survey/index/sid/{$iSurveyId}"), 'post', array(
            'id' => 'tokenform',
            'class' => 'form-horizontal col-sm-12 col-md-10 col-md-offset-1'
        )); ?>
        <div class="col-sm-12 form-group">
                <?php echo CHtml::label(gT("Token: "), 'token', array(
                    'class' => 'control-label col-sm-12 col-md-4 '.$sKpClass,
                    ));
                ?>
            <div class="col-sm-12 col-md-6">

                <?php if(!isset($token)): ?>
                    <?php echo CHtml::passwordField('token', '', array(
                        'class' => 'text input-sm form-control '.$sKpClass,
                        'required' => 'required',
                        'id' => 'token'));
                    ?>
                <?php else: ?>
                    <?php echo CHtml::passwordField('visibleToken', $visibleToken, array(
                        'id' => 'visibleToken',
                        'class' => 'text input-sm form-control '.$sKpClass,
                        'disabled'=>'disabled',
                        'data-value' => $visibleToken,
                        'value' => $visibleToken,
                        ));
                    ?>
                    <?php echo CHtml::hiddenField('token', $token, array(
                        'class'=>$sKpClass,
                        'id' => 'token',
                        'data-value' => $token,
                        'value' => $token));
                    ?>
                <?php endif; ?>

                <?php echo CHtml::hiddenField('sid', $iSurveyId, array('id' => 'sid')); ?>
                <?php echo CHtml::hiddenField('lang', $sLangCode, array('id' => 'lang')); ?>

                <?php if ($bNewTest): ?>
                    <?php echo CHtml::hiddenField('lang', $sLangCode, array('id' => 'lang')); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        if ($bDirectReload) : ?>
            <?php echo CHtml::hiddenField('loadall', $iSurveyId, array('id' => 'loadall')); ?>
            <?php echo CHtml::hiddenField('scid', $sCid, array('id' => 'scid')); ?>
            <?php echo CHtml::hiddenField('loadname', $Loadname, array('id' => 'loadname')); ?>
            <?php echo CHtml::hiddenField('loadpass', $sLoadpass, array('id' => 'loadpass')); ?>
        <?php endif; ?>


        <?php if (isset($bCaptchaEnabled)): ?>
            <div class="col-sm-12 form-group">
                <label class="col-md-4 col-sm-12 control-label">
                    <p class='col-sm-6 col-md-12 remove-padding'><?php eT("Please enter the letters you see below:"); ?></p>
                    <span class="col-sm-6 col-md-12">
                        <?php $this->widget('CCaptcha',array(
                            'buttonOptions'=>array('class'=> 'btn btn-xs btn-info'),
                            'buttonType' => 'button',
                            'buttonLabel' => gt('Reload image')
                        )); ?>
                    </span>
                </label>
                <div class="col-sm-6">
                    <div>&nbsp;</div>
                    <?php echo CHtml::textField('loadsecurity', '', array(
                        'id' => 'captchafield',
                        'class' => 'text input-sm form-control '.$sKpClass,
                        'required' => 'required'
                    )) ?>
                </div>
            </div>
        <?php endif; ?>
        <!-- Submit area -->
        <div class="row form-group">
            <span class='col-sm-12 col-md-3 col-md-offset-9'>
                <?php echo CHtml::submitButton(gT("Continue"), array('class' => 'btn btn-default btn-block button submit')); ?>
            </span>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>