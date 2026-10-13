<?php

/* @var $href string anchor of the linked part */
/* @var $text string link text */
/* @var $untranslatedCount int number of untranslated texts in the linked part */
/* @var $title string|null tooltip, e.g. the full question text */
/* @var $children array|null items of the nested list, each with href, text, title and untranslatedCount */

?>
<li class="quick-translation-nav-item" data-untranslated="<?php echo (int) $untranslatedCount; ?>">
    <a href="<?php echo CHtml::encode($href); ?>"<?php echo !empty($title) ? ' title="' . CHtml::encode($title) . '"' : ''; ?>>
        <span class="quick-translation-nav-text"><?php echo CHtml::encode($text); ?></span>
        <?php if ($untranslatedCount > 0) : ?>
            <span class="quick-translation-count" aria-hidden="true"><?php echo (int) $untranslatedCount; ?></span>
            <span class="visually-hidden"><?php echo ngT("{n} untranslated text|{n} untranslated texts", $untranslatedCount); ?></span>
        <?php endif; ?>
    </a>
    <?php if (!empty($children)) : ?>
        <ul>
            <?php foreach ($children as $child) {
                $this->renderPartial('translateNavItem_view', $child);
            } ?>
        </ul>
    <?php endif; ?>
</li>
