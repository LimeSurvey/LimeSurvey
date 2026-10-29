<?php
/** @var string $tooltip */
/** @var string $linkId */
/** @var bool $enabledCondition */
/** @var string $linkClass */
/** @var string $url */
/** @var array $linkAttributes */
/** @var string $iconClass */
/** @var string $title */
?>

<?php
// Note: Values are often escaped already (e.g. by gT()), so existing entities are not encoded again
$encodeAttribute = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', false);
};
?>
<div data-bs-toggle="tooltip" title="<?= $encodeAttribute($tooltip ?? '') ?>">
    <a id="<?= $encodeAttribute($linkId ?? '') ?>"
       class="dropdown-item <?= $enabledCondition ? "" : "disabled" ?> <?= $encodeAttribute($linkClass ?? '') ?>"
       href="<?= $encodeAttribute($url ?? '#') ?>"
       role="button"
        <?php if (isset($linkAttributes) && is_array($linkAttributes)) : ?>
            <?php foreach ($linkAttributes as $attribute => $value) : ?>
                <?= $encodeAttribute($attribute) ?>="<?= $encodeAttribute($value) ?>"
            <?php endforeach; ?>
        <?php endif; ?>>
        <?php if (isset($iconClass)) : ?>
            <i class="<?= $encodeAttribute($iconClass) ?>"></i>
        <?php endif; ?>
        <?= $title ?>
    </a>
</div>
