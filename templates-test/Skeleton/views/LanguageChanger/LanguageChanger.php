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

<!-- views/survey/system/LanguageChanger -->
    <div class="form-group form-horizontal">
        <label id="langchanger-label" for="langchanger" class="control-label text-right">
            <?php eT("Language:");?>
        </label>
        <div class=''>
            <?php echo CHtml::dropDownList('langchanger', $sSelected,$aListLang,array('class'=>$sClass,'data-targeturl'=>$sTargetURL));?>
        </div>
        <div class='sr-only'>
            <?php echo CHtml::htmlButton(gT("Change the language"),array('type'=>'submit','id'=>"changelangbtn",'value'=>'changelang','name'=>'changelang','class'=>'changelang jshide btn btn-default')); ?>
        </div>
    </div>
<!-- end of  views/survey/system/LanguageChanger -->
