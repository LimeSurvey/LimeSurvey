<?php
/**
 * form
 *
 */
?>
<div class='save-survey-form '>
    <div class='form-group save-survey-row save-survey-name'>
        <label class='control-label col-sm-3 save-survey-label' for='savename'><?php echo gT("Saved name:") ?></label>
        <div class='col-sm-7 save-survey-input input-cell'>
          <?php
              /* using CHtml::textField because it encode (and break XSS) */
              echo CHtml::textField('savename',Yii::app()->request->getPost('savename'),array(
                  'id'=>'savename',
                  'class'=>'form-control',
                  'required'=>true,
              ));
          ?>
        </div>
    </div>
    <div class='form-group save-survey-row save-survey-password'>
        <label class='control-label col-sm-3 save-survey-label label-cell' for='savepass'><?php echo gT("Password:") ?></label>
        <div class='col-sm-7 save-survey-input input-cell'>
          <?php
              /* Never rewrite a password in HTML */
              echo CHtml::passwordField('savepass','',array(
                  'id'=>'savepass',
                  'class'=>'form-control',
                  'required'=>true,
              ));
          ?>
        </div>
    </div>
    <div class='form-group save-survey-row save-survey-password'>
        <label class='control-label col-sm-3 save-survey-label label-cell' for='savepass2'><?php echo gT("Repeat password:") ?></label>
        <div class='col-sm-7 save-survey-input input-cell'>
          <?php
              echo CHtml::passwordField('savepass2','',array(
                  'id'=>'savepass2',
                  'class'=>'form-control',
                  'required'=>true,
              ));
          ?>
        </div>
    </div>
    <div class='form-group save-survey-row save-survey-password'>
        <label class='control-label col-sm-3 save-survey-label label-cell' for='saveemail'><?php echo gT("Your email address:") ?></label>
        <div class='col-sm-7 save-survey-input input-cell'>
          <?php
              echo CHtml::emailField('saveemail',Yii::app()->request->getPost('saveemail'),array(
                  'id'=>'saveemail',
                  'class'=>'form-control',
              ));
          ?>
        </div>
    </div>
    <?php if($captcha) : ?>
        <div class='form-group save-survey-row save-survey-captcha'>
            <label class='control-label col-sm-3 save-survey-label label-cell' for='loadsecurity'><?php echo gT("Security question:") ?></label>
            <div class='col-sm-7 save-survey-input input-cell'>
                <div class='input-group'>
                    <div class='input-group-addon captcha-image' >
                        <img src='<?php echo $captcha ?>' alt='' />
                    </div>
                    <input class='form-control' type='text' size='5' maxlength='3' id='loadsecurity' name='loadsecurity' value='' alt=''/>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class='form-group save-survey-row save-survey-submit'>
        <div class='col-sm-7 col-md-offset-3 save-survey-input input-cell'>
            <button type='submit' id='savebutton' name="savesubmit" class='btn btn-default' value='save'><?php echo gT("Save Now") ?></button>
        </div>
    </div>
</div>
