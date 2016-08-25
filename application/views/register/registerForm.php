<?php echo CHtml::form($urlAction,'post',array('id'=>'limesurvey', 'role' => 'form', 'class' => 'form-horizontal col-sm-12 col-md-10 col-md-offset-1')); ?>
<input type="hidden" name="lang" value="<?php echo $sLanguage; ?>" id="register_lang" />
<div class='form-group col-sm-12'>
    <label for='register_firstname' class='control-label col-md-4'><?php eT("First name:"); ?></label>
    <div class="col-sm-12 col-md-6">
        <?php echo CHtml::textField('register_firstname', $sFirstName,array('id'=>'register_firstname','class'=>'form-control input-sm')); ?>
    </div>
</div>
<div class='form-group col-sm-12'>
    <label for='register_lastname' class='control-label col-md-4'><?php eT("Last name:"); ?></label>
    <div class="col-sm-12 col-md-6">
        <?php echo CHtml::textField('register_lastname', $sLastName,array('id'=>'register_lastname','class'=>'form-control input-sm')); ?>
    </div>
</div>
<div class='form-group col-sm-12'>
    <label for='register_email' class='control-label col-md-4'><span class="text-danger asterisk"></span> <?php eT("Email address:"); ?></label>
    <div class="col-sm-12 col-md-6">
        <?php echo CHtml::textField('register_email', $sEmail,array('id'=>'register_email','class'=>'form-control input-sm','required'=>'required')); ?>
    </div>
</div>
<?php foreach($aExtraAttributes as $key=>$aExtraAttribute){ ?>
    <div class='form-group col-sm-12'>
        <label for="register_<?php echo $key; ?>" class='control-label col-md-4'><?php echo $aExtraAttribute['mandatory'] == 'Y' ? '<span class="text-danger asterisk"></span>' : ""; ?> <?php echo $aExtraAttribute['caption']; ?></label>
        <div class="col-sm-12 col-md-6">
            <?php echo CHtml::textField("register_{$key}", $aAttribute[$key],array('id'=>"register_{$key}",'class'=>'form-control input-sm')); ?>
        </div>
    </div>
    <?php } ?>
<?php if($bCaptcha){ ?>
    <div class='form-group col-sm-12'>
        <label for="loadsecurity" class='control-label col-md-4 col-sm-12'>
            <p class="col-sm-6 col-md-12 remove-padding"><?php eT("Please enter the letters you see below:"); ?></p>
            <span class="col-sm-6 col-md-12">
                <?php $this->widget('CCaptcha',
                    array(
                        'buttonOptions'=>array('class'=> 'btn btn-xs btn-info'),
                        'buttonType' => 'button',
                        'buttonLabel' => gt('Reload image')
                )); ?>
            </span>
        </label>
        <div class="col-md-6 col-sm-12">
            <div>&nbsp;</div>
            <?php echo CHtml::textField('loadsecurity', '',array('id'=>'loadsecurity','class'=>'form-control input-sm','required'=>'required')); ?>
        </div>
    </div>
    <?php } ?>
    <div class='form-group col-sm-12'>
        <div class='col-md-4'></div>
        <div class="col-sm-12 col-md-6">
            <?php printf(gT('Fields marked with an asterisk (%s) are mandatory.'),'<span class="text-danger asterisk"></span>'); ?>
        </div>
    </div>

<div class='form-group col-sm-12'>
    <div class="col-sm-12 col-md-3 col-md-offset-9">
        <?php echo CHtml::submitButton(gT("Continue",'unescaped'),array('class'=>'btn-default btn-block btn','id'=>'register','name'=>'register')); ?>
    </div>
</div>
<?php echo CHtml::endForm(); ?>
