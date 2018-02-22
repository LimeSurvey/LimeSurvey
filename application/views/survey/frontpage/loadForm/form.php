<?php
/**
 * form
 * @todo : set some var to core : input name for starting
 */
?>
<div class='load-survey-form '>
    <div class='form-group load-survey-row load-survey-name'>
        <label class='control-label col-sm-3 load-survey-label' for='loadname'><?php echo gT("Saved name:") ?></label>
        <div class='col-sm-7 load-survey-input input-cell'>
            <input class='form-control' type='text' id='loadname' name='loadname' value='' required>
        </div>
    </div>
    <div class='form-group load-survey-row load-survey-password'>
        <label class='control-label col-sm-3 load-survey-label label-cell' for='loadpass'><?php echo gT("Password:") ?></label>
        <div class='col-sm-7 load-survey-input input-cell'>
            <input class='form-control' type='password' id='loadpass' name='loadpass' value='' required>
        </div>
    </div>
    <?php if($captcha) : ?>
      <div class='form-group load-survey-row load-survey-captcha'>
          <label class='control-label col-sm-3 load-survey-label label-cell' for='loadsecurity'><?php echo gT("Security question:") ?></label>
          <div class='col-sm-7 load-survey-input input-cell'>
              <div class='input-group'>
                  <div class='input-group-addon captcha-image' >
                      <img src='<?php echo $captcha ?>' alt='' />
                  </div>
                  <input class='form-control' type='text' size='5' maxlength='3' id='loadsecurity' name='loadsecurity' value='' alt='' required>
              </div>
          </div>
      </div>
    <?php endif; ?>
    <div class='form-group load-survey-row load-survey-submit'>
        <div class='col-sm-7 col-md-offset-3 load-survey-input input-cell'>
            <button type='submit' id='loadbutton' name="loadall" class='btn btn-default' value='reload'><?php echo  gT("Load now") ?></button>
        </div>
    </div>
</div>
