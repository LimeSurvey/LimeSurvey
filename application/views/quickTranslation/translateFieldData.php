<?php

/* @var     $surveyId int */
/* @var     $gid  int */
/* @var     $qid int */
/* @var     $type string field type, e.g. 'question' or 'question_help' */
/* @var     $amTypeOptions array */
/* @var     $textfrom string */
/* @var     $textto string */
/* @var     $j int record index */
/* @var     $rowfrom array */
/* @var     $nrows float */
/* @var     $untranslated bool */
/* @var     $baselang string */
/* @var     $tolang string */
/* @var     $bReadOnly bool */

$value1 = (!empty($amTypeOptions["id1"])) ? $rowfrom[$amTypeOptions["id1"]] : "";
$value2 = (!empty($amTypeOptions["id2"])) ? $rowfrom[$amTypeOptions["id2"]] : "";
$iScaleID = (!empty($amTypeOptions["scaleid"])) ? $rowfrom[$amTypeOptions["scaleid"]] : "";
$labelId = "{$type}_label_{$j}";
$sourceId = "{$type}_from_{$j}";
$newValueName = "{$type}_newvalue_{$j}";

// Subquestions and answer options are labelled with their code, like in the survey
$isCodeLabel = ($type == 'subquestion' || $type == 'answer');
$label = $type == 'subquestion' ? $rowfrom['title'] : ($type == 'answer' ? $rowfrom['code'] : $amTypeOptions['fieldLabel']);
?>
<div class="quick-translation-field quick-translation-field-<?php echo $type; ?><?php echo $untranslated ? ' is-untranslated' : ''; ?>">
    <div class="quick-translation-from">
        <div class="quick-translation-label-row">
            <span id="<?php echo $labelId; ?>" class="<?php echo $isCodeLabel ? 'quick-translation-code' : 'quick-translation-label'; ?>"><?php echo CHtml::encode($label); ?></span>
            <?php if ($untranslated) : ?>
                <span class="quick-translation-untranslated"><?php eT("Untranslated"); ?></span>
            <?php endif; ?>
        </div>
        <div class="quick-translation-source">
            <?php if ($type == 'question_help') : ?>
                <i class="ri-information-fill quick-translation-help-icon" aria-hidden="true"></i>
            <?php endif; ?>
            <?php // The content of the _from_ element is the source text for the auto translation ?>
            <div class="_from_" id="<?php echo $sourceId; ?>" lang="<?php echo CHtml::encode($baselang); ?>"><?php echo showJavaScript($textfrom); ?></div>
        </div>
    </div>
    <div class="quick-translation-to">
        <?php
        echo CHtml::hiddenField("{$type}_id1_{$j}", $value1);
        echo CHtml::hiddenField("{$type}_id2_{$j}", $value2);
        if (is_numeric($iScaleID)) {
            echo CHtml::hiddenField("{$type}_scaleid_{$j}", $iScaleID);
        }
        // Save a copy in type_oldvalue_i to identify changes before db update
        echo CHtml::hiddenField("{$type}_oldvalue_{$j}", $textto);

        $aDisplayOptions = [
            'class' => 'form-control',
            'rows' => max(1, $nrows),
            'lang' => $tolang,
            'readonly' => $bReadOnly,
            'aria-labelledby' => "{$labelId} quick-translation-target-language",
            'aria-describedby' => $sourceId,
        ];
        if ($type == 'group') {
            $aDisplayOptions['maxlength'] = 100;
        }
        $htmleditor_data = array(
            "edit" . $type,
            $newValueName,
            htmlspecialchars((string) $textto),
            $surveyId,
            $gid,
            $qid,
            "translate" . $amTypeOptions["HTMLeditorType"]
        );
        ?>
        <div class="quick-translation-editor">
            <?php echo CHtml::textArea($newValueName, $textto, $aDisplayOptions); ?>
            <div class="quick-translation-editor-buttons">
                <?php if (!$bReadOnly) : ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary quick-translation-copy"
                            data-target-field-id="<?php echo $newValueName; ?>"
                            data-source="<?php echo CHtml::encode($textfrom); ?>"
                            data-bs-toggle="tooltip" title="<?php eT("Copy source text"); ?>"
                            aria-label="<?php echo CHtml::encode(sprintf(gT("Copy source text to %s"), $label)); ?>">
                        <i class="ri-file-copy-line" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>
                <?php echo $this->loadEditor($amTypeOptions, $htmleditor_data); ?>
            </div>
        </div>
    </div>
</div>
