<?php
/**
 * Personal settings edition
 */
?>

<div class="container-fluid welcome full-page-wrapper">
    <?php echo CHtml::form($this->createUrl("/admin/user/sa/personalsettings"), 'post', array('class' => 'form44 form-horizontal', 'id'=>'personalsettings','autocomplete'=>"off")); ?>
    <h3 class="pagetitle"><?php eT("Your profile"); ?></h3>
        <div class="form-group">
            <?php echo CHtml::label(gT("User name:"), 'lang', array('class'=>"col-sm-2 control-label")); ?>
             <div class="col-sm-3">
                <?php echo CHtml::textField('username', $sUsername,array('class'=>'form-control','readonly'=>'readonly')); ?>
             </div>
             <div class="col-sm-3">
                <span class='text-info'><?php eT("The user name cannot be changed."); ?></span>
             </div>
        </div>
        <div class="form-group">
            <?php echo CHtml::label(gT("Email:"), 'lang', array('class'=>"col-sm-2 control-label")); ?>
             <div class="col-sm-3">
                <?php echo CHtml::emailField('email', $sEmailAdress,array('class'=>'form-control','maxlength'=>254)); ?>
             </div>
        </div>
        <div class="form-group">
            <?php echo CHtml::label(gT("Full name:"), 'lang', array('class'=>"col-sm-2 control-label")); ?>
             <div class="col-sm-3">
                <?php echo CHtml::textField('fullname', $sFullname ,array('class'=>'form-control','maxlength'=>50)); ?>
             </div>
        </div>
        <div class="form-group">
            <?php echo CHtml::label(gT("Password:"), 'lang', array('class'=>"col-sm-2 control-label")); ?>
             <div class="col-sm-3">
                <?php echo CHtml::passwordField('password', '',array('class'=>'form-control','autocomplete'=>"off",'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
             </div>
        </div>
        <div class="form-group">
            <?php echo CHtml::label(gT("Repeat password:"), 'lang', array('class'=>"col-sm-2 control-label")); ?>
             <div class="col-sm-3">
                <?php echo CHtml::passwordField('repeatpassword', '',array('class'=>'form-control','autocomplete'=>"off",'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
             </div>
        </div>
    <!-- form -->
    <h3 class="pagetitle"><?php eT("Your personal settings"); ?></h3>

    <!-- form -->

        <!-- Interface language -->
        <div class="form-group">
            <?php echo CHtml::label(gT("Interface language:"), 'lang', array('class'=>"col-sm-2 control-label")); ?>
            <div class="col-sm-3">
                <?php
                $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                    'asDropDownList' => true,
                    'name' => 'lang',
                    'data' => $aLanguageData,
                    'pluginOptions' => array(
                        'htmlOptions' => array(
                            'id' => 'lang',
                           'class'=> "form-control"
                        )
                    ),
                    'value' => $sSavedLanguage
                ));

                ?>
            </div>
        </div>

        <!-- HTML editor mode -->
        <div class="form-group">
            <?php echo CHtml::label(gT("HTML editor mode:"), 'htmleditormode', array('class'=>"col-sm-2 control-label")); ?>
            <div class="col-sm-3">
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

        <!-- Question type selector -->
        <div class="form-group">
            <?php echo CHtml::label(gT("Question type selector:"), 'questionselectormode', array('class'=>"col-sm-2 control-label")); ?>
            <div class="col-sm-3">
                <?php
                echo CHtml::dropDownList('questionselectormode', Yii::app()->session['questionselectormode'], array(
                    'default' => gT("Default",'unescaped'),
                    'full' => gT("Full selector",'unescaped'),
                    'none' => gT("Simple selector",'unescaped')
                ), array('class'=>"form-control"));
                ?>
            </div>
        </div>

        <!-- Template editor mode -->
        <div class="form-group">
            <?php echo CHtml::label(gT("Template editor mode:"), 'templateeditormode', array('class'=>"col-sm-2 control-label")); ?>
            <div class="col-sm-3">
                <?php
                echo CHtml::dropDownList('templateeditormode', Yii::app()->session['templateeditormode'], array(
                    'default' => gT("Default"),
                    'full' => gT("Full template editor"),
                    'none' => gT("Simple template editor")
                ), array('class'=>"form-control"));
                ?>
            </div>
        </div>

        <!-- Date format -->
        <div class="form-group">
            <?php echo CHtml::label( gT("Date format:"), 'dateformat', array('class'=>"col-sm-2 control-label")); ?>
             <div class="col-sm-3">
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

        <!-- Buttons -->
        <p>
            <?php echo CHtml::hiddenField('action', 'savepersonalsettings'); ?>
            <?php echo CHtml::submitButton(gT("Save settings",'unescaped'),array('class' => 'hidden')); ?>
        </p>
    <?php echo CHtml::endForm(); ?>
</div>
