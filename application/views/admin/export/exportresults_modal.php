<?php
/**
 * Export results modal body
 * @var AdminController $this
 */
?>

<style>
    #resultexport-modal-form .export-section {
        margin-bottom: 16px !important;
        padding: 3px 12px 3px 16px;
    }

    #resultexport-modal-form .export-section:last-child {
        margin-bottom: 0 !important;
    }

    #resultexport-modal-form .export-section-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-family: 'IBM Plex Sans', sans-serif;
        font-weight: 600;
        font-size: 12px;
        line-height: 16px;
        letter-spacing: -0.017em;
        color: #1E1E1E;
        opacity: 1;
        margin-bottom: 8px;
    }

    #resultexport-modal-form .export-radio-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 140px);
        gap: 12px 20px;
    }

    #resultexport-modal-form .export-radio-option {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 12px;
        width: 140px !important;
        height: auto !important;
    }

    #resultexport-modal-form .export-radio-option .form-check-input {
        appearance: none !important;
        -webkit-appearance: none !important;
        flex-shrink: 0;
        margin: 0 !important;
        width: 24px !important;
        height: 24px !important;
        border-radius: 50% !important;
        border: 1.33px solid #1E1E1E !important;
        outline: none !important;
        box-shadow: none !important;
        background-color: #fff !important;
    }

    #resultexport-modal-form .export-radio-option .form-check-input:checked {
        background-color: #333641 !important;
        box-shadow: inset 0 0 0 5px #fff !important;
        border-color: #1E1E1E !important;
        outline: none !important;
    }

    #resultexport-modal-form .export-radio-option .form-check-label {
        margin: 0;
        cursor: pointer;
        flex: 1;
        min-width: 0;
        font-family: 'IBM Plex Sans', sans-serif;
        font-weight: 400;
        font-size: 14px;
        line-height: 20px;
        letter-spacing: -0.017em;
        color: #1E1E1E;
    }

    #resultexport-modal-form .export-section-label-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        font-size: 12px;
        line-height: 1;
    }

    #resultexport-modal-form .export-radio-option--fenced .form-check-input {
        border-color: #FFF1E0 !important;
        opacity: 1 !important;
        cursor: not-allowed;
    }

    #resultexport-modal-form .export-radio-option--fenced .form-check-label {
        cursor: not-allowed;
    }

    .export-upsell-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 25px;
        margin-bottom: 16px;
        background: #FFF3E6;
        border-radius: 4px;
    }

    .export-upsell-banner-icon {
        font-size: 20px;
        line-height: 1;
        flex-shrink: 0;
    }

    .export-upsell-banner-title {
        font-family: 'IBM Plex Sans', sans-serif;
        font-weight: 500;
        font-size: 18px;
        line-height: 20px;
        letter-spacing: -0.017em;
        color: #1E1E1E;
        margin: 0;
    }

    .export-upsell-banner-text {
        font-family: 'IBM Plex Sans', sans-serif;
        font-weight: 400;
        font-size: 14px;
        line-height: 20px;
        letter-spacing: -0.017em;
        color: #333641;
        margin: 4px 0 0;
    }

    .export-upsell-banner-button {
        flex-shrink: 0;
        background: #FFBA68;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        font-family: 'IBM Plex Sans', sans-serif;
        font-weight: 500;
        font-size: 14px;
        color: #1E1E1E;
        text-decoration: none;
    }

</style>

