<?php
/**
 * For new Captcha system
 */
$labelWidth=$labelWidth ?? 5;
$inputgroupWidth=$inputgroupWidth ?? 7;

?>
<div class='mb-3 captcha-item'>
    <label class='form-label col-md-5' for='loadsecurity'><?php echo gT("Please solve the following equation:") ?></label>
    <div class='col-md-7 load-survey-input input-cell'>
        <div class='input-group'>
            <div class='input-group-text captcha-widget' >
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
