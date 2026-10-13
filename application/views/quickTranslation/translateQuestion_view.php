<?php

/* @var $qid int */
/* @var $gid int */
/* @var $code string question code */
/* @var $typeName string name of the question type (HTML escaped) */
/* @var $fields array question text and help */
/* @var $subquestionScales array each with title, fields and untranslatedCount */
/* @var $answerScales array each with title, fields and untranslatedCount */
/* @var $untranslatedCount int */
/* @var $fieldData array data for all fields (languages) */
/* @var $surveyid int */
/* @var $tolang string */
/* @var $tolangdesc string */
/* @var $canPreview bool */

$id = 'quick-translation-question-' . $qid;
?>
<article class="quick-translation-question card mb-3" id="<?php echo $id; ?>" tabindex="-1"
         aria-labelledby="<?php echo $id; ?>-title" data-untranslated="<?php echo (int) $untranslatedCount; ?>">
    <div class="card-header d-flex align-items-center gap-2 flex-wrap">
        <h4 class="h6 mb-0" id="<?php echo $id; ?>-title">
            <span class="quick-translation-code"><?php echo CHtml::encode($code); ?></span>
        </h4>
        <span class="quick-translation-type me-auto"><?php echo $typeName; ?></span>
        <?php if ($untranslatedCount > 0) : ?>
            <span class="quick-translation-count"><?php echo ngT("{n} untranslated text|{n} untranslated texts", $untranslatedCount); ?></span>
        <?php endif; ?>
        <?php if ($canPreview) : ?>
            <a class="btn btn-sm btn-outline-secondary quick-translation-preview" target="_blank"
               href="<?php echo App()->createUrl("survey/index/action/previewquestion/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/lang/{$tolang}"); ?>">
                <i class="ri-eye-line" aria-hidden="true"></i>
                <?php echo CHtml::encode(sprintf(gT("Preview in %s"), $tolangdesc)); ?>
                <span class="visually-hidden"><?php eT("(opens in a new window)"); ?></span>
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php foreach ($fields as $field) {
            $this->renderPartial('translateFieldData', array_merge($field, $fieldData));
        } ?>
        <?php foreach (array_merge($subquestionScales, $answerScales) as $scale) : ?>
            <div class="quick-translation-part" data-untranslated="<?php echo (int) $scale['untranslatedCount']; ?>">
                <h5 class="h6 quick-translation-part-title"><?php echo CHtml::encode($scale['title']); ?></h5>
                <?php foreach ($scale['fields'] as $field) {
                    $this->renderPartial('translateFieldData', array_merge($field, $fieldData));
                } ?>
            </div>
        <?php endforeach; ?>
    </div>
</article>
