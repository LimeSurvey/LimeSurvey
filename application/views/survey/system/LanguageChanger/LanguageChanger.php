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
    <div class="form-group form-horizontal" >
        <label id="langchanger-label" for="langchanger" class="col-sm-4 col-xs-4 control-label text-right">
            <?php eT("Language:");?>
        </label>
        <div class='col-xs-8 col-sm-4'>
            <?php echo CHtml::dropDownList('lang', $sSelected,$aListLang,array('id'=>'langchanger','class'=>$sClass,'data-targeturl'=>$sTargetURL));?>
        </div>
        <div class='sr-only'>
            <!--  In previewmode the no-js functionality didn't work : no form (for $_POST['lang']+$_POST['changelangbtn'] value, javascript do $_GET in activateLanguageChanger function)-->
            <!-- It must be a js-only button -->
            <?php
               echo CHtml::htmlButton(gT("Change the language"),array('type'=>'submit','id'=>"changelangbtn",'value'=>'changelang','name'=>'changelang','class'=>'changelang jshide btn btn-default'));
            ?>
        </div>
    </div>
<!-- end of  views/survey/system/LanguageChanger -->
