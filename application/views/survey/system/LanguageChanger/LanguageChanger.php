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
    <div class="form-group">
        <label for="langchanger" class="control-label">
            <?php eT("Select language:");?>
        </label>

        <?php echo CHtml::dropDownList('langchanger', $sSelected,$aListLang,array('class'=>$sClass,'data-targeturl'=>$sTargetURL));?>
        <!--  We don't have to add this button if in previewmode -->
        <?php echo CHtml::htmlButton(gT("Change the language"),array('type'=>'submit','id'=>"changelangbtn",'value'=>'changelang','name'=>'changelang','class'=>'changelang jshide btn btn-default')); ?>
    </div>
<!-- end of  views/survey/system/LanguageChanger -->
