<div class="tokenmessage-wrapper">
    <?php if (isset($error)): ?>
        <span class='error'>$error</span><br/>
    <?php endif; ?>
    <script type='text/javascript'>var focus_element = '#captchafield';</script>
    <div class='jumbotron'>
        <div id="tokenmessage" class="container clearfix">
            <h3>
                <?php eT("Before you start, please prove you are human."); ?><br/>
            </h3>
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

        <div class="form-group col-sm-12">
            <label class="col-md-4 col-sm-12 control-label">
                <p class='col-sm-6 col-md-12 remove-padding'><?php eT("Please enter the letters you see below:"); ?></p>
                  <span class="col-md-12 col-sm-6">
                    <?php $this->widget('CCaptcha',array(
                        'buttonOptions'=>array('class'=> 'btn btn-xs btn-info'),
                        'buttonType' => 'button',
                        'buttonLabel' => gt('Reload image','unescaped')
                    )); ?>
                </span>
            </label>
            <div class="col-md-6">
                <div>&nbsp;</div>
                <?php echo CHtml::textField('loadsecurity', '', array(
                    'id' => 'captchafield',
                    'class' => 'text input-sm form-control '.$sKpClass,
                    'required' => 'required'
                )) ?>
            </div>
        </div>

        <!-- Submit area -->
        <div class="row form-group">
            <span class='col-sm-12 col-md-3 col-md-offset-9'>
                <input type='hidden' name='lang' value='<?php echo $sLangCode; ?>' />
                <?php echo CHtml::submitButton(gT("Continue"), array('class' => 'btn btn-default btn-block button submit')); ?>
            </span>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>