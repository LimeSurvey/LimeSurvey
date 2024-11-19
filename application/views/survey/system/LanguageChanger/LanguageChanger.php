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

    <div class="row row-cols-lg-auto g-1 form-change-lang <?php echo $sClass ?>" >
        <label class="col-form-label fw-bold">
            <span class="form-label"><?php eT("Language:");?></span>
        </label>
        <div class="col-12">
            <?php echo CHtml::dropDownList('lang', $sSelected,$aListLang,array('id'=>false,'class'=>"form-select",'data-targeturl'=>$targetUrl));?>
        </div>
        <div class="col-12">
            <!-- for no js functionality use LanguageChangerForm: @see makeLanguageChangerSurvey -->
            <?php
               echo CHtml::htmlButton(gT("Change language"),array('type'=>'submit','value'=>'changelang','name'=>'move','class'=>"btn btn-outline-secondary ls-js-hidden"));
            ?>
        </div>
    </div>
<!-- end of  views/survey/system/LanguageChanger -->
<?php
App()->getClientScript()->registerScript("activateLanguageChanger","activateLanguageChanger();\n",CClientScript::POS_END);
?>
