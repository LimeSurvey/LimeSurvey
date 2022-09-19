<?php
/* @var Quota $oQuota */

/* @var CActiveForm $form */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>
<div class="container">
    <div class="row">
        <div class="col-12 border-start border-2 border-secondary">
                <!-- Language tabs -->
                <ul class="nav nav-tabs">
                    <?php foreach ($oQuota->survey->getAllLanguages() as $lang): ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= ($lang == $oQuota->survey->language) ? 'active' : null ?>" data-bs-toggle="tab" href="#edittxtele<?php echo $lang ?>">
                                <?php echo getLanguageNameFromCode($lang, false); ?>
                                <?php echo($lang == $oQuota->survey->language ? '(' . gT("Base language") . ')' : null); ?>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
                <div class='tab-content'>
                    <?php foreach ($oQuota->survey->getAllLanguages() as $language) {
                        echo CHtml::tag(
                            'div',
                            array(
                                'id' => 'edittxtele' . $language,
                                'class' => 'tab-pane fade' . " " . ($language == $oQuota->survey->language ? 'show active ' : ''),
                            ),
                            $this->renderPartial('/admin/quotas/_form_langsetting',
                                array(
                                    'form' => $form,
                                    'oQuota' => $oQuota,
                                    'oQuotaLanguageSetting' => ($aQuotaLanguageSettings[$language] ?? new QuotaLanguageSetting),
                                    'language' => $language,
                                ),
                                true
                            )
                        );
                    } ?>
                </div>
        </div>
    </div>
</div>
