<?php
/**
 * Personal settings edition
 */
?>

<div class="container">
<?php echo CHtml::form($this->createUrl("/admin/user/sa/personalsettings"), 'post', array('class' => 'form44 ', 'id'=>'personalsettings','autocomplete'=>"off")); ?>
    <div class="row">
        <div class="col-xs-12">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#your-profile" role="tab" data-toggle="tab"><?php eT("My profile"); ?></a></li>
                <li role="presentation"><a href="#your-personal-settings" role="tab" data-toggle="tab"><?php eT("My personal settings"); ?></a></li>
                <li role="presentation" ><a href="#your-personal-menues" role="tab" data-toggle="tab"><?php eT("My personal menus"); ?></a></li>
                <li role="presentation" ><a href="#your-personal-menueentries" role="tab" data-toggle="tab"><?php eT("My personal menu entries"); ?></a></li>
            </ul>
            <div class="tab-content">

                <!-- TAB: My profile settings -->
                <div role="tabpanel" class="tab-pane fade in active" id="your-profile">
                    <div class="pagetitle h3"><?php eT("My profile"); ?></div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("User name:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo CHtml::textField('username', $sUsername,array('class'=>'form-control','readonly'=>'readonly')); ?>
                                    </div>
                                    <div class="">
                                        <span class='text-info'><?php eT("The user name cannot be changed."); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <hr/>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("Email:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo CHtml::emailField('email', $sEmailAdress,array('class'=>'form-control','maxlength'=>254)); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("Full name:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo CHtml::textField('fullname', $sFullname ,array('class'=>'form-control','maxlength'=>50)); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <hr/>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <button class="btn btn-default btn-warning" id="selector__showChangePassword" style="color: white; outline: none;">
                                    <i class="fa fa-lock"></i>
                                    <?=gT("Change password")?>
                                </button>
                                
                                <br/>
                            </div>
                        </div>
                        <div class="row selector__password-row hidden">
                            <input type="hidden" id="newpasswordshown" name="newpasswordshown" value="0" />
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("Current password:"), 'lang', array('class'=>"control-label")); ?>
                                    <div class="">
                                        <?php echo CHtml::passwordField('oldpassword', '',array('disabled'=>true, 'class'=>'form-control','autocomplete'=>"off",'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">                            
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("New password:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo CHtml::passwordField('password', '',array('disabled'=>true, 'class'=>'form-control','autocomplete'=>"off",'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("Repeat new password:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo CHtml::passwordField('repeatpassword', '',array('disabled'=>true, 'class'=>'form-control','autocomplete'=>"off",'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: My personal settings -->
                <div role="tabpanel" class="tab-pane fade" id="your-personal-settings">
                    <div class="pagetitle h3"><?php eT("My personal settings"); ?></div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <!-- Interface language -->
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("Interface language:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="selector_contain_select2">
                                        <?php
                                        $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                                            'asDropDownList' => true,
                                            'name' => 'lang',
                                            'data' => $aLanguageData,
                                            'pluginOptions' => array(
                                                'buttonWidth' => '100%',
                                                'htmlOptions' => array(
                                                    'id' => 'lang',
                                                    'style'=> "widht:100%;"
                                                )
                                            ),
                                            'value' => $sSavedLanguage,
                                            'htmlOptions' => array(
                                                'class'=> "form-control",
                                                'style'=> "widht:100%;",
                                                'data-width' => '100%'
                                            )
                                        ));

                                            ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <!-- HTML editor mode -->
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("HTML editor mode:"), 'htmleditormode', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php
                                            echo CHtml::dropDownList('htmleditormode', Yii::app()->session['htmleditormode'], array(
                                                'default' => gT("Default",'unescaped'),
                                                'inline' => gT("Inline HTML editor",'unescaped'),
                                                'popup' => gT("Popup HTML editor",'unescaped'),
                                                'none' => gT("No HTML editor",'unescaped')
                                            ), array('class'=>"form-control"));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <!-- Question type selector -->
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("Question type selector:"), 'questionselectormode', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php
                                        echo CHtml::dropDownList('questionselectormode', Yii::app()->session['questionselectormode'], array(
                                            'default' => gT("Default",'unescaped'),
                                            'full' => gT("Full selector",'unescaped'),
                                            'none' => gT("Simple selector",'unescaped')
                                        ), array('class'=>"form-control"));
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <!-- Template editor mode -->
                                <div class="form-group">
                                    <?php echo CHtml::label(gT("Template editor mode:"), 'templateeditormode', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php
                                        echo CHtml::dropDownList('templateeditormode', Yii::app()->session['templateeditormode'], array(
                                            'default' => gT("Default"),
                                            'full' => gT("Full template editor"),
                                            'none' => gT("Simple template editor")
                                        ), array('class'=>"form-control"));
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <!-- Date format -->
                                <div class="form-group">
                                    <?php echo CHtml::label( gT("Date format:"), 'dateformat', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <select name='dateformat' id='dateformat' class="form-control">
                                            <?php
                                            foreach (getDateFormatData(0,Yii::app()->session['adminlang']) as $index => $dateformatdata)
                                            {
                                                echo "<option value='{$index}'";
                                                if ($index == Yii::app()->session['dateformat'])
                                                {
                                                    echo " selected='selected'";
                                                }

                                                echo ">" . $dateformatdata['dateformat'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="your-personal-menues">
                    <?php $this->renderPartial('/admin/surveymenu/shortlist', $surveymenu_data); ?>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="your-personal-menueentries">
                    <?php $this->renderPartial('/admin/surveymenu_entries/shortlist', $surveymenuentry_data); ?>
                </div>
            </div>
        </div>
    </div>

        <!-- Buttons -->
        <p>
            <?php echo CHtml::hiddenField('action', 'savepersonalsettings'); ?>
            <?php echo CHtml::submitButton(gT("Save settings",'unescaped'),array('class' => 'hidden')); ?>
        </p>
    <?php echo CHtml::endForm(); ?>

</div>

<?php App()->getClientScript()->registerScript("personalSettings", "
$('#selector__showChangePassword').on('click', function(e){
    e.preventDefault();
    $('#newpasswordshown').val('1');
    $('.selector__password-row').removeClass('hidden').find('input').each(
        function(i,item){
            $(item).prop('disabled', false);
        }
    );
    $(this).closest('div').remove();
});
", LSYii_ClientScript::POS_POSTSCRIPT);
