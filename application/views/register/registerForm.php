<?php echo CHtml::form($urlAction,'post',array('id'=>'limesurvey', 'role' => 'form')); ?>
    <input type="hidden" name="lang" value="<?php echo $sLanguage; ?>" id="register_lang" />
    <div class='form-group'>
        <label for='register_firstname'><?php eT("First name"); ?></label>
        <?php echo CHtml::textField('register_firstname', $sFirstName,array('id'=>'register_firstname','class'=>'form-control')); ?>
    </div>
    <div class='form-group'>
        <label for='register_lastname'><?php eT("Last name"); ?></label>
        <?php echo CHtml::textField('register_lastname', $sLastName,array('id'=>'register_lastname','class'=>'form-control')); ?>
    </div>
    <div class='form-group'>
        <label for='register_email'><?php eT("Email address"); ?></label>
        <?php echo CHtml::textField('register_email', $sEmail,array('id'=>'register_email','class'=>'form-control')); ?>
    </div>
    <?php foreach($aExtraAttributes as $key=>$aExtraAttribute){ ?>
        <div class='form-group'>
            <label for="register_<?php echo $key; ?>"><?php echo $aExtraAttribute['caption']; ?><?php echo $aExtraAttribute['mandatory'] == 'Y' ? '*' : ""; ?></label>
            <?php echo CHtml::textField("register_{$key}", $aAttribute[$key],array('id'=>"register_{$key}",'class'=>'form-control')); ?>
        </div>
    <?php } ?>
    <?php if($bCaptcha){ ?>
    <div class='form-group'>
        <label for='loadsecurity'><?php eT("Security Question"); ?></label>
        <img src="<?php echo Yii::app()->getController()->createUrl('/verification/image/sid/'.$iSurveyId) ?>" alt='' class='captcha' />
        <?php echo CHtml::textField('loadsecurity', '',array('id'=>'loadsecurity','class'=>'form-control','size'=>'5','maxlength'=>'3')); ?>
    </div>
    <?php } ?>
    <div class='form-group'>
        <?php echo CHtml::submitButton(gT("Continue",'unescaped'),array('class'=>'btn-default btn','id'=>'register','name'=>'register')); ?>
    </div>
<?php echo CHtml::endForm(); ?>
