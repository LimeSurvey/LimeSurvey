<?php
/**
 * form
 * @todo : set some var to core : input name for starting
 */
?>
<div class=''>
    <div class='form-group'>
        <label class='control-label col-sm-3' for='loadname'><?php echo gT("Token:") ?><?php $this->renderPartial('/survey/system/required',array());?></label>
        <div class='col-sm-7 load-survey-input input-cell'>
            <?php if(!isset($token)): ?>
                <input class='form-control' type='password' id='token' name='token' value='' required>
            <?php else: ?>
                <?php
                    echo CHtml::passwordField('token',$token,array(
                        'id'=>'token',
                        'required'=>true,
                        'readonly'=>true,
                    ));
                ?>
            <?php endif; ?>
        </div>
    </div>
    <?php if (isset($bCaptchaEnabled)): ?>
        <div class='form-group captcha-item'>
            <!-- Don't seems aria capable -->
            <label class='control-label col-sm-3' for='loadsecurity'><?php echo gT("Please solve the following equation:") ?><?php $this->renderPartial('/survey/system/required',array());?></label>
            <div class='col-sm-7'>
                <div class='ls-input-group'>
                    <div class='ls-input-group-extra captcha-widget' >
                          <?php $this->widget('CCaptcha',array(
                              'buttonOptions'=>array('class'=> 'btn btn-xs btn-info'),
                              'buttonType' => 'button',
                              'buttonLabel' => gt('Reload image','unescaped')
                          )); ?>
                    </div>
                    <input class='form-control' type='text' size='15' maxlength='15' id='loadsecurity' name='loadsecurity' value='' alt='' required>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- hidden fields : move this to controller -->
    <?php echo CHtml::hiddenField('lang', $sLangCode, array('id' => 'lang')); ?>
    <?php if ($bNewTest): ?>
        <?php echo CHtml::hiddenField('newtest', "Y", array('id' => 'lang')); ?>
    <?php endif; ?>
    <?php
    if ($bDirectReload) : ?>
        <?php echo CHtml::hiddenField('loadall', $iSurveyId, array('id' => 'loadall')); ?>
        <?php echo CHtml::hiddenField('scid', $sCid, array('id' => 'scid')); ?>
        <?php echo CHtml::hiddenField('loadname', $Loadname, array('id' => 'loadname')); ?>
        <?php echo CHtml::hiddenField('loadpass', $sLoadpass, array('id' => 'loadpass')); ?>
    <?php endif; ?>

    <div class='form-group load-survey-row load-survey-submit'>
        <div class='col-sm-7 col-md-offset-3 load-survey-input input-cell'>
            <button type='submit' id='default' name="continue" class='btn btn-default' value='continue'><?php echo gT("Continue") ?></button>
        </div>
    </div>
</div>
