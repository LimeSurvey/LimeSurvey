<?php
/**
 * Language Changer view. For now, called from frontend_helper::makeLanguageChangerSurvey
 *
 * @var $sSelected
 * @var $aListLang
 * @var $sClass
 */
?>
<!-- views/surveys/LanguageChanger -->
    <?php
    App()->getController()->renderPartial("/survey/system/LanguageChanger/LanguageChangerForm",array(
        'sSelected' => $sSelected ,
        'aListLang' => $aListLang ,
        'sClass'    => $sClass    ,
        'targetUrl' => null,
    ));
    ?>
<!-- end of views/surveys/LanguageChanger -->
