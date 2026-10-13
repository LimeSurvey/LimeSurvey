<?php

/* @var $id string HTML ID of the section */
/* @var $title string section heading, e.g. the group name */
/* @var $untranslatedCount int */
/* @var $fields array fields shown directly in the section */
/* @var $questions array|null questions of a group, see QuickTranslation::getSurveyStructure() */
/* @var $hasAutoTranslate bool whether the Google Translate API key is set */
/* @var $bReadOnly bool */

$fieldData = [
    'baselang' => $baselang,
    'tolang' => $tolang,
    'bReadOnly' => $bReadOnly,
];
?>
<section class="quick-translation-section card mb-4" id="<?php echo $id; ?>" tabindex="-1"
         aria-labelledby="<?php echo $id; ?>-title" data-untranslated="<?php echo (int) $untranslatedCount; ?>">
    <div class="card-header d-flex align-items-center gap-2 flex-wrap">
        <h3 class="h5 mb-0 me-auto" id="<?php echo $id; ?>-title">
            <button type="button" class="quick-translation-toggle" data-bs-toggle="collapse"
                    data-bs-target="#<?php echo $id; ?>-body" aria-expanded="true" aria-controls="<?php echo $id; ?>-body">
                <i class="ri-arrow-down-s-line" aria-hidden="true"></i>
                <?php echo CHtml::encode($title); ?>
            </button>
        </h3>
        <?php if ($untranslatedCount > 0) : ?>
            <span class="quick-translation-count"><?php echo ngT("{n} untranslated text|{n} untranslated texts", $untranslatedCount); ?></span>
        <?php endif; ?>
        <?php if (!$bReadOnly) : ?>
            <?php if ($hasAutoTranslate) : ?>
                <button type="button" class="auto-trans btn btn-sm btn-outline-secondary" id="auto-trans-<?php echo $id; ?>">
                    <?php eT("Auto Translate"); ?>
                </button>
            <?php else : ?>
                <?php // A disabled button gets no focus and no mouse events, the wrapper shows why it is disabled ?>
                <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip"
                      title="<?php eT("Auto translation needs a Google Translate API key in the global settings."); ?>">
                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled="disabled"
                            aria-describedby="quick-translation-auto-translate-unavailable">
                        <?php eT("Auto Translate"); ?>
                    </button>
                </span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="collapse show" id="<?php echo $id; ?>-body">
        <div class="card-body">
            <?php foreach ($fields as $field) {
                $this->renderPartial('translateFieldData', array_merge($field, $fieldData));
            } ?>
            <?php foreach ($questions ?? [] as $question) {
                $this->renderPartial('translateQuestion_view', array_merge($question, [
                    'fieldData' => $fieldData,
                    'surveyid' => $surveyid,
                    'tolang' => $tolang,
                    'tolangdesc' => $tolangdesc,
                    'canPreview' => $canPreview,
                ]));
            } ?>
        </div>
    </div>
</section>
