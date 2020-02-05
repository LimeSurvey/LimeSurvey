<?php
/**
 * Personal settings edition
 *
 * @var $currentPreselectedQuestiontype string
 * @var $oQuestionSelector              PreviewModalWidget
 */

$aQuestionTypeGroups = array();

if (App()->session['questionselectormode'] !== 'default') {
    $selectormodeclass = App()->session['questionselectormode'];
} else {
    $selectormodeclass = App()->getConfig('defaultquestionselectormode');
}
uasort($aQuestionTypeList, "questionTitleSort");
foreach ($aQuestionTypeList as $questionType) {
    $htmlReadyGroup = str_replace(' ', '_', strtolower($questionType['group']));
    if (!isset($aQuestionTypeGroups[$htmlReadyGroup])) {
        $aQuestionTypeGroups[$htmlReadyGroup] = array(
            'questionGroupName' => $questionType['group']
        );
    }
    $imageName = $questionType['question_type'];
    if ($imageName == ":") {
        $imageName = "COLON";
    } else {
        if ($imageName == "|") {
            $imageName = "PIPE";
        } else {
            if ($imageName == "*") {
                $imageName = "EQUATION";
            }
        }
    }

    $questionType['type'] = $questionType['question_type'];
    $questionType['detailpage'] = '
        <div class="col-sm-12 currentImageContainer">
            <img src="' . $questionType['image_path'] . '" />
        </div>';
    if ($imageName == 'S') {
        $questionType['detailpage'] = '
            <div class="col-sm-12 currentImageContainer">
                <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '.png" />
                <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '2.png" />
            </div>';
    }
    $aQuestionTypeGroups[$htmlReadyGroup]['questionTypes'][] = $questionType;
}
$currentPreselectedQuestiontype = array_key_exists('preselectquestiontype', $aUserSettings) ? $aUserSettings['preselectquestiontype'] : Yii::app()->getConfig('preselectquestiontype');
$oQuestionSelector = $this->beginWidget('ext.admin.PreviewModalWidget.PreviewModalWidget', array(
    'widgetsJsName' => "preselectquestiontype",
    'renderType' => "group-simple",
    'modalTitle' => "Select question type",
    'groupTitleKey' => "questionGroupName",
    'groupItemsKey' => "questionTypes",
    'debugKeyCheck' => "Type: ",
    'previewWindowTitle' => gT("Preview question type"),
    'groupStructureArray' => $aQuestionTypeGroups,
    'value' => $currentPreselectedQuestiontype,
    'debug' => YII_DEBUG,
    'currentSelected' => $selectedQuestion['title'] ?? gT('Invalid question'),
    'buttonClasses' => ['btn-primary'],
    'optionArray' => [
        'selectedClass' => $selectedQuestion['settings']->class ?? 'invalid_question',
    ]
));

echo $oQuestionSelector->getModal();
?>

