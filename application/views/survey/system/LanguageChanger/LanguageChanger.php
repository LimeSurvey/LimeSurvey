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
    <div class="form-inline form-change-lang <?php echo $sClass ?>" >
        <label class="form-group">
            <span class="control-label"><?php eT("Language:");?></span>
            <?php echo CHtml::dropDownList('lang', $sSelected,$aListLang,array('id'=>false,'class'=>"form-control",'data-targeturl'=>$targetUrl));?>
        </label>
            <!-- for no js functionality use LanguageChangerForm: @see makeLanguageChangerSurvey -->
            <?php
               echo CHtml::htmlButton(gT("Change language"),array('type'=>'submit','value'=>'changelang','name'=>'move','class'=>"btn btn-default ls-js-hidden"));
            ?>
    </div>
<!-- end of  views/survey/system/LanguageChanger -->
<?php
App()->getClientScript()->registerScript("activateLanguageChanger","activateLanguageChanger();\n",CClientScript::POS_END);
?>
