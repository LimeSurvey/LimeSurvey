<?php
/**
 * form
 * @todo : set some var to core : input name for starting
 */
?>
<div class='load-survey-form '>
    <div class='mb-3 load-survey-row load-survey-name'>
        <label class='form-label col-md-3 load-survey-label' for='loadname'><?php echo gT("Saved name:") ?></label>
        <div class='col-md-7 load-survey-input input-cell'>
            <input class='form-control' type='text' id='loadname' name='loadname' value='' required>
        </div>
    </div>
    <div class='mb-3 load-survey-row load-survey-password'>
        <label class='form-label col-md-3 load-survey-label label-cell' for='loadpass'><?php echo gT("Password:") ?></label>
        <div class='col-md-7 load-survey-input input-cell'>
            <input class='form-control' type='password' id='loadpass' name='loadpass' value='' required>
        </div>
    </div>
    <?php if($captcha) : ?>
      <div class='mb-3 load-survey-row load-survey-captcha'>
          <label class='form-label col-md-3 load-survey-label label-cell' for='loadsecurity'><?php echo gT("Security question:") ?></label>
          <div class='col-md-7 load-survey-input input-cell'>
              <div class='input-group'>
                  <div class='input-group-addon captcha-image' >
                      <img src='<?php echo $captcha ?>' alt='' />
                  </div>
                  <input class='form-control' type='text' size='5' maxlength='3' id='loadsecurity' name='loadsecurity' value='' alt='' required>
              </div>
          </div>
      </div>
    <?php endif; ?>
    <div class='mb-3 load-survey-row load-survey-submit'>
        <div class='col-md-7 offset-lg-3 load-survey-input input-cell'>
            <button type='submit' id='loadbutton' name="loadall" class='btn btn-outline-secondary' value='reload'><?php echo  gT("Load now") ?></button>
        </div>
    </div>
</div>