<div class="container">
<?php echo TbHtml::form($this->createUrl("/admin/user/sa/personalsettings"), 'post', array('class' => 'form44 ', 'id'=>'personalsettings','autocomplete'=>"off")); ?>
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
                                    <?php echo TbHtml::label(gT("User name:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo TbHtml::textField('username', $sUsername,array('class'=>'form-control','readonly'=>'readonly')); ?>
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
                                    <?php echo TbHtml::label(gT("Email:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo TbHtml::emailField('email', $sEmailAdress,array('class'=>'form-control','maxlength'=>254)); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <?php echo TbHtml::label(gT("Full name:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo TbHtml::textField('fullname', $sFullname ,array('class'=>'form-control','maxlength'=>50)); ?>
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
                                    <?php echo TbHtml::label(gT("Current password:"), 'lang', array('class'=>"control-label")); ?>
                                    <div class="">
                                        <?php echo TbHtml::passwordField('oldpassword', '',array('disabled'=>true, 'class'=>'form-control','autocomplete'=>"off",'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">                            
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo TbHtml::label(gT("New password:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo TbHtml::passwordField('password', '',array('disabled'=>true, 'class'=>'form-control','autocomplete'=>"off",'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
                                    </div>
                                    <div class="">
                                        <span class='text-info'><?php echo $passwordHelpText; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo TbHtml::label(gT("Repeat new password:"), 'lang', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php echo TbHtml::passwordField('repeatpassword', '',array('disabled'=>true, 'class'=>'form-control','autocomplete'=>"off",'placeholder'=>html_entity_decode(str_repeat("&#9679;",10),ENT_COMPAT,'utf-8'))); ?>
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
                                    <?php echo TbHtml::label(gT("Interface language:"), 'lang', array('class'=>" control-label")); ?>
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
                                    <?php echo TbHtml::label(gT("HTML editor mode:"), 'htmleditormode', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php
                                            echo TbHtml::dropDownList('htmleditormode',  App()->session['htmleditormode'], array(
                                                'default' => gT("Default",'unescaped'),
                                                'wysiwyg' => gT("Inline HTML editor",'unescaped'),
                                                'source' => gT("Sourcecode editor",'unescaped'),
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
                                    <?php echo TbHtml::label(gT("Question type selector:"), 'questionselectormode', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php
                                        echo TbHtml::dropDownList('questionselectormode', App()->session['questionselectormode'], array(
                                            'default' => gT("Default",'unescaped'),
                                            'full' => gT("Full selector",'unescaped'),
                                            'none' => gT("Simple selector",'unescaped')
                                        ), array('class'=>"form-control"));
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <!-- Question type preselect -->
                                <div class="form-group">
                                    <?php echo TbHtml::label(gT("Preselected question type:"), 'preselectquestiontype', array('class'=>" control-label")); ?>
                                    <?=$oQuestionSelector->getButtonOrSelect(true)?>
                                    <?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <!-- Template editor mode -->
                                <div class="form-group">
                                    <?php echo TbHtml::label(gT("Template editor mode:"), 'templateeditormode', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <?php
                                        echo TbHtml::dropDownList('templateeditormode', App()->session['templateeditormode'], array(
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
                                    <?php echo TbHtml::label( gT("Date format:"), 'dateformat', array('class'=>" control-label")); ?>
                                    <div class="">
                                        <select name='dateformat' id='dateformat' class="form-control">
                                            <?php
                                            foreach (getDateFormatData(0,App()->session['adminlang']) as $index => $dateformatdata)
                                            {
                                                echo "<option value='{$index}'";
                                                if ($index == App()->session['dateformat'])
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
                            <!-- Show script field in question editor -->
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <?php echo TbHtml::label( gT("Show script field:"), 'showScriptEdit', array('class'=>" control-label")); ?>
                                    <?php
                                        echo TbHtml::dropDownList('showScriptEdit', ($aUserSettings['showScriptEdit'] ?? '0'), array(
                                            '0' => gT("No",'unescaped'),
                                            '1' => gT("Yes",'unescaped'),
                                        ), array('class'=>"form-control"));
                                    ?>
                                </div>
                            </div>
                            <!-- Directly show edit mode -->
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <?php echo TbHtml::label( gT("Directly show edit mode:"), 'noViewMode', array('class'=>" control-label")); ?>
                                    <?php
                                        echo TbHtml::dropDownList('noViewMode', ($aUserSettings['noViewMode'] ?? '0'), array(
                                            '0' => gT("No",'unescaped'),
                                            '1' => gT("Yes",'unescaped'),
                                        ), array('class'=>"form-control"));
                                    ?>
                                </div>
                            </div>
                            <!-- Basic non numerical part of answer options -->
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <?php echo TbHtml::label( gT("Non-Numerical answer option prefix:"), 'answeroptionprefix', array('class'=>" control-label")); ?>
                                    <?php
                                        echo TbHtml::textField(
                                            'answeroptionprefix',
                                            ($aUserSettings['answeroptionprefix'] ?? 'AO'),
                                            array(
                                                'class'=>"form-control",
                                                'pattern' => "[A-Za-z]{0,3}"
                                            )
                                        );
                                    ?>
                                </div>
                            </div>
                            <!-- Basic non numerical part of subquestions -->
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                <?php echo TbHtml::label( gT("Non-Numerical subquestions prefix:"), 'subquestionprefix', array('class'=>" control-label")); ?>
                                    <?php
                                        echo TbHtml::textField(
                                            'subquestionprefix',
                                            ($aUserSettings['subquestionprefix'] ?? 'SQ'),
                                            array(
                                                'class'=>"form-control",
                                                'pattern' => "[A-Za-z]{0,3}"
                                            )
                                        );
                                    ?>
                                </div>
                            </div>
                            <!-- Lock questionorganizer in sidebar -->
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                <?php echo TbHtml::label( gT("Lock question organizer in sidebar by default:"), 'lock_organizer', array('class'=>" control-label")); ?>
                                    <?php
                                     echo TbHtml::dropDownList('lock_organizer', ($aUserSettings['lock_organizer'] ?? '0'), array(
                                         '0' => gT("No",'unescaped'),
                                         '1' => gT("Yes",'unescaped'),
                                     ), array('class'=>"form-control"));
                                 ?>
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
            <?php echo TbHtml::hiddenField('action', 'savepersonalsettings'); ?>
            <?php echo TbHtml::submitButton(gT("Save settings",'unescaped'),array('class' => 'hidden')); ?>
        </p>
    <?php echo TbHtml::endForm(); ?>

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
