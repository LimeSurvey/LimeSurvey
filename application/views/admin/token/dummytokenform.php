<?php
/**
 * Add dummy token
 */
?>

<div class='side-body'>
    <h3><?php eT("Create dummy participants"); ?></h3>

    <div class="row">
        <div class="col-12 content-right">
            <?php echo CHtml::form(array("admin/tokens/sa/adddummies/surveyid/{$surveyid}/subaction/add"), 'post', array('id'=>'edittoken', 'name'=>'edittoken', 'class'=>'form30 ')); ?>
            <div class="row">
                <!-- ID  -->
                <div class="mb-3 col-12">
                    <label  class=" form-label">ID:</label>
                    <div class="">
                        <p class="form-control-static"><?php eT("Auto"); ?></p>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Number of tokens  -->
                <div class="mb-3 col-6">
                    <label  class=" form-label" for='amount'><?php eT("Number of participants:"); ?></label>
                    <div class="">
                        <input class='form-control' type='number' min='1' size='20' id='amount' name='amount' value="<?php echo $amount; ?>" />
                    </div>
                </div>

                <!-- Token length  -->
                <div class="mb-3 col-6">
                    <label  class=" form-label" for='tokenlen'><?php eT("Access code length"); ?>:</label>
                    <div class="">
                        <input class='form-control' type='text' size='20' id='tokenlen' name='tokenlen' value="<?php echo $tokenlength; ?>" />
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- First name  -->
                <div class="mb-3 col-6">
                    <label  class=" form-label" for='firstname'><?php eT("First name"); ?>:</label>
                    <div class="">
                        <input class='form-control' type='text' size='30' id='firstname' name='firstname' value="<?php echo $firstname; ?>" />
                    </div>
                </div>

                <!-- Last name  -->
                <div class="mb-3 col-6">
                    <label  class=" form-label" for='lastname'><?php eT("Last name"); ?>:</label>
                    <div class="">
                        <input class='form-control' type='text' size='30'  id='lastname' name='lastname' value="<?php echo $lastname; ?>" />
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Email  -->
                <div class="mb-3 col-6">
                    <label  class=" form-label" for='email'><?php eT("Email address:"); ?></label>
                    <div class="">
                        <input class='form-control' type='email' maxlength='320' size='50' id='email' name='email' value="<?php echo $email; ?>" />
                    </div>
                </div>

                <!-- Language  -->
                <div class="mb-3 col-6">
                    <label  class=" form-label" for='language'><?php eT("Language"); ?>:</label>
                    <div class="">
                        <?php echo languageDropdownClean($surveyid, $language); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Uses left  -->
                <div class="mb-3 col-6">
                    <label  class=" form-label" for='usesleft'><?php eT("Uses left:"); ?></label>
                    <div class="">
                        <input class='form-control' type='text' size='20' id='usesleft' name='usesleft' value="<?php echo $usesleft; ?>" />
                    </div>
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
            <div class="row">
                <!--  Validity -->
                <div class="mb-3 col-6">
                    <label  class=" form-label" for='validfrom'><?php eT("Valid from"); ?>:</label>
                    <div class=" has-feedback">
                        <?php Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', [
                                'name' => "validfrom",
                                'value' => $validfrom ?? '',
                                'pluginOptions' => [
                                    'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                    'allowInputToggle' => true,
                                    'showClear' => true,
                                    'theme' => 'light',
                                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                                ]
                            ]);
                        ?>
                        <?php
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'text'        => sprintf(gT('Format: %s'), $dateformatdetails['jsdate'] . ' ' . gT('hh:mm')),
                            'type'        => 'info',
                            'htmlOptions' => ['class' => 'mt-1'],
                        ]);
                        ?>
                    </div>
                </div>
                <div class="mb-3 col-6">
                    <label  class=" form-label" for='validuntil'><?php eT('Until:'); ?></label>
                    <div class=" has-feedback">
                        <?php Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                                'name' => "validuntil",
                                'value' => $validuntil ?? '',
                                'pluginOptions' => array(
                                    'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                    'allowInputToggle' => true,
                                    'showClear' => true,
                                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                                )
                            ));
                        ?>
                        <?php
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'text'        => sprintf(gT('Format: %s'), $dateformatdetails['jsdate'] . ' ' . gT('hh:mm')),
                            'type'        => 'info',
                            'htmlOptions' => ['class' => 'mt-1'],
                        ]);
                        ?>
                    </div>
                </div>
            </div>
                <!-- Attribute fields  -->
                <?php foreach ($aAttributeFields as $attrName => $attrDescription): ?>
                    <?php
                    $this->renderPartial(
                            '/admin/token/attribute_subviews/tokenformAttributesWrapper',
                            [
                                    'attrDescription' => $attrDescription,
                                    'attrName' => $attrName,
                                    'inputValue' => null,
                                    'jsDate' => $dateformatdetails['jsdate'],
                                    'addClass' => 'col-6',
                            ]
                    );
                    ?>
                <?php endforeach; ?>

                <!--Hidden Buttons (default action) -->
                <input type='submit' class="d-none" value='1' />
            </form>
        </div>
    </div>
</div>
