<?php

/**
 * Personal settings edition
 *
 * @var $currentPreselectedQuestiontype string
 * @var $oQuestionSelector              PreviewModalWidget
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('personalsettings');

$aQuestionTypeGroups = [];

if (App()->session['questionselectormode'] !== 'default') {
    $selectormodeclass = App()->session['questionselectormode'];
} else {
    $selectormodeclass = App()->getConfig('defaultquestionselectormode');
}
foreach ($aQuestionTypeList as $questionTheme) {
    $htmlReadyGroup = str_replace(' ', '_', strtolower((string) $questionTheme->group));
    if (!isset($aQuestionTypeGroups[$htmlReadyGroup])) {
        $aQuestionTypeGroups[$htmlReadyGroup] = [
            'questionGroupName' => $questionTheme->group
        ];
    }
    $imageName = $questionTheme->question_type;
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

    $questionTypeData = [];
    $questionTypeData['type'] = $questionTheme->question_type;
    $questionTypeData['name'] = $questionTheme->name;
    $questionTypeData['title'] = $questionTheme->title;
    $questionTypeData['detailpage'] = '
        <div class="col-12 currentImageContainer">
            <img src="' . $questionTheme->image_path . '" />
        </div>';
    if ($imageName == 'S') {
        $questionTypeData['detailpage'] = '
            <div class="col-12 currentImageContainer">
                <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '.png" />
                <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '2.png" />
            </div>';
    }
    $aQuestionTypeGroups[$htmlReadyGroup]['questionTypes'][] = $questionTypeData;
}

$oQuestionSelector = $this->beginWidget(
    'ext.admin.PreviewModalWidget.PreviewModalWidget',
    [
        'widgetsJsName'       => "preselectquestiontype",
        'renderType'          => "group-simple",
        'modalTitle'          => "Select question type",
        'groupTitleKey'       => "questionGroupName",
        'groupItemsKey'       => "questionTypes",
        'debugKeyCheck'       => "Type: ",
        'previewWindowTitle'  => gT("Preview question type"),
        'groupStructureArray' => $aQuestionTypeGroups,
        'value'               => $currentPreselectedQuestiontype,
        'theme'               => $currentPreselectedQuestionTheme,
        'debug'               => YII_DEBUG,
        'currentSelected'     => $selectedQuestion['title'] ?? gT('Invalid question'),
        'buttonClasses'       => ['btn-primary'],
        'optionArray'         => [
            'selectedClass' => $selectedQuestion['settings']->class ?? 'invalid_question',
            'onUpdate'      => [
                'value',
                'theme',
                "$('#preselectquestiontheme').val(theme);"
            ],
        ]
    ]
);

echo $oQuestionSelector->getModal();
?>

<div class="container">
    <?php echo TbHtml::form($this->createUrl("/admin/user/sa/personalsettings"), 'post', ['class' => 'form44 ', 'id' => 'personalsettings', 'autocomplete' => "off"]); ?>
    <?php echo TbHtml::hiddenField('action', 'savepersonalsettings'); ?>
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="nav-item"><a class="nav-link active" href="#your-profile" role="tab" data-bs-toggle="tab"><?php eT("Profile"); ?></a></li>
                <li role="presentation" class="nav-item"><a class="nav-link" href="#your-personal-settings" role="tab" data-bs-toggle="tab"><?php eT("Personal settings"); ?></a></li>
                <li role="presentation" class="nav-item"><a class="nav-link" href="#your-personal-menues" role="tab" data-bs-toggle="tab"><?php eT("Personalized menus"); ?></a></li>
                <li role="presentation" class="nav-item"><a class="nav-link" href="#your-personal-menueentries" role="tab" data-bs-toggle="tab"><?php eT("Personalized menu entries"); ?></a></li>
            </ul>
            <div class="tab-content">

                <!-- TAB: My profile settings -->
                <div role="tabpanel" class="tab-pane fade show active" id="your-profile">
                    <div class="pagetitle h3"><?php eT("Profile"); ?></div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("User name:"), 'lang', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::textField('username', $sUsername, ['class' => 'form-control', 'readonly' => 'readonly']); ?>
                                </div>
                                <div class="">
                                    <span class='text-info'><?php eT("The user name cannot be changed."); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <hr />
                    </div>
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Full name:"), 'lang', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::textField('fullname', $sFullname, ['class' => 'form-control', 'maxlength' => 50]); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Email address:"), 'lang', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::emailField('email', $sEmailAdress, ['readonly' => true, 'class' => 'form-control', 'maxlength' => 254]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <hr />
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <button type="button" class="btn btn-warning " id="selector__showChangePassword">
                                <i class="ri-lock-fill"></i>
                                <?= gT("Change password") ?>
                            </button>
                            <button type="button" class="btn btn-warning " id="selector__showChangeEmail">
                                <i class="ri-lock-fill"></i>
                                <?= gT("Change email address") ?>
                            </button>
                            <br />
                        </div>
                    </div>
                    <div class="row selector__oldpassword-row d-none">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="oldpassword" class="form-label">
                                    <?php echo gT("Current password:"); ?>
                                    <span class="required">*</span>
                                </label>
                                <div class="">
                                    <?php echo TbHtml::passwordField('oldpassword', '', ['disabled' => true, 'class' => 'form-control', 'autocomplete' => "off", 'placeholder' => html_entity_decode(str_repeat("&#9679;", 10), ENT_COMPAT, 'utf-8')]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row selector__password-row d-none">
                        <input type="hidden" id="newpasswordshown" name="newpasswordshown" value="0" />
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("New password:"), 'lang', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::passwordField('password', '', ['disabled' => true, 'class' => 'form-control', 'autocomplete' => "off", 'placeholder' => html_entity_decode(str_repeat("&#9679;", 10), ENT_COMPAT, 'utf-8')]); ?>
                                </div>
                                <div class="">
                                    <span class='text-info'><?php echo $passwordHelpText; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Repeat new password:"), 'lang', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::passwordField('repeatpassword', '', ['disabled' => true, 'class' => 'form-control', 'autocomplete' => "off", 'placeholder' => html_entity_decode(str_repeat("&#9679;", 10), ENT_COMPAT, 'utf-8')]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row selector__email-row d-none">
                        <input type="hidden" id="newemailshown" name="newemailshown" value="0" />
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("New email address:"), 'lang', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::emailField('newemail', $sEmailAdress, ['class' => 'form-control', 'maxlength' => 254]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: My personal settings -->
                <div role="tabpanel" class="tab-pane fade" id="your-personal-settings">
                    <div class="pagetitle h3"><?php eT("My personal settings"); ?></div>
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <!-- Interface language -->
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Interface language:"), 'lang', ['class' => " form-label"]); ?>
                                <div class="selector_contain_select2">
                                    <?php $this->widget(
                                        'yiiwheels.widgets.select2.WhSelect2',
                                        [
                                            'asDropDownList' => true,
                                            'name'           => 'lang',
                                            'data'           => $aLanguageData,
                                            'pluginOptions'  => [
                                                'buttonWidth' => '100%',
                                                'htmlOptions' => [
                                                    'id'    => 'lang',
                                                    'style' => "width:100%;"
                                                ]
                                            ],
                                            'value'          => $sSavedLanguage,
                                            'htmlOptions'    => [
                                                'class'      => "form-control",
                                                'style'      => "width:100%;",
                                                'data-width' => '100%'
                                            ]
                                        ]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <!-- HTML editor mode -->
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("HTML editor mode:"), 'htmleditormode', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::dropDownList(
                                        'htmleditormode',
                                        App()->session['htmleditormode'],
                                        [
                                            'default' => gT("Default", 'unescaped'),
                                            'inline'  => gT("Inline HTML editor", 'unescaped'),
                                            'popup'   => gT("Popup HTML editor", 'unescaped'),
                                            'none'    => gT("Sourcecode editor", 'unescaped'),
                                        ],
                                        ['class' => "form-select"]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <!-- Question type selector -->
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Question type selector:"), 'questionselectormode', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::dropDownList(
                                        'questionselectormode',
                                        App()->session['questionselectormode'],
                                        [
                                            'default' => gT("Default", 'unescaped'),
                                            'full'    => gT("Full selector", 'unescaped'),
                                            'none'    => gT("Simple selector", 'unescaped')
                                        ],
                                        ['class' => "form-select"]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <!-- Question type preselect -->
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Preselected question type:"), 'preselectquestiontype', ['class' => " form-label"]); ?>
                                <?= $oQuestionSelector->getButtonOrSelect(true) ?>
                                <?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
                                <?php echo TbHtml::hiddenField('preselectquestiontheme', $currentPreselectedQuestionTheme); ?>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <!-- Template editor mode -->
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Template editor mode:"), 'templateeditormode', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::dropDownList(
                                        'templateeditormode',
                                        App()->session['templateeditormode'],
                                        [
                                            'default' => gT("Default"),
                                            'full'    => gT("Full template editor"),
                                            'none'    => gT("Simple template editor")
                                        ],
                                        ['class' => "form-select"]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <!-- Date format -->
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Date format:"), 'dateformat', ['class' => " form-label"]); ?>
                                <div class="">
                                    <select name='dateformat' id='dateformat' class="form-select">
                                        <?php foreach (getDateFormatData(0, App()->session['adminlang']) as $index => $dateformatdata) {
                                            echo "<option value='{$index}'";
                                            if ($index == App()->session['dateformat']) {
                                                echo " selected='selected'";
                                            }

                                            echo ">" . $dateformatdata['dateformat'] . '</option>';
                                        } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Basic non numerical part of answer options -->
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Non-Numerical answer option prefix:"), 'answeroptionprefix', ['class' => " form-label"]); ?>
                                <?php echo TbHtml::textField(
                                    'answeroptionprefix',
                                    ($aUserSettings['answeroptionprefix'] ?? 'AO'),
                                    [
                                        'class'   => "form-control",
                                        'pattern' => "[A-Za-z]{0,3}"
                                    ]
                                ); ?>
                            </div>
                        </div>
                        <!-- Basic non numerical part of subquestions -->
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Non-Numerical subquestions prefix:"), 'subquestionprefix', ['class' => " form-label"]); ?>
                                <?php echo TbHtml::textField(
                                    'subquestionprefix',
                                    ($aUserSettings['subquestionprefix'] ?? 'SQ'),
                                    [
                                        'class'   => "form-control",
                                        'pattern' => "[A-Za-z]{0,3}"
                                    ]
                                );
                                ?>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <!-- Breadcrumb mode -->
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Breadcrumb mode:"), 'breadcrumbMode', ['class' => " form-label"]); ?>
                                <div class="">
                                    <?php echo TbHtml::dropDownList(
                                        'breadcrumbMode',
                                        ($aUserSettings['breadcrumbMode'] ?? 'default'),
                                        [
                                            'default' => gT("Default"),
                                            'long'    => gT("Long"),
                                            'short'   => gT("Short"),
                                        ],
                                        ['class' => "form-select"]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                        <!-- Show script field in question editor -->
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Show script field:"), 'showScriptEdit', ['class' => " form-label"]); ?>
                                <div>
                                    <?php
                                    $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => 'showScriptEdit',
                                        'checkedOption' => $aUserSettings['showScriptEdit'] ?? 0,
                                        'selectOptions' =>    [
                                            '1' => gT("Yes", 'unescaped'),
                                            '0' => gT("No", 'unescaped'),
                                        ],
                                        'htmlOptions' => []
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                        <!-- Directly show edit mode -->
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Directly show edit mode:"), 'noViewMode', ['class' => " form-label"]); ?>
                                <div>
                                    <?php
                                    $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => 'noViewMode',
                                        'checkedOption' => $aUserSettings['noViewMode'] ?? 0,
                                        'selectOptions' =>    [
                                            '1' => gT("Yes", 'unescaped'),
                                            '0' => gT("No", 'unescaped'),
                                        ],
                                        'htmlOptions' => []
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Lock questionorganizer in sidebar -->
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Lock question organizer in sidebar by default:"), 'lock_organizer', ['class' => " form-label"]); ?>
                                <div>
                                    <?php
                                    $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => 'lock_organizer',
                                        'checkedOption' => $aUserSettings['lock_organizer'] ?? 0,
                                        'selectOptions' =>    [
                                            '1' => gT("Yes", 'unescaped'),
                                            '0' => gT("No", 'unescaped'),
                                        ],
                                        'htmlOptions' => []
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                        <!-- Create example question group and question -->
                        <div class="col-12 col-lg-6">
                            <div class="mb-3">
                                <?php echo TbHtml::label(gT("Create example question group and question:"), 'createsample', ['class' => " form-label"]); ?>
                                <div>
                                    <?php
                                    $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => 'createsample',
                                        'checkedOption' => $aUserSettings['createsample'] ?? 'default',
                                        'selectOptions' =>    [
                                            '1' => gT("Yes", 'unescaped'),
                                            '0' => gT("No", 'unescaped'),
                                            'default' => gT("Default", 'unescaped'),
                                        ],
                                        'htmlOptions' => []
                                    ]); ?>
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
    <?php echo TbHtml::submitButton(gT("Save settings", 'unescaped'), ['class' => 'd-none']); ?>
    <?php echo TbHtml::endForm(); ?>

</div>

<?php
Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'personalsettings.js');
