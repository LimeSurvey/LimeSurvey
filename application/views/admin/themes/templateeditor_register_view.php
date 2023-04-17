<input type="hidden" name="lang" value="en" id="register_lang" />
<div class='mb-3'>
    <label for='register_firstname'><?php eT("First name"); ?></label>
    <?php echo CHtml::textField('register_firstname', '', array('id'=>'register_firstname','class'=>'form-control')); ?>
</div>
<div class='mb-3'>
    <label for='register_lastname'><?php eT("Last name"); ?></label>
    <?php echo CHtml::textField('register_lastname', '', array('id'=>'register_lastname','class'=>'form-control')); ?>
</div>
<div class='mb-3'>
    <label for='register_email'><?php eT("Email address"); ?></label>
    <?php echo CHtml::textField('register_email', '', array('id'=>'register_email','class'=>'form-control')); ?>
</div>
<div class='mb-3'>
    <?php echo CHtml::submitButton(gT("Continue",'unescaped'),array('class'=>'btn-outline-secondary btn','id'=>'register','name'=>'register')); ?>
</div>
