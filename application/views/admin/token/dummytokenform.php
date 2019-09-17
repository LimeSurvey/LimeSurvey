<?php
/**
 * Add dummy token
 */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Create dummy participants"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo CHtml::form(array("admin/tokens/sa/adddummies/surveyid/{$surveyid}/subaction/add"), 'post', array('id'=>'edittoken', 'name'=>'edittoken', 'class'=>'form30 ')); ?>

                <!-- ID  -->
                <div class="form-group">
                    <label  class=" control-label">ID:</label>
                    <div class="">
                        <p class="form-control-static"><?php eT("Auto"); ?></p>
                    </div>
                </div>

                <!-- Number of tokens  -->
                <div class="form-group">
                    <label  class=" control-label" for='amount'><?php eT("Number of participants:"); ?></label>
                    <div class="">
                        <input class='form-control' type='text' size='20' id='amount' name='amount' value="<?php echo $amount; ?>" />
                    </div>
                </div>

                <!-- Token length  -->
                <div class="form-group">
                    <label  class=" control-label" for='tokenlen'><?php eT("Token length"); ?>:</label>
                    <div class="">
                        <input class='form-control' type='text' size='20' id='tokenlen' name='tokenlen' value="<?php echo $tokenlength; ?>" />
                    </div>
                </div>

                <!-- First name  -->
                <div class="form-group">
                    <label  class=" control-label" for='firstname'><?php eT("First name"); ?>:</label>
                    <div class="">
                        <input class='form-control' type='text' size='30' id='firstname' name='firstname' value="<?php echo $firstname; ?>" />
                    </div>
                </div>

                <!-- Last name  -->
                <div class="form-group">
                    <label  class=" control-label" for='lastname'><?php eT("Last name"); ?>:</label>
                    <div class="">
                        <input class='form-control' type='text' size='30'  id='lastname' name='lastname' value="<?php echo $lastname; ?>" />
                    </div>
                </div>

                <!-- Email  -->
                <div class="form-group">
                    <label  class=" control-label" for='email'><?php eT("Email address:"); ?></label>
                    <div class="">
                        <input class='form-control' type='email' maxlength='320' size='50' id='email' name='email' value="<?php echo $email; ?>" />
                    </div>
                </div>

                <!-- Language  -->
                <div class="form-group">
                    <label  class=" control-label" for='language'><?php eT("Language"); ?>:</label>
                    <div class="">
                        <?php echo languageDropdownClean($surveyid, $language); ?>
                    </div>
                </div>

                <!-- Uses left  -->
                <div class="form-group">
                    <label  class=" control-label" for='usesleft'><?php eT("Uses left:"); ?></label>
                    <div class="">
                        <input class='form-control' type='text' size='20' id='usesleft' name='usesleft' value="<?php echo $usesleft; ?>" />
                    </div>
                </div>

            <?php
                if (isset($validfrom) && $validfrom != 'N')
                {
                    $validfrom = convertToGlobalSettingFormat($validfrom, true);
                }

                if (isset($validuntil) && $validuntil != 'N')
                {
                    $validuntil = convertToGlobalSettingFormat($validuntil, true);
                }
            ?>
                <!--  Validity -->
                <div class="form-group">
                    <label  class=" control-label" for='validfrom'><?php eT("Valid from"); ?>:</label>
                    <div class=" has-feedback">
                        <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                                'name' => "validfrom",
                                'value' => isset($validfrom) ? $validfrom : '',
                                'pluginOptions' => array(
                                    'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                    'allowInputToggle' =>true,
                                    'showClear' => true,
                                    'tooltips' => array(
                                        'clear'=> gT('Clear selection'),
                                        'prevMonth'=> gT('Previous month'),
                                        'nextMonth'=> gT('Next month'),
                                        'selectYear'=> gT('Select year'),
                                        'prevYear'=> gT('Previous year'),
                                        'nextYear'=> gT('Next year'),
                                        'selectDecade'=> gT('Select decade'),
                                        'prevDecade'=> gT('Previous decade'),
                                        'nextDecade'=> gT('Next decade'),
                                        'prevCentury'=> gT('Previous century'),
                                        'nextCentury'=> gT('Next century'),
                                        'selectTime'=> gT('Select time')
                                    ),
                                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                                )
                            ));
                        ?>
                        <span class="help-block"><?php printf(gT('Format: %s'), $dateformatdetails['jsdate'] . ' ' . gT('hh:mm')); ?></span>
                    </div>

                    <label  class=" control-label" for='validuntil'><?php eT('Until:'); ?></label>
                    <div class=" has-feedback">
                        <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                                'name' => "validuntil",
                                'value' => isset($validuntil) ? $validuntil : '',
                                'pluginOptions' => array(
                                    'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                    'allowInputToggle' =>true,
                                    'showClear' => true,
                                    'tooltips' => array(
                                        'clear'=> gT('Clear selection'),
                                        'prevMonth'=> gT('Previous month'),
                                        'nextMonth'=> gT('Next month'),
                                        'selectYear'=> gT('Select year'),
                                        'prevYear'=> gT('Previous year'),
                                        'nextYear'=> gT('Next year'),
                                        'selectDecade'=> gT('Select decade'),
                                        'prevDecade'=> gT('Previous decade'),
                                        'nextDecade'=> gT('Next decade'),
                                        'prevCentury'=> gT('Previous century'),
                                        'nextCentury'=> gT('Next century'),
                                'selectTime'=> gT('Select time')
                                    ),
                                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                                )
                            ));
                        ?>
                        <span class="help-block"><?php printf(gT('Format: %s'), $dateformatdetails['jsdate'] . ' ' . gT('hh:mm')); ?></span>
                    </div>
                </div>

                <!-- Attribute fields  -->
                <?php foreach ($aAttributeFields as $attr_name => $attr_description): ?>
                    <div class="form-group">
                        <label  class=" control-label" for='<?php echo $attr_name; ?>'><?php echo $attr_description['description'] . ($attr_description['mandatory'] == 'Y' ? '*' : '') ?>:</label>
                        <div class="">
                            <input class='form-control' type='text' size='55' id='<?php echo $attr_name; ?>' name='<?php echo $attr_name; ?>' value='<?php if (isset($$attr_name)){echo htmlspecialchars($$attr_name, ENT_QUOTES, 'UTF-8');}?>' />
                        </div>
                    </div>
                <?php endforeach; ?>

                <!--Hidden Buttons (default action) -->
                <input type='submit' class="hidden" value='1' />
            </form>
        </div>
    </div>
</div>
