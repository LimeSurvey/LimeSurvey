<?php

/**
 * Edit the survey text elements of a survey for one given language
 * It is rendered from editLocalSettings_main_view.
 *
 * @var AdminController $this
 * @var Survey $oSurvey
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyTexts');

?>

<?php App()->getClientScript()->registerScript(
    "editLocalSettings-view-variables",
    "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '" . gT("If you are using token functions or notifications emails you need to set an administrator email address.", 'js') . "'
    var sURLParameters = '';
    var sAddParam = '';
",
    LSYii_ClientScript::POS_BEGIN
); ?>

<div id="edittxtele-<?= $i ?>" class="tab-pane fade <?= $i == 0 ? "show active" : "" ?> center-box">
    <div class="row mb-3">
        <div class="col-lg-6">
            <!-- Survey title -->
            <label class=" question-group-title form-label" for="short_title_<?=$aSurveyLanguageSettings['surveyls_language']; ?>">
                <?php eT("Survey title:"); ?>
            </label>
            <?php echo CHtml::textField(
                "short_title_{$aSurveyLanguageSettings['surveyls_language']}",
                $aSurveyLanguageSettings['surveyls_title'],
                array(
                    'class' => 'form-control', 'size' => "80", 'maxlength' => 200,
                    'id' => "short_title_{$aSurveyLanguageSettings['surveyls_language']}"
                )
            ); ?>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-4">
            <!-- Date format -->
            <label class="form-label " for="dateformat_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>">
                <?php eT("Date format:"); ?></label>
            <select size='1' id='dateformat_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>' 
                    name='dateformat_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>' class="form-select">
                <?php foreach (getDateFormatData(0, Yii::app()->session['adminlang']) as $index => $dateformatdata) : ?>
                    <option value='<?php echo $index; ?>' 
                      <?php if ($aSurveyLanguageSettings['surveyls_dateformat'] == $index) : ?> selected='selected' <?php endif; ?>>
                        <?php echo $dateformatdata['dateformat']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2">
            <!-- Decimal mark -->
            <label class="form-label "><?php eT("Decimal mark:"); ?></label>
            <?php $aRadixPoint = [];
            foreach (getRadixPointData() as $index => $radixptdata) {
                $aRadixPoint[$index] = html_entity_decode((string) $radixptdata['desc']);
            }
            $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'numberformat_' . $aSurveyLanguageSettings['surveyls_language'],
                'checkedOption' => $aSurveyLanguageSettings['surveyls_numberformat'],
                'selectOptions' => $aRadixPoint,
                'ariaLabel'     => gT("Decimal mark:"),
                'htmlOptions'   => [
                    "style" => "z-index:0"
                ]
            ]); ?>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-6">
            <!-- Survey alias -->
            <label class=" control-label" for="alias_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>">
                <?php eT("Survey alias:"); ?>
            </label>
            <?php echo CHtml::textField(
                "alias_{$aSurveyLanguageSettings['surveyls_language']}",
                $aSurveyLanguageSettings['surveyls_alias'],
                [
                    'class' => 'form-control',
                    'size' => "80",
                    'maxlength' => 100,
                    'id' => "alias_{$aSurveyLanguageSettings['surveyls_language']}",
                    //'pattern' => '[\w\d-]+'
                ]
            ); ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12 col-lg-6">
            <!-- Description -->
            <label class=" form-label" for="description_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>"><?php eT("Description:"); ?></label>
            <div class="htmleditor input-group">
                <?php echo CHtml::textArea(
                    "description_{$aSurveyLanguageSettings['surveyls_language']}",
                    $aSurveyLanguageSettings['surveyls_description'],
                    array('class' => 'form-control', 'cols' => '80', 'rows' => '15', 'id' => "description_{$aSurveyLanguageSettings['surveyls_language']}")
                ); ?>
                <?php echo getEditor(
                    "survey-desc",
                    "description_" . $aSurveyLanguageSettings['surveyls_language'],
                    "[" . gT("Description:", "js") . "](" . $aSurveyLanguageSettings['surveyls_language'] . ")",
                    $surveyid,
                    '',
                    '',
                    $action
                ); ?>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <!-- Welcome message -->
            <label class=" form-label" for='welcome_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'>
                <?php eT("Welcome message:"); ?></label>
            <?php echo CHtml::textArea(
                "welcome_{$aSurveyLanguageSettings['surveyls_language']}",
                $aSurveyLanguageSettings['surveyls_welcometext'],
                array('class' => 'form-control', 'cols' => '80', 'rows' => '15', 'id' => "welcome_{$aSurveyLanguageSettings['surveyls_language']}")
            ); ?>
            <?php echo getEditor(
                "survey-welc",
                "welcome_" . $aSurveyLanguageSettings['surveyls_language'],
                "[" . gT("Welcome:", "js") . "](" . $aSurveyLanguageSettings['surveyls_language'] . ")",
                $surveyid,
                '',
                '',
                $action
            ); ?>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12 col-lg-6">
            <!-- End message -->
            <label class=" form-label" for='endtext_<?php echo $aSurveyLanguageSettings['surveyls_language']; ?>'>
            <?php eT("End message:"); ?></label>
            <?php echo CHtml::textArea(
                "endtext_{$aSurveyLanguageSettings['surveyls_language']}",
                $aSurveyLanguageSettings['surveyls_endtext'],
                array('class' => 'form-control', 'cols' => '80', 'rows' => '15', 'id' => "endtext_{$aSurveyLanguageSettings['surveyls_language']}")
            ); ?>
            <?php echo getEditor(
                "survey-endtext",
                "endtext_" . $aSurveyLanguageSettings['surveyls_language'],
                "[" . gT("End message:", "js") . "](" . $aSurveyLanguageSettings['surveyls_language'] . ")",
                $surveyid,
                '',
                '',
                $action
            ); ?>
        </div>
        <div class="col-12 col-lg-6">
            <div>
                <label class="form-label "><?php eT("End URL:"); ?></label>
                <?php echo CHtml::textField(
                    "url_{$aSurveyLanguageSettings['surveyls_language']}",
                    htmlspecialchars_decode((string) $aSurveyLanguageSettings['surveyls_url']),
                    array('class' => 'form-control', 'size' => "80", 
                            'placeholder' => 'https://', 'id' => "url_{$aSurveyLanguageSettings['surveyls_language']}")
                ); ?>
            </div>
            <div class="mt-2">
                <label class="form-label "><?php eT("URL description:"); ?></label>
                <?php echo CHtml::textField(
                    "urldescrip_{$aSurveyLanguageSettings['surveyls_language']}",
                    $aSurveyLanguageSettings['surveyls_urldescription'],
                    array('class' => 'form-control', 'size' => "80", 'maxlength' => 255, 
                    'id' => "urldescrip_{$aSurveyLanguageSettings['surveyls_language']}")
                ); ?>
            </div>
        </div>
    </div>
</div>
