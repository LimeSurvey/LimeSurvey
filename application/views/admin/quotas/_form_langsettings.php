<?php
/* @var Quota $oQuota */
/* @var CActiveForm $form */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>

<!-- Language tabs -->
<ul class="nav nav-tabs">
    <?php foreach ($oQuota->survey->getAllLanguages() as $lang): ?>
        <li role="presentation" <?php echo ($lang==$oQuota->survey->language ? 'class="active"': null);?>>
            <a data-toggle="tab" href="#edittxtele<?php echo $lang ?>">
                <?php echo getLanguageNameFromCode($lang,false); ?>
                <?php echo ($lang==$oQuota->survey->language ? '('.gT("Base language").')':null);?>
            </a>
        </li>
    <?php endforeach?>
</ul>
<div class='tab-content'>
    <?php foreach ($oQuota->survey->getAllLanguages() as $language)
    {
        echo CHtml::tag(
            'div',
            array(
                'id' => 'edittxtele' . $language,
                'class' => 'tab-pane fade in' . ($language == $oQuota->survey->language ? ' active ' : ''),
            ),$this->renderPartial('/admin/quotas/_form_langsetting',
            array(
                'form'=>$form,
                'oQuota'=>$oQuota,
                'oQuotaLanguageSetting' =>$aQuotaLanguageSettings[$language],
                'language' =>$language,
            ),true)
        );
    }?>
</div>
