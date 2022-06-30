<input type="hidden" name="lang" value="<?php echo $sLanguage; ?>" id="register_lang" />
<div class=''>
    <div class='mb-3'>
        <label for='register_firstname' class='form-label col-md-5'><?php eT("First name:"); ?></label>
        <div class="col-md-7">
            <?php echo CHtml::textField('register_firstname', $sFirstName,array('id'=>'register_firstname','class'=>'form-control')); ?>
        </div>
    </div>

    <div class='mb-3'>
        <label for='register_lastname' class='form-label col-md-5'><?php eT("Last name:"); ?></label>
        <div class="col-md-7">
            <?php echo CHtml::textField('register_lastname', $sLastName,array('id'=>'register_lastname','class'=>'form-control')); ?>
        </div>
    </div>
    <div class='mb-3'>
        <label for='register_email' class='form-label col-md-5'><?php eT("Email address:"); ?><?php $this->renderPartial('/survey/system/required',array());?></label>
        <div class="col-md-7">
            <?php echo CHtml::textField('register_email', $sEmail,array('id'=>'register_email','class'=>'form-control input-sm','required'=>true)); ?>
        </div>
    </div>
<?php foreach($aExtraAttributes as $key=>$aExtraAttribute){ ?>
    <div class='mb-3'>
        <label for="register_<?php echo $key; ?>" class='form-label col-md-5'><?php echo $aExtraAttribute['caption']; ?><?php echo $aExtraAttribute['mandatory'] == 'Y' ? $this->renderPartial('/survey/system/required',array()): ""; ?></label>
        <div class="col-md-7">
            <?php echo CHtml::textField("register_{$key}", $aAttribute[$key],array('id'=>"register_{$key}",'class'=>'form-control input-sm')); ?>
        </div>
    </div>
    <?php } ?>
<?php if($bCaptcha){ ?>
    <div class='mb-3 captcha-item'>
        <label class='form-label col-md-5' for='loadsecurity'><?php echo gT("Please solve the following equation:") ?><?php $this->renderPartial('/survey/system/required',array());?></label>
        <div class='col-md-7'>
            <div class='input-group'>
                <div class='form-label captcha-widget' >
                      <?php $this->widget('CCaptcha',array(
                          'buttonOptions'=>array('class'=> 'btn btn-xs btn-info'),
                          'buttonType' => 'button',
                          'buttonLabel' => gT('Reload image','unescaped')
                      )); ?>
                </div>
                <input class='form-control' type='text' size='15' maxlength='15' id='loadsecurity' name='loadsecurity' value='' alt='' required>
            </div>
        </div>
    </div>
<?php } ?>
    <div class='row' aria-hidden='true'>
        <div class="col-md-7 offset-lg-5">
            <?php
                printf(gT('Fields marked with an asterisk (%s) are mandatory.'),'<small class="text-danger asterisk fa fa-asterisk small"></small>'); ?>
        </div>
    </div>

<div class='mb-3'>
    <div class='col-md-7 offset-lg-3'>
        <?php echo CHtml::submitButton(gT("Continue",'unescaped'),array('class'=>'btn-primary btn-block btn','id'=>'register','name'=>'register')); ?>
    </div>
</div>
