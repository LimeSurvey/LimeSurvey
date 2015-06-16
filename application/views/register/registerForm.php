<?php echo CHtml::form($urlAction,'post',array('id'=>'limesurvey')); ?>
    <input type="hidden" name="lang" value="<?php echo $sLanguage; ?>" id="register_lang" />
    <table class="register register-form-table" summary="<?php eT('A table with a registration form'); ?>">
    <tbody>
    <tr class="register-form-row register-form-fname">
        <th class="register-form-label label-cell"><label for='register_firstname'><?php eT("First name"); ?></label></th>
        <td class="register-form-input input-cell" >
            <?php echo CHtml::textField('register_firstname', $sFirstName,array('id'=>'register_firstname','class'=>'text')); ?>
        </td>
    </tr>
    <tr class="register-form-row register-form-lname">
        <th class="register-form-label label-cell"><label for='register_lastname'><?php eT("Last name"); ?></label></th>
        <td class="register-form-input input-cell" >
            <?php echo CHtml::textField('register_lastname', $sLastName,array('id'=>'register_lastname','class'=>'text')); ?>
        </td>
    </tr>
    <tr class="register-form-row register-form-email">
        <th class="register-form-label label-cell"><label for='register_email'><?php eT("Email address"); ?></label></th>
        <td class="register-form-input input-cell" >
            <?php echo CHtml::textField('register_email', $sEmail,array('id'=>'register_email','class'=>'text')); ?>
        </td>
    </tr>
    <?php foreach($aExtraAttributes as $key=>$aExtraAttribute){ ?>
    <tr class="register-form-row register-form-attribute">
        <th class="register-form-label label-cell"><label for="register_<?php echo $key; ?>"><?php echo $aExtraAttribute['caption']; ?><?php echo $aExtraAttribute['mandatory'] == 'Y' ? '*' : ""; ?></label></th>
        <td class="register-form-input input-cell" >
            <?php echo CHtml::textField("register_{$key}", $aAttribute[$key],array('id'=>"register_{$key}",'class'=>'text')); ?>
        </td>
    </tr>
    <?php } ?>
    <?php if($bCaptcha){ ?>
    <tr class="register-form-row register-form-captcha">
        <th class="register-form-label label-cell"><label for='loadsecurity'><?php eT("Security Question"); ?></label></th>
        <td class="register-form-input input-cell" ><img src="<?php echo Yii::app()->getController()->createUrl('/verification/image/sid/'.$iSurveyId) ?>" alt='' class='captcha' />
            <?php echo CHtml::textField('loadsecurity', '',array('id'=>'loadsecurity','class'=>'text','size'=>'5','maxlength'=>'3')); ?>
        </td>
    </tr>
    <?php } ?>
    <tr><td></td><td><?php echo CHtml::submitButton(gT("Continue"),array('class'=>'button submit','id'=>'register','name'=>'register')); ?></td></tr>
    </tbody>
    </table>
<?php echo CHtml::endForm(); ?>
