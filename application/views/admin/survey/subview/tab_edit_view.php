<?php

/**
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */

if (isset($data)) {
    extract($data);
}

/*
 * do we need this here?
 *
if (isset($scripts)) {
    echo $scripts;

    $iSurveyID = App()->request->getParam('surveyid');
    App()->session['FileManagerContent'] = "edit:survey:{$iSurveyID}";

    initKcfinder();
}
*/
 $cs = Yii::app()->getClientScript();
 $cs->registerPackage('bootstrap-select2');

// $adminlang = Yii::app()->session['adminlang']; get adminlang from db table if necessary ...

?>

<div class="container-center">
    <div class="row">
        <div class="form-group col-md-4 col-sm-6">
            <label for="surveyTitle"><?= gt('Survey title')?></label>
            <input type="text" class="form-control" name="surveyls_title" id="surveyTitle" required="required" >
        </div>
        <div class="form-group col-md-4 col-md-6">
            <label for="createsample" class="control-label"><?= gt('Create example question group and question?')?></label>
            <div>
                <input type="checkbox" name="createsample" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-4 col-md-6" >
            <label class="control-label" for="language"><?= gt('Base language')?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                    'asDropDownList' => true,
                    'htmlOptions'=>array('style'=>"width: 100%"),
                    'data' => isset($listLanguagesCode) ?  $listLanguagesCode : [],
                    'value' => $defaultLanguage, //or better user language ...
                    'name' => 'language',
                    'pluginOptions' => array()
                ));?>
            </div>
        </div>
        <div class="form-group col-md-4 col-md-6">
            <label class=" control-label" for='gsid'><?php  eT("Survey group:"); ?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                    'asDropDownList' => true,
                    'htmlOptions'=>array('style'=>"width: 100%"),
                    'data' => isset($aSurveyGroupList) ?  $aSurveyGroupList : [],
                    'value' => $oSurvey->gsid,
                    'name' => 'gsid',
                    'pluginOptions' => array()
                ));?>
            </div>
        </div>
    </div>
</div>
