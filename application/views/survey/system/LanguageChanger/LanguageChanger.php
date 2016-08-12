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
    <div class="form-group form-horizontal" >
        <label id="langchanger-label" for="langchanger" class="col-sm-4 col-xs-4 control-label text-right">
            <?php eT("Language:");?>
        </label>

        <div class='col-xs-8 col-sm-4'>
            <?php echo CHtml::dropDownList('langchanger', $sSelected,$aListLang,array('class'=>$sClass,'data-targeturl'=>$sTargetURL));?>
        </div>
        <div class='col-xs-0 col-sm-4'>
            <!--  We don't have to add this button if in previewmode -->
            <?php
               echo CHtml::htmlButton(gT("Change the language"),array('type'=>'submit','id'=>"changelangbtn",'value'=>'changelang','name'=>'changelang','style'=>'display:none;','class'=>'changelang jshide btn btn-default'));
            ?>
        </div>
    </div>
<!-- end of  views/survey/system/LanguageChanger -->
