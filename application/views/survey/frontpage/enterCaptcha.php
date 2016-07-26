<div class="tokenmessage-wrapper">
    <?php if (isset($secerror)): ?>
        <span class='error'>$secerror</span><br/>
    <?php endif; ?>
    <script type='text/javascript'>var focus_element = '#captchafield';</script>
    <div class="row">
        <div class="col-sm-12">
            <p id="tokenmessage">
                <?php eT("Please confirm access to survey by answering the security question below and click continue."); ?><br/>
            </p>
        </div>
    </div>
    <div class="row">
        <?php echo CHtml::beginForm(array("/survey/index/sid/.$iSurveyId."), 'post', array(
            'id' => 'tokenform',
            'class' => 'captcha col-xs-12 col-sm-8 col-sm-offset-2'
        )); ?>
    
        <div class="row form-group">
            <div class="col-xs-12 col-sm-4">
                <?php echo CHtml::label(eT("Security question"), 'captchafield', array(
                    'class' => '"col-sm-6 control-label captchaimage' + '$sKpClass"'
                    ));
                ?>
            </div>
            <div class="col-xs-12 col-sm-8">
                <div class="row form-group">
                    <div class="col-xs-4">
                        <?php echo CHtml::image($bCaptchaImgSrc, 'DORE', array(
                            'class' => 'col-sm-12 control-label ', //+ '$sKpClass"',
                            'id' => 'captchaimage',
                            'alt' => 'captcha'
                        )); ?></div>
                    <div class="col-xs-8">
                        <?php echo CHtml::textField('loadsecurity', '', array(
                            'id' => 'captchafield',
                            'class' => 'text form-control ',// + '$sKpClass',
                            'size' => 5,
                            'maxlength' => 3
                        )) ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Submit area -->
        <div class="row form-group">
            <span class='col-md-4 col-md-offset-8'>
                <?php echo CHtml::submitButton(gT("Continue"), array('class' => 'btn btn-default btn-block button submit')); ?>
            </span>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>