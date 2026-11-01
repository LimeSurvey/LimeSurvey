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
// Items that only navigate to their url keep the link role, items that run an action are buttons
$linkAttributes = isset($linkAttributes) && is_array($linkAttributes) ? $linkAttributes : [];
$isAction = empty($url) || $url === '#' || stripos((string) $url, 'javascript:') === 0
    || isset($linkAttributes['data-bs-toggle'])
    || isset($linkAttributes['onclick'])
    || isset($linkAttributes['data-post-url']);
$role = $linkAttributes['role'] ?? ($isAction ? 'button' : null);
unset($linkAttributes['role']);
?>
<div data-bs-toggle="tooltip" title="<?= $encodeAttribute($tooltip ?? '') ?>">
    <a <?= !empty($linkId) ? 'id="' . $encodeAttribute($linkId) . '"' : '' ?>
       class="dropdown-item <?= $enabledCondition ? "" : "disabled" ?> <?= $encodeAttribute($linkClass ?? '') ?>"
       href="<?= $encodeAttribute($url ?? '#') ?>"
       <?= $role !== null ? 'role="' . $encodeAttribute($role) . '"' : '' ?>
       <?= !$enabledCondition ? 'aria-disabled="true" tabindex="-1"' : '' ?>
        <?php foreach ($linkAttributes as $attribute => $value) : ?>
            <?= $encodeAttribute($attribute) ?>="<?= $encodeAttribute($value) ?>"
        <?php endforeach; ?>>
        <?php if (isset($iconClass)) : ?>
            <i class="<?= $encodeAttribute($iconClass) ?>"></i>
        <?php endif; ?>
        <?= $title ?>
    </a>
</div>
