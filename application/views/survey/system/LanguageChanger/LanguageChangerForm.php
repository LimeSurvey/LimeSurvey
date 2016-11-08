<?php
/**
 * Language Changer view. For now, called from frontend_helper::makeLanguageChangerSurvey
 *
 * @var $sSelected
 * @var $aListLang
 * @var $sClass
 * @var $sTargetURL
 */
?>
<!-- Must be included only one time (else : multiple id) -->
<!-- views/survey/system/LanguageChanger -->
<form method="get" class="ls-languagechanger-form">
    <?php
    App()->getController()->renderPartial("/survey/system/LanguageChanger/LanguageChanger",array(
        'sSelected' => $sSelected ,
        'aListLang' => $aListLang ,
        'sClass'    => $sClass    ,
        'targetUrl' => $targetUrl,
    ));
    ?>
</form>
<!-- end of  views/survey/system/LanguageChanger -->