<?php echo CHtml::form(array('admin/export/sa/exportresults/surveyid/' . $surveyid), 'post', array('id' => 'resultexport-modal-form')); ?>

    <?php if ($isFreeUser) : ?>
    <div class="export-upsell-banner">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span class="export-upsell-banner-icon">&#10024;</span>
            <div>
                <p class="export-upsell-banner-title"><?php eT('Take your results anywhere - unlock more formats'); ?></p>
                <p class="export-upsell-banner-text"><?php eT('Switch to LimeSurvey Expert to receive the advanced export options.'); ?></p>
            </div>
        </div>
        <a href="#" class="export-upsell-banner-button"><?php eT('Show options'); ?></a>
    </div>
    <?php endif; ?>

    <div class="export-section">
        <label class="export-section-label"><?php eT('Export data'); ?></label>
        <div class="export-radio-grid">
            <div class="export-radio-option">
                <input class="form-check-input" type="radio" name="exportdata" id="exportdata-filtered" value="filtered" checked>
                <label class="form-check-label" for="exportdata-filtered"><?php eT('Filtered data'); ?></label>
            </div>
            <div class="export-radio-option">
                <input class="form-check-input" type="radio" name="exportdata" id="exportdata-all" value="all">
                <label class="form-check-label" for="exportdata-all"><?php eT('All data'); ?></label>
            </div>
        </div>
    </div>

    <div class="export-section">
        <label class="export-section-label">
            <?php eT('File formats'); ?>
            <?php if ($isFreeUser) : ?>
                <span class="export-section-label-icon">&#10024;</span>
            <?php endif; ?>
        </label>
        <?php
        // Fixed display order to match the approved design, instead of relying on plugin registration order.
        $formatOrder = ['csv', 'html', 'pdf', 'spsssav', 'stataxml', 'rsyntax', 'rdata', 'json', 'xls', 'doc'];
        $orderedExports = array_merge(array_flip($formatOrder), $exports);
        // Free-plan accounts only get CSV/HTML; the rest are shown but disabled behind the upsell banner.
        // All non-free subscription aliases have every format enabled.
        $allowedFormats = $isFreeUser ? ['csv', 'html'] : array_keys($orderedExports);
        // First row only holds the 2 primary formats when gating is active, matching the approved design's layout.
        $rowBreaksAfter = $isFreeUser ? [2] : [];
        $renderedCount = 0;
        foreach ($orderedExports as $key => $info) :
            if (!is_array($info) || empty($info['label'])) {
                continue;
            }
            $isFenced = !in_array($key, $allowedFormats);
            if ($renderedCount === 0) : ?>
                <div class="export-radio-grid">
            <?php elseif (in_array($renderedCount, $rowBreaksAfter)) : ?>
                </div>
                <div class="export-radio-grid" style="margin-top: 12px;">
            <?php endif; ?>
                <div class="export-radio-option<?= $isFenced ? ' export-radio-option--fenced' : ''; ?>">
                    <input class="form-check-input" type="radio" name="type" id="export-format-<?= CHtml::encode($key); ?>" value="<?= CHtml::encode($key); ?>" <?= $info['label'] == $defaultexport ? 'checked' : ''; ?> <?= $isFenced ? 'disabled' : ''; ?>>
                    <label class="form-check-label" for="export-format-<?= CHtml::encode($key); ?>"><?= $info['label']; ?></label>
                </div>
            <?php
            $renderedCount++;
        endforeach; ?>
        </div>
    </div>

    <div class="export-section">
        <label class="export-section-label"><?php eT('CSV file separator'); ?></label>
        <div class="export-radio-grid">
            <?php foreach ($aCsvFieldSeparator as $separator => $label) : ?>
                <div class="export-radio-option">
                    <input class="form-check-input" type="radio" name="csvfieldseparator" id="csvfieldseparator-<?= md5($separator); ?>" value="<?= CHtml::encode($separator); ?>" <?= $separator === chr(44) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="csvfieldseparator-<?= md5($separator); ?>"><?= $label; ?><?= $separator !== chr(9) ? ' (' . CHtml::encode($separator) . ')' : ''; ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="export-section">
        <label class="export-section-label"><?php eT('Export language'); ?></label>
        <div class="export-radio-grid">
            <?php foreach ($aLanguages as $code => $language) : ?>
                <div class="export-radio-option">
                    <input class="form-check-input" type="radio" name="exportlang" id="exportlang-<?= CHtml::encode($code); ?>" value="<?= CHtml::encode($code); ?>" <?= $code === $thissurvey['language'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="exportlang-<?= CHtml::encode($code); ?>"><?= CHtml::encode($language); ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php foreach ($selectedColumns as $column) : ?>
        <input type="hidden" name="colselect[]" value="<?= CHtml::encode($column); ?>">
    <?php endforeach; ?>
    <?php foreach ($responseFilters as $column => $value) : ?>
        <?php if ($value !== '') : ?>
            <input type="hidden" name="SurveyDynamic[<?= CHtml::encode($column); ?>]" value="<?= CHtml::encode($value); ?>">
        <?php endif; ?>
    <?php endforeach; ?>
    <input type="hidden" name="export_from" value="<?= (int) $min_datasets; ?>">
    <input type="hidden" name="export_to" value="<?= (int) $max_datasets; ?>">
    <input type="hidden" name="completionstate" value="all">
    <!-- Responses defaults -->
    <input type="hidden" name="answers" value="long">
    <input type="hidden" name="converty" value="0">
    <input type="hidden" name="convertyto" value="1">
    <input type="hidden" name="convertn" value="0">
    <input type="hidden" name="convertnto" value="2">
    <input type="hidden" name="maskequations" value="Y">
    <!-- Headings defaults -->
    <input type="hidden" name="headstyle" value="full">
    <input type="hidden" name="striphtmlcode" value="1">
    <input type="hidden" name="headspacetounderscores" value="0">
    <input type="hidden" name="abbreviatedtext" value="0">
    <input type="hidden" name="abbreviatedtextto" value="15">
    <input type="hidden" name="emcode" value="0">
    <input type="hidden" name="codetextseparator" value=".">
</form>