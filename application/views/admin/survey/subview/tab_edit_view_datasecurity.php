<?php
/**
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */
if(isset($data)){
    extract($data);
}
 $count=0;
 if(isset($scripts))
    echo $scripts;


    $iSurveyID = Yii::app()->request->getParam('surveyid');
?>
<div class="container-center">
    <div id="advancedDataSecurityTextEditor"><lsdatasectexteditor/></div>
    <div id="datasecTextEditLoader" class="ls-flex ls-flex-column align-content-center align-items-center">
        <div class="ls-flex align-content-center align-items-center">
            <div class="loader-advancedquestionsettings text-center">
                <div class="contain-pulse animate-pulse">
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                </div>
            </div>
        </div>
    </div>
</div>

